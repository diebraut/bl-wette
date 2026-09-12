<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
ini_set('display_errors', '0');
require __DIR__ . '/../common/include.php';
$db = mysqli_connect(HOST, DB_USER, DB_PASSWD, DB_NAME);
mysqli_set_charset($db, 'utf8mb4');
$token = $_POST['user'] ?? '';
if (!is_string($token) || !preg_match('/^[a-zA-Z0-9]{1,128}$/D', $token)) {
    http_response_code(401); echo '{}'; exit;
}
$statement = mysqli_prepare($db, 'SELECT intUser FROM tblsession WHERE strSessionid=?');
mysqli_stmt_bind_param($statement, 's', $token);
mysqli_stmt_execute($statement);
if (!mysqli_fetch_assoc(mysqli_stmt_get_result($statement))) {
    http_response_code(401); echo '{}'; exit;
}
$currentDay = (int)mysqli_fetch_row(mysqli_query($db, 'SELECT intDay FROM tblinfo LIMIT 1'))[0];
$actDay = max(1, min(34, (int)($_POST['day'] ?? $currentDay)));
$view = $_POST['view'] ?? '';
$previousVersion = $_POST['version'] ?? '';
$templates = ['table' => 'usertable.php', 'bltable' => 'bltable.php', 'day' => 'daystat.php'];
$liveFragment = true;
$menuuser = $token;
$user = $token;
$_GET = [];
$_POST = ['user' => $token, 'action' => '', 'xpage' => 1];
ob_start();
// Only read-only templates, never the login POST handler. No session keepalive.
if (isset($templates[$view])) include __DIR__ . '/' . $templates[$view];
$html = ob_get_clean();
$clubs = mysqli_fetch_all(mysqli_query($db, 'SELECT strName,intPoint,CAST(intGoal AS SIGNED)-CAST(intGGoal AS SIGNED) AS diff FROM tblverein ORDER BY intPoint DESC,diff DESC,intGoal DESC,lngIndex ASC'), MYSQLI_ASSOC);
$payload = ['html' => $html, 'clubs' => $clubs, 'currentDay' => $currentDay];
// Hash the actual view, including result corrections and matchday advancement.
// No timestamps: an unchanged view must have an unchanged version.
$version = hash('sha256', json_encode($payload, JSON_INVALID_UTF8_SUBSTITUTE));
if (is_string($previousVersion) && hash_equals($version, $previousVersion)) {
    echo json_encode(['changed' => false, 'version' => $version]);
    exit;
}
echo json_encode(['changed' => true, 'version' => $version] + $payload, JSON_INVALID_UTF8_SUBSTITUTE);
