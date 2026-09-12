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
    $result = (new MatchUpdate($db))->run();
    $line = date('c') . ' ' . json_encode($result) . PHP_EOL;
    echo $line;
    error_log($line, 3, sys_get_temp_dir() . '/bl-wette-match-update.log');
    exit(empty($result['errors']) ? 0 : 1);
} catch (Throwable $error) {
    $line = date('c') . ' Auswertung fehlgeschlagen: ' . $error->getMessage() . PHP_EOL;
    fwrite(STDERR, $line);
    error_log($line, 3, sys_get_temp_dir() . '/bl-wette-match-update.log');
    exit(1);
}
