<?php
if (PHP_SAPI !== 'cli') exit;
require __DIR__ . '/../common/include.php';
if (!in_array(HOST, ['localhost','127.0.0.1'], true)) exit('Local only');
$db = mysqli_connect(HOST,DB_USER,DB_PASSWD,DB_NAME);
function snapshot($db) {
    return mysqli_fetch_all(mysqli_query($db,'CHECKSUM TABLE tblspieltag,tblwette,tblverein,tblgamer,tblsession,tblinfo'));
}
$before=snapshot($db);
$token=mysqli_fetch_row(mysqli_query($db,'SELECT strSessionid FROM tblsession LIMIT 1'))[0];
foreach (['table','bltable','day','list','help'] as $view) {
    $ch=curl_init('http://127.0.0.1/user/live.php');
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,
        CURLOPT_POSTFIELDS=>http_build_query(['user'=>$token,'view'=>$view,'day'=>3]),CURLOPT_TIMEOUT=>10]);
    $body=curl_exec($ch); $code=curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
    $data=json_decode($body,true);
    if ($code!==200 || !isset($data['html'],$data['clubs'],$data['currentDay'])) throw new RuntimeException('Invalid response: '.$view);
    if (in_array($view,['table','bltable','day']) && strpos($data['html'],'<table')===false) throw new RuntimeException('Missing table');
    if (strpos($data['html'],'blLiveConfig')!==false) throw new RuntimeException('Menu/session script included');
    echo "OK: $view JSON and fragments\n";
    foreach ([$data['version'], 'outdated'] as $version) {
        $ch=curl_init('http://127.0.0.1/user/live.php');
        curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,
            CURLOPT_POSTFIELDS=>http_build_query(['user'=>$token,'view'=>$view,'day'=>3,'version'=>$version]),CURLOPT_TIMEOUT=>10]);
        $next=json_decode(curl_exec($ch),true); curl_close($ch);
        if ($version===$data['version']) {
            if ($next['changed']!==false || isset($next['html']) || isset($next['clubs'])) throw new RuntimeException('Unchanged payload transmitted');
        } elseif ($next['changed']!==true || !isset($next['html'])) throw new RuntimeException('Changed version not refreshed');
    }
    echo "OK: $view unchanged response contains no display data; outdated version refreshes\n";
}
$ch=curl_init('http://127.0.0.1/user/live.php');
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);curl_exec($ch);
if(curl_getinfo($ch,CURLINFO_HTTP_CODE)!==401) throw new RuntimeException('Missing authentication');
curl_close($ch);
if ($before!==snapshot($db)) throw new RuntimeException('Database changed during read requests');
echo "OK: authentication required; database including sessions unchanged\n";
