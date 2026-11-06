<?php
if (PHP_SAPI !== 'cli') exit(1);
require __DIR__ . '/../common/include.php';
require __DIR__ . '/../common/match_update.php';
if (!in_array(HOST, ['localhost','127.0.0.1'], true)) exit('Local tests only');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = mysqli_connect(HOST, DB_USER, DB_PASSWD, DB_NAME);
foreach (['tblspieltag','tblverein','tblgamer','tblwette','tblinfo'] as $table) {
    $definition = mysqli_fetch_row(mysqli_query($db, "SHOW CREATE TABLE $table"))[1];
    mysqli_query($db, preg_replace('/^CREATE TABLE /', 'CREATE TEMPORARY TABLE ', $definition));
}
function expectSchedule($ok, $text) {
    if (!$ok) throw new RuntimeException($text);
    echo "OK: $text\n";
}
function scheduleScalar($sql) { global $db; return mysqli_fetch_row(mysqli_query($db, $sql))[0]; }
mysqli_query($db, 'INSERT INTO tblinfo (intDay) VALUES (3)');
$fixtures = [];
foreach ([3,4,10,34] as $day) {
    for ($i=1; $i<=9; $i++) {
        $id = $day*100+$i;
        $date = $day===3 ? '2026-09-11' : ($day===4 ? '2026-09-18' : '2027-05-22');
        mysqli_query($db, "INSERT INTO tblspieltag (lngIndex,intTag,intVerein1,intVerein2,intStatus,intGoal1,intGoal2,intMatchIdFromOpenLigaDB,dtmStart)
            VALUES ($id,$day,1,2,0,0,0,$id,'$date 21:00:00')");
        $fixtures[$day][] = ['matchID'=>$id,'group'=>['groupOrderID'=>$day],
            'matchDateTimeUTC'=>$date.'T19:00:00Z','matchIsFinished'=>false,'matchResults'=>[]];
    }
}
$schedule = $fixtures[10];
$schedule[0]['matchDateTimeUTC'] = '2026-11-01T16:30:00Z';
$calls = 0;
$failSchedule = false;
$stamp = tempnam(sys_get_temp_dir(), 'bl-wette-schedule-test-');
$service = new MatchUpdate($db, function ($day) use (&$fixtures) { return $fixtures[$day] ?? []; },
    function () use (&$schedule,&$calls,&$failSchedule) {
        $calls++;
        if ($failSchedule) throw new RuntimeException('Schedule outage');
        return $schedule;
    }, $stamp);
$zone = new DateTimeZone('Europe/Berlin');
try {
    $changes = $service->refreshSeasonSchedule(3, true);
    expectSchedule(count($changes)===1, 'Dry run reports the published future kickoff change');
    expectSchedule(scheduleScalar('SELECT dtmStart FROM tblspieltag WHERE lngIndex=1001')==='2027-05-22 21:00:00', 'Dry run leaves dates untouched');
    $calls = 0;
    $early = new DateTimeImmutable('2026-09-11 20:45:00',$zone);
    $service->run($early);
    expectSchedule($calls===1, 'Daily full-season check runs once');
    expectSchedule(scheduleScalar('SELECT dtmStart FROM tblspieltag WHERE lngIndex=1001')==='2026-11-01 17:30:00', 'Published UTC kickoff converts to Berlin winter time');
    $service->run($early);
    expectSchedule($calls===1, 'Unchanged daily state avoids repeated full-season requests');
    $service->run(new DateTimeImmutable('2026-09-11 21:05:00',$zone));
    $schedule[0]['matchDateTimeUTC'] = '2026-11-01T18:30:00Z';
    foreach ($fixtures[3] as &$match) {
        $match['matchIsFinished']=true;
        $match['matchResults']=[['resultTypeID'=>2,'pointsTeam1'=>1,'pointsTeam2'=>0]];
    }
    unset($match);
    $service->run(new DateTimeImmutable('2026-09-11 23:00:00',$zone), true);
    expectSchedule($calls===2, 'Completing a round during live polling triggers another season check');
    expectSchedule(scheduleScalar('SELECT dtmStart FROM tblspieltag WHERE lngIndex=1001')==='2026-11-01 19:30:00', 'Future club fixtures receive the newly published kickoff');
    expectSchedule((int)scheduleScalar('SELECT intDay FROM tblinfo')===4, 'Normal round advancement remains intact');
    $service->run(new DateTimeImmutable('2026-09-11 23:01:00',$zone));
    expectSchedule($calls===2, 'Next regular invocation does not repeat the completed-round check');
    $failSchedule = true;
    $nextDate = new DateTimeImmutable('2026-09-12 12:00:00',$zone);
    $before = file_get_contents($stamp);
    $result = $service->run($nextDate);
    expectSchedule(!empty($result['errors']) && file_get_contents($stamp)===$before, 'Failed API request preserves retry state');
    $failSchedule = false;
    $service->run($nextDate);
    expectSchedule($calls===4, 'Next regular run retries failed season check');
    mysqli_query($db,'UPDATE tblinfo SET intDay=34');
    foreach ($fixtures[34] as &$match) {
        $match['matchIsFinished']=true;
        $match['matchResults']=[['resultTypeID'=>2,'pointsTeam1'=>0,'pointsTeam2'=>0]];
    }
    unset($match);
    $seasonEnd = new DateTimeImmutable('2027-05-22 23:00:00',$zone);
    $service->run($seasonEnd);
    expectSchedule((int)scheduleScalar('SELECT intDay FROM tblinfo')===34, 'Final round remains day 34');
    expectSchedule(strpos(file_get_contents($stamp),'34')!==false, 'Final completed round is tracked even without advancing');
    $schedule = [$fixtures[3][0]];
    $schedule[0]['matchDateTimeUTC']='2026-09-11T20:00:00Z';
    expectSchedule($service->refreshSeasonSchedule(34,true)===[], 'Completed fixtures never have their kickoff rewritten');
} finally { @unlink($stamp); }
echo "Schedule tests passed; only temporary tables used.\n";
