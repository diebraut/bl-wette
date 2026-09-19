<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../common/include.php';
require __DIR__ . '/../common/match_update.php';
if (empty($localConfig['automatic_match_updates'])) {
    echo "Automatische Auswertung nicht aktiviert.\n";
    exit;
}
try {
    $db = mysqli_connect(HOST, DB_USER, DB_PASSWD, DB_NAME);
    mysqli_set_charset($db, 'utf8mb4');
    $updater = new MatchUpdate($db);
    $startedAt = microtime(true);
    $hadErrors = false;
    $liveOnly = false;
    do {
        $result = $updater->run(null, $liveOnly);
        $line = date('c') . ' ' . json_encode($result) . PHP_EOL;
        echo $line;
        error_log($line, 3, sys_get_temp_dir() . '/bl-wette-match-update.log');
        if (!empty($result['errors'])) $hadErrors = true;

        $runningResult = mysqli_query($db, 'SELECT EXISTS(SELECT 1 FROM tblspieltag WHERE intStatus=1)');
        $hasRunningMatch = $runningResult && (int)mysqli_fetch_row($runningResult)[0] === 1;
        // The five-minute scheduler remains responsible for detecting a new
        // kickoff.  Once a match is running, this process polls OpenLigaDB
        // every ten seconds until the final whistle or shortly before the
        // next scheduled invocation.
        if (!$hasRunningMatch || microtime(true) - $startedAt >= 280) break;
        $liveOnly = true;
        sleep(10);
    } while (true);
    exit($hadErrors ? 1 : 0);
} catch (Throwable $error) {
    $line = date('c') . ' Auswertung fehlgeschlagen: ' . $error->getMessage() . PHP_EOL;
    fwrite(STDERR, $line);
    error_log($line, 3, sys_get_temp_dir() . '/bl-wette-match-update.log');
    exit(1);
}
