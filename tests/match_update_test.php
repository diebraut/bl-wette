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
        $apiStart=$day===3 ? '2026-09-11T19:00:00Z' : '2026-09-18T18:30:00Z';
        $fixtures[$day][]=['matchID'=>$id,'group'=>['groupOrderID'=>$day],'matchIsFinished'=>false,
            'matchDateTimeUTC'=>$apiStart,'goals'=>[],'matchResults'=>[]];
    }
}
mysqli_query($db,'INSERT INTO tblwette (intUserid,intTag,intSpielid,intGoal1,intGoal2) VALUES (1,3,301,2,1)');
$calls=0;
$service=new MatchUpdate($db,function($day) use (&$fixtures,&$calls) { $calls++;return $fixtures[$day]; });
$zone=new DateTimeZone('Europe/Berlin');
$early=new DateTimeImmutable('2026-09-11 20:45:00',$zone);
$started=new DateTimeImmutable('2026-09-11 21:05:00',$zone);
$late=new DateTimeImmutable('2026-09-11 23:00:00',$zone);
$service->run($early);
check($calls===2,'Current and next matchday checked for schedule changes');
check(scalar('SELECT dtmStart FROM tblspieltag WHERE lngIndex=301')==='2026-09-11 21:00:00','Changed kickoff stored before match');
check((int)scalar('SELECT intStatus FROM tblspieltag WHERE lngIndex=301')===0,'Match remains open before updated kickoff');
$service->run($started);
check((int)scalar('SELECT intStatus FROM tblspieltag WHERE lngIndex=301')===1,'Match starts at current OpenLigaDB kickoff');
check((int)scalar('SELECT intGoal1 FROM tblspieltag WHERE lngIndex=301')===0,'Running goalless match starts at 0:0');
$fixtures[3][0]['goals']=[['scoreTeam1'=>1,'scoreTeam2'=>0]];
$service->run($started);
check((int)scalar('SELECT intGoal1 FROM tblspieltag WHERE lngIndex=301')===1,'OpenLigaDB goal updates live score');
check((int)scalar('SELECT intPoint FROM tblgamer WHERE lngIndex=1')===0,'Provisional result not persisted in total score');
$fixtures[3][0]['matchIsFinished']=true;
$fixtures[3][0]['matchResults']=[['resultTypeID'=>2,'pointsTeam1'=>2,'pointsTeam2'=>1]];
$service->run($late);
check((int)scalar('SELECT intPoint FROM tblgamer WHERE lngIndex=1')===4,'Exact tip gets four points immediately');
check((int)scalar('SELECT intPoint FROM tblverein WHERE lngIndex=1')===3,'Club points updated');
check((int)scalar('SELECT intDay FROM tblinfo')===3,'Unfinished matches prevent advancement');
$service->run($late);
check((int)scalar('SELECT intPoint FROM tblgamer WHERE lngIndex=1')===4,'Repeat run does not double count');
check((int)scalar('SELECT intMoney FROM tblgamer WHERE lngIndex=1')===123,'Money unchanged');
foreach ($fixtures[3] as &$match) {
    $match['matchIsFinished']=true;
    $match['matchResults']=[['resultTypeID'=>2,'pointsTeam1'=>2,'pointsTeam2'=>1]];
}
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
    'matchDateTimeUTC'=>'2026-09-04T18:30:00Z',
    'matchResults'=>[['resultTypeID'=>2,'pointsTeam1'=>1,'pointsTeam2'=>0]]]];
$service->run($late);
check((int)scalar('SELECT intStatus FROM tblspieltag WHERE lngIndex=201')===2,'Older unfinished match evaluated independently of current matchday');
echo "All tests passed; only temporary tables used.\n";
