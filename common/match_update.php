<?php
/** Background evaluation; no login/session side effects. */
class MatchUpdate
{
    private $db;
    private $fetch;

    public function __construct($db, $fetch = null)
    {
        $this->db = $db;
        $this->fetch = $fetch ?: function ($day) {
            $ch = curl_init('https://api.openligadb.de/getmatchdata/' . CONST_LIGA . '/' . CONST_LIGA_SEASON . '/' . (int)$day);
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 15, CURLOPT_USERAGENT => 'bl-wette result updater']);
            $body = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $data = is_string($body) ? json_decode($body, true) : null;
            if ($status !== 200 || !is_array($data) || !$data) {
                throw new RuntimeException('OpenLigaDB: keine gueltigen Daten fuer Spieltag ' . $day);
            }
            return $data;
        };
    }

    private function query($sql)
    {
        $result = mysqli_query($this->db, $sql);
        if ($result === false) throw new RuntimeException(mysqli_error($this->db));
        return $result;
    }

    public static function finalScore($match)
    {
        if (($match['matchIsFinished'] ?? false) !== true) return null;
        foreach ($match['matchResults'] ?? [] as $result) {
            if ((int)($result['resultTypeID'] ?? 0) !== 2) continue;
            $a = $result['pointsTeam1'] ?? null;
            $b = $result['pointsTeam2'] ?? null;
            if (is_int($a) && is_int($b) && $a >= 0 && $b >= 0) return [$a, $b];
        }
        return null;
    }

    private function refreshDay($day, $evaluate)
    {
        $matches = call_user_func($this->fetch, $day);
        $changed = 0;
        foreach ($matches as $match) {
            $id = (int)($match['matchID'] ?? 0);
            if ($id <= 0 || (int)($match['group']['groupOrderID'] ?? 0) !== $day) continue;
            $row = mysqli_fetch_assoc($this->query("SELECT * FROM tblspieltag WHERE intTag=$day AND intMatchIdFromOpenLigaDB=$id"));
            if (!$row) continue;
            $pk = (int)$row['lngIndex'];
            if (!empty($match['matchDateTimeUTC'])) {
                $start = (new DateTimeImmutable($match['matchDateTimeUTC'], new DateTimeZone('UTC')))
                    ->setTimezone(new DateTimeZone('Europe/Berlin'))->format('Y-m-d H:i:s');
                $this->query("UPDATE tblspieltag SET dtmStart='$start' WHERE lngIndex=$pk");
            }
            $score = self::finalScore($match);
            if (!$evaluate || $score === null) continue;
            list($a, $b) = $score;
            if ((int)$row['intStatus'] !== 2 || (int)$row['intGoal1'] !== $a || (int)$row['intGoal2'] !== $b) {
                $this->query("UPDATE tblspieltag SET intStatus=2,intGoal1=$a,intGoal2=$b WHERE lngIndex=$pk");
                $changed++;
            }
        }
        return $changed;
    }

    public function recalculatePoints()
    {
        // Absolute totals: repeat runs and crash recovery never add points twice.
        $clubs = $this->query('SELECT lngIndex FROM tblverein');
        while ($club = mysqli_fetch_assoc($clubs)) {
            $id = (int)$club['lngIndex'];
            $totals = mysqli_fetch_assoc($this->query("SELECT
                COALESCE(SUM(IF(intVerein1=$id,intGoal1,intGoal2)),0) AS scored,
                COALESCE(SUM(IF(intVerein1=$id,intGoal2,intGoal1)),0) AS conceded,
                COALESCE(SUM(IF(intGoal1=intGoal2,1,IF((intVerein1=$id AND intGoal1>intGoal2) OR (intVerein2=$id AND intGoal2>intGoal1),3,0))),0) AS points
                FROM tblspieltag WHERE intStatus=2 AND (intVerein1=$id OR intVerein2=$id)"));
            $this->query("UPDATE tblverein SET intGoal={$totals['scored']},intGGoal={$totals['conceded']},intPoint={$totals['points']} WHERE lngIndex=$id");
        }
        // Same scoring as getUserResultArray(); money and tips are untouched.
        $this->query("UPDATE tblgamer g SET intPoint=COALESCE((SELECT SUM(
            IF(SIGN(w.intGoal1-w.intGoal2)=SIGN(s.intGoal1-s.intGoal2),
                1+(w.intGoal1=s.intGoal1)+(w.intGoal2=s.intGoal2)+(w.intGoal1-w.intGoal2=s.intGoal1-s.intGoal2),0))
            FROM tblwette w JOIN tblspieltag s ON s.lngIndex=w.intSpielid
            WHERE w.intUserid=g.lngIndex AND s.intStatus=2),0)");
    }

    public function advanceCompletedDay()
    {
        $day = (int)mysqli_fetch_assoc($this->query('SELECT intDay FROM tblinfo LIMIT 1'))['intDay'];
        if ($day < 1 || $day >= (int)CONST_NUMBER_OF_MATCH_DAYS) return $day;
        $counts = mysqli_fetch_assoc($this->query("SELECT COUNT(*) AS total,SUM(intStatus<>2) AS unfinished FROM tblspieltag WHERE intTag=$day"));
        if ((int)$counts['total'] !== 9 || (int)$counts['unfinished'] !== 0) return $day;
        $next = $day + 1;
        $dates = mysqli_fetch_assoc($this->query("SELECT COUNT(*) AS total,COUNT(dtmStart) AS dated,MIN(dtmStart) AS firstStart,MAX(dtmStart) AS lastStart FROM tblspieltag WHERE intTag=$next"));
        if ((int)$dates['total'] !== 9 || (int)$dates['dated'] !== 9) return $day;
        $first = mysqli_real_escape_string($this->db, $dates['firstStart']);
        $last = (new DateTimeImmutable($dates['lastStart']))->modify('+2 hours')->format('Y-m-d H:i:s');
        $this->query("UPDATE tblinfo SET intDay=$next,dtmClose='$first',dtmEndOfMatchDay='$last' WHERE intDay=$day");
        return $next;
    }

    public function run($now = null)
    {
        $lock = 'bl-wette-results-' . DB_NAME;
        if ((int)mysqli_fetch_row($this->query("SELECT GET_LOCK('$lock',0)"))[0] !== 1) return ['busy' => true];
        try {
            $now = $now ?: new DateTimeImmutable('now', new DateTimeZone('Europe/Berlin'));
            $due = $now->setTimezone(new DateTimeZone('Europe/Berlin'))->modify('-105 minutes')->format('Y-m-d H:i:s');
            $days = $this->query("SELECT DISTINCT intTag FROM tblspieltag WHERE intStatus<>2 AND dtmStart<='$due' ORDER BY intTag");
            $changed = 0;
            $errors = [];
            while ($row = mysqli_fetch_assoc($days)) {
                $day = (int)$row['intTag'];
                try { $changed += $this->refreshDay($day, true); }
                catch (Throwable $e) { $errors[] = $e->getMessage(); }
                if ($day < (int)CONST_NUMBER_OF_MATCH_DAYS) {
                    try { $this->refreshDay($day + 1, false); }
                    catch (Throwable $e) { $errors[] = $e->getMessage(); }
                }
            }
            // Also repairs totals if a previous run stopped after writing a result.
            $this->recalculatePoints();
            return ['evaluated' => $changed, 'currentDay' => $this->advanceCompletedDay(), 'errors' => $errors];
        } finally {
            $this->query("SELECT RELEASE_LOCK('$lock')");
        }
    }
}
