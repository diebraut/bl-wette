<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../common/include.php';
require __DIR__ . '/../common/match_update.php';
if (!in_array(HOST, ['localhost','127.0.0.1'], true)) exit('Local tests only');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = mysqli_connect(HOST, DB_USER, DB_PASSWD, DB_NAME);
// Connection-local temporary tables shadow real tables. They disappear on exit.
foreach (['tblspieltag','tblverein','tblgamer','tblwette','tblinfo'] as $table) {
    $definition = mysqli_fetch_row(mysqli_query($db, "SHOW CREATE TABLE $table"))[1];
    mysqli_query($db, preg_replace('/^CREATE TABLE /', 'CREATE TEMPORARY TABLE ', $definition));
}
function check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
    echo "OK: $message\n";
}
function scalar($sql) { global $db; return mysqli_fetch_row(mysqli_query($db,$sql))[0]; }
mysqli_query($db, 'INSERT INTO tblinfo (intDay) VALUES (3)');
mysqli_query($db, 'INSERT INTO tblverein (lngIndex) VALUES (1),(2)');
mysqli_query($db, "INSERT INTO tblgamer (lngIndex,strAlias,intPoint,intMoney) VALUES (1,'Test',0,123),(2,'No tip',0,0)");
$fixtures = [];
for ($day=3;$day<=4;$day++) {
    for ($i=1;$i<=9;$i++) {
        $id=$day*100+$i;
        $start=$day===3 ? '2026-09-11 20:30:00' : '2026-09-18 20:30:00';
        mysqli_query($db,"INSERT INTO tblspieltag (lngIndex,intTag,intVerein1,intVerein2,intStatus,intGoal1,intGoal2,intMatchIdFromOpenLigaDB,dtmStart) VALUES ($id,$day,1,2,0,0,0,$id,'$start')");
        $fixtures[$day][]=['matchID'=>$id,'group'=>['groupOrderID'=>$day],'matchIsFinished'=>false,
            'matchResults'=>[['resultTypeID'=>2,'pointsTeam1'=>2,'pointsTeam2'=>1]]];
    }
}
mysqli_query($db,'INSERT INTO tblwette (intUserid,intTag,intSpielid,intGoal1,intGoal2) VALUES (1,3,301,2,1)');
$calls=0;
$service=new MatchUpdate($db,function($day) use (&$fixtures,&$calls) { $calls++;return $fixtures[$day]; });
$early=new DateTimeImmutable('2026-09-11 21:00:00');
$late=new DateTimeImmutable('2026-09-11 23:00:00');
$service->run($early);
check($calls===0,'No API calls before expected finish');
$service->run($late);
check((int)scalar('SELECT intPoint FROM tblgamer WHERE lngIndex=1')===0,'Provisional result not scored');
$fixtures[3][0]['matchIsFinished']=true;
$service->run($late);
check((int)scalar('SELECT intPoint FROM tblgamer WHERE lngIndex=1')===4,'Exact tip gets four points immediately');
check((int)scalar('SELECT intPoint FROM tblverein WHERE lngIndex=1')===3,'Club points updated');
check((int)scalar('SELECT intDay FROM tblinfo')===3,'Unfinished matches prevent advancement');
$service->run($late);
check((int)scalar('SELECT intPoint FROM tblgamer WHERE lngIndex=1')===4,'Repeat run does not double count');
check((int)scalar('SELECT intMoney FROM tblgamer WHERE lngIndex=1')===123,'Money unchanged');
foreach ($fixtures[3] as &$match) $match['matchIsFinished']=true;
unset($match);
$fixtures[3][8]['matchResults']=[];
$service->run($late);
check((int)scalar('SELECT intDay FROM tblinfo')===3,'Missing final score prevents advancement');
$fixtures[3][8]['matchResults']=[['resultTypeID'=>2,'pointsTeam1'=>0,'pointsTeam2'=>0]];
$service->run($late);
check((int)scalar('SELECT intDay FROM tblinfo')===4,'All final results advance exactly one matchday');
check((int)scalar('SELECT intPoint FROM tblverein WHERE lngIndex=1')===25,'Club totals include draw');
$failed=new MatchUpdate($db,function($day) { throw new RuntimeException('Test outage'); });
$result=$failed->run(new DateTimeImmutable('2026-09-19 23:00:00'));
check(!empty($result['errors']) && (int)scalar('SELECT intDay FROM tblinfo')===4,'API outage does not advance');
mysqli_query($db,'UPDATE tblinfo SET intDay=34');
check($service->advanceCompletedDay()===34,'Season never advances to 35');
mysqli_query($db,"INSERT INTO tblspieltag (lngIndex,intTag,intVerein1,intVerein2,intStatus,intGoal1,intGoal2,intMatchIdFromOpenLigaDB,dtmStart) VALUES (201,2,1,2,0,0,0,201,'2026-09-04 20:30:00')");
$fixtures[2]=[['matchID'=>201,'group'=>['groupOrderID'=>2],'matchIsFinished'=>true,
    'matchResults'=>[['resultTypeID'=>2,'pointsTeam1'=>1,'pointsTeam2'=>0]]]];
$service->run($late);
check((int)scalar('SELECT intStatus FROM tblspieltag WHERE lngIndex=201')===2,'Older unfinished match evaluated independently of current matchday');
echo "All tests passed; only temporary tables used.\n";
