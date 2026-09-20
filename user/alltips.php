<?php
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
require __DIR__ . '/../common/include.php';
$db = mysqli_connect(HOST, DB_USER, DB_PASSWD, DB_NAME);
mysqli_set_charset($db, 'utf8mb4');
function tipsEscape($v) { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function tipsFormatDayDate($date, $withYear = true) {
    $timestamp = strtotime($date);
    if ($timestamp === false) return '';
    $weekdays = ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'];
    return $weekdays[(int)date('w', $timestamp)] . ' ' . date($withYear ? 'd.m.y' : 'd.m.', $timestamp);
}
function tipsDayDateText($option) {
    $firstRaw = $option['firstDate'] ?? '';
    $lastRaw = $option['lastDate'] ?? '';
    if ($firstRaw === '') return 'Termin offen';
    if ($firstRaw === $lastRaw || $lastRaw === '') return tipsFormatDayDate($firstRaw);
    $sameYear = date('Y', strtotime($firstRaw)) === date('Y', strtotime($lastRaw));
    return tipsFormatDayDate($firstRaw, !$sameYear) . ' – ' . tipsFormatDayDate($lastRaw);
}
$dayOptions = mysqli_fetch_all(mysqli_query($db, 'SELECT intTag,DATE(MIN(dtmStart)) AS firstDate,DATE(MAX(dtmStart)) AS lastDate FROM tblspieltag GROUP BY intTag ORDER BY intTag'), MYSQLI_ASSOC);
$days = array_column($dayOptions, 'intTag');
$info = mysqli_fetch_assoc(mysqli_query($db, 'SELECT intDay FROM tblinfo LIMIT 1'));
$currentDay = (int)($info['intDay'] ?? 1);
$currentDayStarted = (int)mysqli_fetch_row(mysqli_query($db,
    "SELECT EXISTS(SELECT 1 FROM tblspieltag WHERE intTag=$currentDay AND intStatus>=1)"))[0] === 1;
$sessionToken = $_GET['user'] ?? '';
$currentUserId = 0;
if (is_string($sessionToken) && preg_match('/^[a-zA-Z0-9]{1,128}$/D', $sessionToken)) {
    $sessionStatement = mysqli_prepare($db, 'SELECT intUser FROM tblsession WHERE strSessionid=?');
    mysqli_stmt_bind_param($sessionStatement, 's', $sessionToken);
    mysqli_stmt_execute($sessionStatement);
    $sessionRow = mysqli_fetch_assoc(mysqli_stmt_get_result($sessionStatement));
    if ($sessionRow) $currentUserId = (int)$sessionRow['intUser'];
} else {
    $sessionToken = '';
}
$simulateRunning = ($_GET['simulate'] ?? '') === 'running';
$day = (int)($_GET['day'] ?? ($simulateRunning ? 4 : ($info['intDay'] ?? 1)));
if (!in_array($day, array_map('intval', $days), true)) $day = (int)($info['intDay'] ?? ($days[0] ?? 1));
$matches = mysqli_fetch_all(mysqli_query($db, "SELECT s.lngIndex AS gameId,s.intStatus,s.intGoal1,s.intGoal2,s.dtmStart,
    s.intVerein1 AS team1Id,s.intVerein2 AS team2Id,
    v1.strName AS team1,v2.strName AS team2 FROM tblspieltag s
    LEFT JOIN tblverein v1 ON v1.lngIndex=s.intVerein1 LEFT JOIN tblverein v2 ON v2.lngIndex=s.intVerein2
    WHERE s.intTag=$day ORDER BY s.dtmStart IS NULL,s.dtmStart,s.lngIndex"), MYSQLI_ASSOC);
$simulatedRunningGameId = 0;
if ($simulateRunning && $matches) {
    foreach ($matches as $match) {
        if (!empty($match['dtmStart']) && (int)date('N', strtotime($match['dtmStart'])) === 5) {
            $simulatedRunningGameId = (int)$match['gameId'];
            break;
        }
    }
    if ($simulatedRunningGameId === 0) $simulatedRunningGameId = (int)$matches[0]['gameId'];
}
foreach ($matches as &$match) {
    $match['isSimulationGame'] = (int)$match['gameId'] === $simulatedRunningGameId;
    $match['isRunning'] = !$match['isSimulationGame'] && (int)$match['intStatus'] === 1;
}
unset($match);
$hasRunningMatches = false;
foreach ($matches as $match) {
    if ($match['isRunning']) {
        $hasRunningMatches = true;
        break;
    }
}
$players = mysqli_fetch_all(mysqli_query($db, 'SELECT lngIndex AS userId,strAlias,intPoint FROM tblgamer ORDER BY intPoint DESC,strAlias,lngIndex'), MYSQLI_ASSOC);
$overallRankByUser = [];
$previousOverallPoints = null;
$overallRank = 0;
foreach ($players as $overallIndex => $overallPlayer) {
    $overallPoints = (int)$overallPlayer['intPoint'];
    if ($previousOverallPoints === null || $overallPoints !== $previousOverallPoints) {
        $overallRank = $overallIndex + 1;
    }
    $overallRankByUser[(int)$overallPlayer['userId']] = $overallRank;
    $previousOverallPoints = $overallPoints;
}
// Running matches contribute provisional points; MatchUpdate persists totals only after the final whistle.
$rows = mysqli_fetch_all(mysqli_query($db, "SELECT w.intUserid,w.intSpielid,w.intGoal1,w.intGoal2,
    CASE WHEN s.intStatus>=1 AND SIGN(w.intGoal1-w.intGoal2)=SIGN(s.intGoal1-s.intGoal2)
    THEN 1+(w.intGoal1=s.intGoal1)+(w.intGoal2=s.intGoal2)+(w.intGoal1-w.intGoal2=s.intGoal1-s.intGoal2)
    ELSE 0 END AS points FROM tblwette w JOIN tblspieltag s ON s.lngIndex=w.intSpielid WHERE s.intTag=$day"), MYSQLI_ASSOC);
$tips = []; $dayPoints = [];
foreach ($rows as $tip) {
    $id = (int)$tip['intUserid'];
    if ((int)$tip['intSpielid'] === $simulatedRunningGameId) $tip['points'] = 0;
    $tips[$id][(int)$tip['intSpielid']] = $tip;
    $dayPoints[$id] = ($dayPoints[$id] ?? 0) + (int)$tip['points'];
}
usort($players, function ($a, $b) use ($dayPoints) {
    $byDay = ($dayPoints[(int)$b['userId']] ?? 0) <=> ($dayPoints[(int)$a['userId']] ?? 0);
    if ($byDay !== 0) return $byDay;
    $byTotal = (int)$b['intPoint'] <=> (int)$a['intPoint'];
    return $byTotal !== 0 ? $byTotal : strcmp($a['strAlias'], $b['strAlias']);
});
$now = date('Y-m-d H:i:s');
$upcomingByTeam = [];
$displayedGameIds = array_map(function ($match) { return (int)$match['gameId']; }, $matches);
$displayedGameFilter = $displayedGameIds ? ' AND s.lngIndex NOT IN (' . implode(',', $displayedGameIds) . ')' : '';
$upcomingRows = mysqli_query($db, "SELECT s.intTag,s.dtmStart,s.intVerein1 AS team1Id,s.intVerein2 AS team2Id,
    v1.strName AS team1,v2.strName AS team2 FROM tblspieltag s
    LEFT JOIN tblverein v1 ON v1.lngIndex=s.intVerein1 LEFT JOIN tblverein v2 ON v2.lngIndex=s.intVerein2
    WHERE s.intStatus<>2 AND s.dtmStart>='$now'$displayedGameFilter ORDER BY s.dtmStart,s.lngIndex");
while ($fixture = mysqli_fetch_assoc($upcomingRows)) {
    $startTimestamp = strtotime($fixture['dtmStart']);
    $when = $startTimestamp === false ? 'Termin offen'
        : tipsFormatDayDate(date('Y-m-d', $startTimestamp)) . ', ' . date('H:i', $startTimestamp);
    $homeId = (int)$fixture['team1Id'];
    $awayId = (int)$fixture['team2Id'];
    $upcomingByTeam[$homeId][] = ['side' => 'H', 'opponent' => $fixture['team2'],
        'day' => (int)$fixture['intTag'], 'when' => $when];
    $upcomingByTeam[$awayId][] = ['side' => 'A', 'opponent' => $fixture['team1'],
        'day' => (int)$fixture['intTag'], 'when' => $when];
}
$weekdays = ['So','Mo','Di','Mi','Do','Fr','Sa'];
$dayStarted = false;
foreach ($matches as $match) { if ((int)$match['intStatus'] >= 1 || $match['isRunning']) $dayStarted = true; }
$liveMatches = [];
foreach ($matches as $match) {
    $visible = $match['isSimulationGame'] ? false : (CONST_PER_MATCH_DEADLINE
        ? ($match['isRunning'] || (int)$match['intStatus'] >= 1 || (!empty($match['dtmStart']) && $match['dtmStart'] <= $now))
        : $dayStarted);
    $liveMatches[] = [
        (int)$match['gameId'], (int)$match['intStatus'], $match['intGoal1'], $match['intGoal2'],
        $match['dtmStart'], $match['team1'], $match['team2'], $visible, $match['isRunning'], $match['isSimulationGame']
    ];
}
$livePlayers = [];
foreach ($players as $player) {
    $userId = (int)$player['userId'];
    $visibleTips = [];
    foreach ($matches as $matchIndex => $match) {
        if ($userId !== $currentUserId && !$liveMatches[$matchIndex][7]) {
            $visibleTips[] = null;
            continue;
        }
        $tip = $tips[$userId][(int)$match['gameId']] ?? null;
        $visibleTips[] = $tip
            ? [$tip['intGoal1'], $tip['intGoal2'], (int)$tip['points']]
            : null;
    }
    $livePlayers[] = [$userId, $player['strAlias'], (int)$player['intPoint'],
        $overallRankByUser[$userId], $dayPoints[$userId] ?? 0, $visibleTips];
}
$liveVersion = hash('sha256', json_encode([$day, $currentDay, $currentDayStarted, $liveMatches, $livePlayers], JSON_INVALID_UTF8_SUBSTITUTE));
?>
<!doctype html>
<html lang="de"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Alle Tipps – Spieltag <?php echo $day; ?></title>
<link rel="stylesheet" href="alltips.css?v=<?php echo (int)filemtime(__DIR__ . '/alltips.css'); ?>">
</head><body>
<header class="site-header">Bundesliga Tippspiel</header>
<main data-live-version="<?php echo $liveVersion; ?>" data-current-day="<?php echo $currentDay; ?>" data-current-day-started="<?php echo $currentDayStarted ? '1' : '0'; ?>" data-running-matches="<?php echo $hasRunningMatches ? '1' : '0'; ?>"><h1>Alle Tipps</h1>
<div class="toolbar">
<div class="day-picker-control"><span class="day-picker-label">Spieltag</span>
<details class="day-picker"><summary><?php
    $selectedDateText = 'Termin offen';
    foreach ($dayOptions as $option) {
        if ((int)$option['intTag'] === $day) $selectedDateText = tipsDayDateText($option);
    }
?><span class="day-number"><?php echo $day; ?></span><span class="day-date">(<?php echo tipsEscape($selectedDateText); ?>)</span></summary>
<div class="day-options" role="listbox" aria-label="Spieltag auswählen">
<?php foreach ($dayOptions as $option):
    $optionDay = (int)$option['intTag'];
    $optionParams = ['day' => $optionDay];
    if ($sessionToken !== '') $optionParams['user'] = $sessionToken;
    if ($simulateRunning) $optionParams['simulate'] = 'running';
    $optionUrl = 'alltips.php?' . http_build_query($optionParams, '', '&', PHP_QUERY_RFC3986); ?>
<a href="<?php echo tipsEscape($optionUrl); ?>" role="option" aria-selected="<?php echo $optionDay === $day ? 'true' : 'false'; ?>"><span class="day-number"><?php echo $optionDay; ?></span><span class="day-date">(<?php echo tipsEscape(tipsDayDateText($option)); ?>)</span></a>
<?php endforeach; ?></div></details></div>
<span>Sortiert nach Spieltag-Punkten</span>
<?php if ($simulateRunning): ?><div class="simulation-controls" aria-label="Spielsimulation">
<button type="button" data-simulation-action="start">Spiel beginnen</button>
<button type="button" data-simulation-action="away-goal">Tor Gast</button>
<button type="button" data-simulation-action="home-goal">Tor Heim</button>
<button type="button" data-simulation-action="finish">Spiel beenden</button>
</div><?php endif; ?>
<button class="print" onclick="window.print()">Tipps drucken</button>
</div>
<div class="table-scroll" role="region" aria-label="Tipps aller Spieler" tabindex="0"><table>
<thead><tr>
<th scope="col" rowspan="2">Rang</th><th scope="col" rowspan="2" class="player">Spieler</th>
<th scope="col" rowspan="2" class="day-points" aria-sort="descending">Spieltag-<br>Punkte ↓</th>
<?php foreach ($matches as $match): $start = !empty($match['dtmStart']) ? strtotime($match['dtmStart']) : false; ?>
<th scope="col" class="match<?php echo $match['isSimulationGame'] ? ' simulation-game' : ''; ?><?php echo $match['isRunning'] ? ' running-game' : ''; ?>"><span><button type="button" class="club-name" aria-haspopup="listbox" aria-expanded="false" data-club-name="<?php echo tipsEscape($match['team1']); ?>" data-club-fixtures="<?php echo tipsEscape(json_encode($upcomingByTeam[(int)$match['team1Id']] ?? [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE)); ?>"><?php echo tipsEscape($match['team1']); ?></button></span><span class="versus">–</span>
<span><button type="button" class="club-name" aria-haspopup="listbox" aria-expanded="false" data-club-name="<?php echo tipsEscape($match['team2']); ?>" data-club-fixtures="<?php echo tipsEscape(json_encode($upcomingByTeam[(int)$match['team2Id']] ?? [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE)); ?>"><?php echo tipsEscape($match['team2']); ?></button></span>
<small><?php echo $start ? tipsEscape($weekdays[(int)date('w',$start)] . ' ' . date('d.m. H:i',$start)) : 'Termin offen'; ?></small></th>
<?php endforeach; ?></tr><tr class="results">
<?php foreach ($matches as $match): ?>
<td class="<?php echo $match['isSimulationGame'] ? 'simulation-game simulation-result' : ''; ?><?php echo $match['isRunning'] ? ' running-game' : ''; ?>"><?php echo $match['isSimulationGame'] ? 'Offen'
    : ($match['isRunning'] ? '<span class="live-dot" aria-hidden="true"></span>Spiel läuft · ' . (int)$match['intGoal1'] . ':' . (int)$match['intGoal2']
    : ((int)$match['intStatus'] === 2 ? 'Endstand ' . (int)$match['intGoal1'] . ':' . (int)$match['intGoal2']
    : (((int)$match['intStatus'] >= 1 || (!empty($match['dtmStart']) && $match['dtmStart'] <= $now)) ? 'Ergebnis offen' : 'Offen'))); ?></td>
<?php endforeach; ?></tr></thead><tbody>
<?php $previousPoints = null; $rank = 0; foreach ($players as $index => $player):
    $id = (int)$player['userId'];
    $rankingPoints = $dayPoints[$id] ?? 0;
    if ($previousPoints !== $rankingPoints) $rank = $index + 1;
    $previousPoints = $rankingPoints; ?>
<tr data-user-id="<?php echo $id; ?>" data-player-name="<?php echo tipsEscape($player['strAlias']); ?>" data-total-points="<?php echo (int)$player['intPoint']; ?>" data-base-day-points="<?php echo $dayPoints[$id] ?? 0; ?>"><td class="rank-value"><?php echo $rank; ?></td><th scope="row" class="player"><?php echo tipsEscape($player['strAlias']); ?> (<?php echo (int)$player['intPoint']; ?>, <?php echo $overallRankByUser[$id]; ?>)</th>
<td class="day-points"><?php echo $dayPoints[$id] ?? 0; ?></td>
<?php foreach ($matches as $match):
    $visible = $id === $currentUserId || ($match['isSimulationGame'] ? false : (CONST_PER_MATCH_DEADLINE ? ($match['isRunning'] || (int)$match['intStatus'] >= 1 || (!empty($match['dtmStart']) && $match['dtmStart'] <= $now)) : $dayStarted));
    $tip = $tips[$id][(int)$match['gameId']] ?? null;
    $hasTip = $tip && $tip['intGoal1'] !== null && $tip['intGoal2'] !== null;
    $points = $hasTip ? (int)$tip['points'] : 0; ?>
<td class="<?php echo trim(($match['isSimulationGame'] ? 'simulation-game simulation-tip ' : '') . ($id === $currentUserId ? 'own-tip ' : '') . ($match['isRunning'] ? 'running-game ' : '') . (!$visible ? 'hidden-tip' : '')); ?>"
<?php if ($match['isSimulationGame']): ?>data-tip-home="<?php echo $hasTip ? (int)$tip['intGoal1'] : ''; ?>" data-tip-away="<?php echo $hasTip ? (int)$tip['intGoal2'] : ''; ?>"<?php endif; ?>
title="<?php echo !$visible ? 'Tipps werden erst nach Anpfiff sichtbar' : ($hasTip && (int)$match['intStatus'] >= 1 ? $points . ((int)$match['intStatus'] === 2 ? ' Punkte für den Endstand' : ' Punkte beim aktuellen Spielstand') : ''); ?>">
<span class="<?php echo $visible && $points > 0 ? 'scored scored-' . min(4, $points) : ''; ?>"><?php echo !$visible ? 'Verdeckt' : ($hasTip ? (int)$tip['intGoal1'] . ':' . (int)$tip['intGoal2'] : '-:-'); ?><?php if ($visible && $hasTip && $points > 0) echo ' (' . $points . ')'; ?></span></td>
<?php endforeach; ?></tr><?php endforeach; ?>
<?php if (!$players): ?><tr><td colspan="<?php echo 3 + count($matches); ?>">Noch keine Spieler vorhanden.</td></tr><?php endif; ?>
</tbody></table></div>
<?php if (!$matches): ?><p>Für diesen Spieltag sind noch keine Paarungen vorhanden.</p><?php endif; ?>
<footer><p><strong>Spieltag-Punkte:</strong> aktuelle Punkte aus laufenden und beendeten Begegnungen.</p>
<p>-:- Kein Tipp · Verdeckt: Freigabe nach Anpfiff · Grün: Punkte erzielt</p></footer>
</main>
<div id="club-fixture-popup" class="club-fixture-popup" role="dialog" aria-modal="false" aria-labelledby="club-fixture-title" hidden>
<strong id="club-fixture-title"></strong><div class="club-fixture-options" role="listbox" aria-label="Nächste Partien"></div>
</div>
<script>
(function () {
    'use strict';
    var day = <?php echo $day; ?>;
    var simulateRunning = <?php echo $simulateRunning ? 'true' : 'false'; ?>;
    var sessionToken = <?php echo json_encode($sessionToken, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    var observedCurrentDay = <?php echo $currentDay; ?>;
    var observedCurrentDayStarted = <?php echo $currentDayStarted ? 'true' : 'false'; ?>;
    var hasRunningMatches = <?php echo $hasRunningMatches ? 'true' : 'false'; ?>;
    var refreshing = false;
    var refreshTimer = null;
    var simulationState = {
        phase: simulateRunning ? 'open' : 'off',
        home: 0,
        away: 0
    };
    var activeClubFixtures = [];
    var visibleClubFixtureCount = 10;

    function simulatedTipPoints(tipHome, tipAway) {
        var resultDifference = simulationState.home - simulationState.away;
        var tipDifference = tipHome - tipAway;
        if (Math.sign(resultDifference) !== Math.sign(tipDifference)) return 0;
        return 1
            + (tipHome === simulationState.home ? 1 : 0)
            + (tipAway === simulationState.away ? 1 : 0)
            + (tipDifference === resultDifference ? 1 : 0);
    }

    function updateSimulatedRanking() {
        var body = document.querySelector('tbody');
        if (!body) return;
        var rows = Array.from(body.querySelectorAll('tr[data-user-id]'));
        rows.forEach(function (row) {
            var tipCell = row.querySelector('.simulation-tip');
            var tipHomeText = tipCell ? String(tipCell.dataset.tipHome || '').trim() : '';
            var tipAwayText = tipCell ? String(tipCell.dataset.tipAway || '').trim() : '';
            var tipHome = tipHomeText !== '' ? Number(tipHomeText) : NaN;
            var tipAway = tipAwayText !== '' ? Number(tipAwayText) : NaN;
            var isOwnTip = tipCell && tipCell.classList.contains('own-tip');
            var isHidden = simulationState.phase === 'open' && !isOwnTip;
            var points = simulationState.phase !== 'open' && simulationState.phase !== 'off'
                && Number.isFinite(tipHome) && Number.isFinite(tipAway)
                ? simulatedTipPoints(tipHome, tipAway) : 0;
            var dayPoints = Number(row.dataset.baseDayPoints || 0) + points;
            row.dataset.currentDayPoints = String(dayPoints);
            var dayPointsCell = row.querySelector('.day-points');
            if (dayPointsCell) dayPointsCell.textContent = String(dayPoints);
            if (tipCell) {
                var tipText = Number.isFinite(tipHome) && Number.isFinite(tipAway) ? tipHome + ':' + tipAway : '-:-';
                var tipSpan = tipCell.querySelector('span');
                if (tipSpan) {
                    tipSpan.textContent = isHidden
                        ? 'Verdeckt'
                        : tipText + (points > 0 ? ' (' + points + ')' : '');
                    tipSpan.className = points > 0 ? 'scored scored-' + Math.min(4, points) : '';
                }
                tipCell.classList.toggle('hidden-tip', isHidden);
                tipCell.title = isHidden
                    ? 'Tipps werden erst nach Anpfiff sichtbar'
                    : (simulationState.phase === 'open' ? 'Eigener Tipp' : (simulationState.phase === 'finished'
                    ? points + ' Punkte für den Endstand'
                    : points + ' Punkte beim aktuellen Spielstand'));
            }
        });
        rows.sort(function (left, right) {
            var byDay = Number(right.dataset.currentDayPoints) - Number(left.dataset.currentDayPoints);
            if (byDay !== 0) return byDay;
            var byTotal = Number(right.dataset.totalPoints) - Number(left.dataset.totalPoints);
            if (byTotal !== 0) return byTotal;
            return String(left.dataset.playerName).localeCompare(String(right.dataset.playerName), 'de');
        });
        rows.forEach(function (row) { body.appendChild(row); });
        var previousKey = null;
        var rank = 0;
        rows.forEach(function (row, index) {
            var key = row.dataset.currentDayPoints;
            if (key !== previousKey) rank = index + 1;
            previousKey = key;
            var rankCell = row.querySelector('.rank-value');
            if (rankCell) rankCell.textContent = String(rank);
        });
    }

    function renderSimulation() {
        if (!simulateRunning) return;
        var isRunning = simulationState.phase === 'running';
        document.querySelectorAll('.simulation-game').forEach(function (cell) {
            cell.classList.toggle('running-game', isRunning);
        });
        var resultCell = document.querySelector('.simulation-result');
        if (resultCell) {
            resultCell.innerHTML = simulationState.phase === 'open'
                ? 'Offen'
                : (isRunning
                ? '<span class="live-dot" aria-hidden="true"></span>Spiel läuft · ' + simulationState.home + ':' + simulationState.away
                : 'Endstand ' + simulationState.home + ':' + simulationState.away);
        }
        var startButton = document.querySelector('[data-simulation-action="start"]');
        if (startButton) startButton.disabled = isRunning;
        document.querySelectorAll('[data-simulation-action="away-goal"], [data-simulation-action="home-goal"], [data-simulation-action="finish"]').forEach(function (button) {
            button.disabled = !isRunning;
        });
        updateSimulatedRanking();
    }

    function closeClubFixtures(returnFocus) {
        var popup = document.getElementById('club-fixture-popup');
        var opener = document.querySelector('.club-name[aria-expanded="true"]');
        if (popup) popup.hidden = true;
        document.querySelectorAll('.club-name[aria-expanded="true"]').forEach(function (button) {
            button.setAttribute('aria-expanded', 'false');
        });
        if (returnFocus && opener) opener.focus();
    }

    function renderClubFixtures() {
        var popup = document.getElementById('club-fixture-popup');
        if (!popup) return;
        var options = popup.querySelector('.club-fixture-options');
        options.replaceChildren();
        if (!activeClubFixtures.length) {
            var empty = document.createElement('div');
            empty.className = 'club-fixture-empty';
            empty.textContent = 'Keine weiteren Partien vorhanden.';
            options.appendChild(empty);
            return;
        }
        activeClubFixtures.slice(0, visibleClubFixtureCount).forEach(function (fixture) {
            var option = document.createElement('div');
            option.className = 'club-fixture-option';
            option.setAttribute('role', 'option');
            option.tabIndex = -1;
            var opponent = document.createElement('span');
            opponent.className = 'club-fixture-opponent';
            opponent.textContent = fixture.side + ' ' + fixture.opponent;
            var details = document.createElement('small');
            details.textContent = fixture.day + '. Spieltag · ' + fixture.when;
            option.append(opponent, details);
            options.appendChild(option);
        });
        if (visibleClubFixtureCount < activeClubFixtures.length) {
            var more = document.createElement('button');
            more.type = 'button';
            more.className = 'club-fixture-more';
            more.textContent = 'Weitere Partien';
            options.appendChild(more);
        }
    }

    function openClubFixtures(button) {
        var popup = document.getElementById('club-fixture-popup');
        if (!popup) return;
        closeClubFixtures(false);
        try { activeClubFixtures = JSON.parse(button.dataset.clubFixtures || '[]'); }
        catch (error) { activeClubFixtures = []; }
        visibleClubFixtureCount = 10;
        var title = popup.querySelector('#club-fixture-title');
        title.textContent = 'Nächste Partien: ' + (button.dataset.clubName || button.textContent.trim());
        renderClubFixtures();
        popup.hidden = false;
        button.setAttribute('aria-expanded', 'true');
        var rect = button.getBoundingClientRect();
        var popupRect = popup.getBoundingClientRect();
        var pageZoom = parseFloat(window.getComputedStyle(document.body).zoom) || 1;
        var gap = 8;
        var left = rect.right + gap;
        if (left + popupRect.width > window.innerWidth - gap) {
            left = rect.left - popupRect.width - gap;
        }
        left = Math.max(gap, Math.min(left, window.innerWidth - popupRect.width - gap));
        var top = Math.max(gap, Math.min(rect.top, window.innerHeight - popupRect.height - gap));
        popup.style.left = (left / pageZoom) + 'px';
        popup.style.top = (top / pageZoom) + 'px';
    }

    document.addEventListener('click', function (event) {
        var dayPicker = document.querySelector('.day-picker[open]');
        if (dayPicker && !dayPicker.contains(event.target)) dayPicker.removeAttribute('open');

        var clubButton = event.target.closest('.club-name');
        if (clubButton) {
            if (clubButton.getAttribute('aria-expanded') === 'true') closeClubFixtures(false);
            else openClubFixtures(clubButton);
            return;
        }
        var moreFixtures = event.target.closest('.club-fixture-more');
        if (moreFixtures) {
            var firstNewFixture = visibleClubFixtureCount;
            visibleClubFixtureCount = Math.min(visibleClubFixtureCount + 10, activeClubFixtures.length);
            renderClubFixtures();
            var displayedFixtures = document.querySelectorAll('.club-fixture-option');
            if (displayedFixtures[firstNewFixture]) displayedFixtures[firstNewFixture].focus();
            return;
        }
        var fixturePopup = document.getElementById('club-fixture-popup');
        if (fixturePopup && !fixturePopup.hidden && !fixturePopup.contains(event.target)) closeClubFixtures(false);

        var button = event.target.closest('[data-simulation-action]');
        if (!button) return;
        var action = button.dataset.simulationAction;
        if (action === 'start') {
            simulationState.phase = 'running';
            simulationState.home = 0;
            simulationState.away = 0;
        } else if (action === 'home-goal' && simulationState.phase === 'running') {
            simulationState.home++;
        } else if (action === 'away-goal' && simulationState.phase === 'running') {
            simulationState.away++;
        } else if (action === 'finish' && simulationState.phase === 'running') {
            simulationState.phase = 'finished';
        }
        renderSimulation();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        var dayPicker = document.querySelector('.day-picker[open]');
        var fixturePopup = document.getElementById('club-fixture-popup');
        var handled = false;
        if (dayPicker) {
            dayPicker.removeAttribute('open');
            var summary = dayPicker.querySelector('summary');
            if (summary) summary.focus();
            handled = true;
        }
        if (fixturePopup && !fixturePopup.hidden) {
            closeClubFixtures(true);
            handled = true;
        }
        if (handled) event.preventDefault();
    });

    window.addEventListener('resize', function () { closeClubFixtures(false); });

    async function refreshAllTips() {
        if (refreshTimer !== null) window.clearTimeout(refreshTimer);
        refreshTimer = null;
        if (refreshing || document.hidden) return;
        refreshing = true;
        var controller = new AbortController();
        var timeout = window.setTimeout(function () { controller.abort(); }, 10000);
        try {
            var refreshUrl = new URL('alltips.php', window.location.href);
            refreshUrl.searchParams.set('day', String(day));
            refreshUrl.searchParams.set('live', '1');
            refreshUrl.searchParams.set('_', String(Date.now()));
            if (simulateRunning) refreshUrl.searchParams.set('simulate', 'running');
            if (sessionToken) refreshUrl.searchParams.set('user', sessionToken);
            var response = await fetch(refreshUrl.pathname + refreshUrl.search, {
                cache: 'no-store',
                credentials: 'same-origin',
                signal: controller.signal
            });
            if (!response.ok) return;
            var html = await response.text();
            var nextDocument = new DOMParser().parseFromString(html, 'text/html');
            var currentMain = document.querySelector('main[data-live-version]');
            var nextMain = nextDocument.querySelector('main[data-live-version]');
            if (!currentMain || !nextMain || currentMain.dataset.liveVersion === nextMain.dataset.liveVersion) return;

            var nextCurrentDay = Number(nextMain.dataset.currentDay);
            var nextCurrentDayStarted = nextMain.dataset.currentDayStarted === '1';
            hasRunningMatches = nextMain.dataset.runningMatches === '1';
            if (Number.isInteger(nextCurrentDay) && nextCurrentDay !== observedCurrentDay) {
                observedCurrentDay = nextCurrentDay;
                observedCurrentDayStarted = false;
            }
            if (!simulateRunning && nextCurrentDayStarted && !observedCurrentDayStarted && day !== nextCurrentDay) {
                var targetUrl = new URL('alltips.php', window.location.href);
                targetUrl.searchParams.set('day', String(nextCurrentDay));
                targetUrl.searchParams.delete('live');
                targetUrl.searchParams.delete('_');
                if (sessionToken) targetUrl.searchParams.set('user', sessionToken);
                window.location.replace(targetUrl.pathname + targetUrl.search);
                return;
            }
            observedCurrentDayStarted = nextCurrentDayStarted;

            var currentTable = currentMain.querySelector('.table-scroll');
            var scrollLeft = currentTable ? currentTable.scrollLeft : 0;
            var scrollTop = currentTable ? currentTable.scrollTop : 0;
            closeClubFixtures(false);
            currentMain.innerHTML = nextMain.innerHTML;
            currentMain.dataset.liveVersion = nextMain.dataset.liveVersion;
            currentMain.dataset.runningMatches = nextMain.dataset.runningMatches;
            var nextTable = currentMain.querySelector('.table-scroll');
            if (nextTable) {
                nextTable.scrollLeft = scrollLeft;
                nextTable.scrollTop = scrollTop;
            }
            renderSimulation();
        } catch (error) {
            if (error.name !== 'AbortError') console.warn('Alltips-Aktualisierung fehlgeschlagen:', error);
        } finally {
            window.clearTimeout(timeout);
            refreshing = false;
            if (!document.hidden) scheduleRefresh();
        }
    }

    function scheduleRefresh() {
        if (refreshTimer !== null) window.clearTimeout(refreshTimer);
        var running = hasRunningMatches || simulationState.phase === 'running';
        refreshTimer = window.setTimeout(refreshAllTips, running ? 10000 : 30000);
    }

    window.addEventListener('focus', refreshAllTips);
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) refreshAllTips();
    });
    renderSimulation();
    scheduleRefresh();
}());
</script>
</body></html>
