<?php
ob_start();
header('Content-type: text/html; charset=utf-8');

$localSessionConfig = __DIR__ . '/../common/config.local.php';
$localPersistentLogin = false;
$localSessionLifetime = 1800;
if (is_file($localSessionConfig)) {
        $localSessionOptions = require $localSessionConfig;
        if (!empty($localSessionOptions['persistent_login'])) {
                $localPersistentLogin = true;
                $localSessionLifetime = isset($localSessionOptions['session_timeout_seconds']) ? max(60, (int)$localSessionOptions['session_timeout_seconds']) : 1800;
        }
}
?>
<html lang="de">
   <head>
    <html lang="de">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"> 
                <link rel="stylesheet" type="text/css" href="../common/style.css"/>
                <link rel="stylesheet" type="text/css" href="style.css"/>
                <script language="javascript" src="md5.js"></script>
                <script language="javascript" src="scripte.js"></script>
                <script language="JavaScript1.2"/>
                
                        var old1,old2;
                        function openuser(userid, dayid)
                        {
                                var strURL="usertip.php?user=" + userid + "&day=" + dayid;
                                window.open(strURL, "", "width=454, height=280");
                        }
                        function openall(dayid)
                        {
                                var strURL="alltips.php?day=" + dayid;
                                window.open(strURL, "Alle_Tipps", "width=900, height=330,resizable=yes");
                        }
                        function setHigh(id1,id2)
                        {
                                if(old1 && old2)
                                {
                                        document.getElementById(old1).style.backgroundColor="";
                                        document.getElementById(old2).style.backgroundColor="";
                                        document.getElementById(old1).style.Color="";
                                        document.getElementById(old2).style.Color="";
                                }
                                old1 = id1;
                                old2 = id2;
                                
                                document.getElementById(id1).style.backgroundColor="lightblue";
                                document.getElementById(id2).style.backgroundColor="lightblue";
                        }
                        
                        function openEmail(session)
                        {
                                val="message.php?session=" + session;
                                window.open(val,"Statistik","status=0,width=450, height=300");
                        }
                        
                        function openStat(team1, team2, actday)
                        {
                                val="login.php?action=stat&team1=" + team1 + "&team2=" + team2;
                                window.open(val,"Statistik","status=0,width=500, height=250");
                        }
                        
                        function forgotpwd()
                        {
                                window.open("./forgotpwd.php", "Passwort", "width=402, height=150");
                        }
 
                </script>
        </head>
        <body topmargin="0" leftmargin="0" bottommargin="0" rightmargin="0" marginheight="0" marginwidth="0">

<?php
        error_reporting(E_ALL);
        ini_set('display_errors', 0);
                
        $error = "";
        if(!isset($_POST))
                $_POST  = $HTTP_POST_VARS;
        if(!isset($_GET))
                $_GET  = $HTTP_GET_VARS;
        
        include_once ("../common/include.php");

		$db=mysqli_connect("$address", $dbuser,$dbpasswd,'bl_wette');
		mysqli_query($db,"SET NAMES utf8");

		$actDay = $_GET['actDay'] ?? ($_POST['actDay'] ?? null);
		$action = $_GET['action'] ?? ($_POST['action'] ?? null);
        
        $menuuser = $_POST['user'] ?? '';
        $user = $menuuser;
        $session = $menuuser;
        $closetime = "";

        if ($action === 'logout') {
                if ($menuuser !== '') {
                        $logoutSession = mysqli_real_escape_string($db, $menuuser);
                        mysqli_query($db, "DELETE FROM tblsession WHERE strSessionid='$logoutSession'");
                }
                $menuuser = '';
                $user = '';
                $session = '';
                $action = null;
        }

        $currentDate = date_create_from_format('Y-m-d H:i:s', date('Y-m-d H:i:s'));        

        if(isset($actDay) && $actDay != "")
        {
                include("daystat.php");
        }
        else
        {
            $res = mysqli_query($db,"select intDay, ".
            				 "dtmClose, ".
            				 "dtmEndOfMatchDay AS endMatchDay, ".
            				 "Now() AS cDate ".
            				 "from tblinfo");
            
            $closeDate = mysql_result($res, 0, "dtmClose");
            $fCloseDate = date_create_from_format('Y-m-d H:i:s',$closeDate);
            
            setlocale(LC_TIME, "de_DE.UTF-8");                
            
			if (CONST_PER_MATCH_DEADLINE) {
				$closetime = 'Tippabgabe für jede Paarung bis zum jeweiligen Anpfiff möglich!';
			} elseif (class_exists('IntlDateFormatter')) {
				$fmt = new IntlDateFormatter('de_DE', IntlDateFormatter::FULL, IntlDateFormatter::SHORT, date_default_timezone_get(), null, "EEEE 'den' d. MMMM 'um' HH:mm 'Uhr'");
				$closetime = 'Tippabgabe bis ' . $fmt->format(strtotime($closeDate)) . ' möglich!';
			} else {
				// Fallback ohne Monats-/Wochentagsnamen
				$closetime = 'Tippabgabe bis ' . date('d.m.Y \u\m H:i \U\h\r', strtotime($closeDate)) . ' möglich!';
			}
            
            $statusSpielTag = 0;
            if(mysqli_num_rows($res)!=0)
                    $actDay = mysql_result($res, 0, "intDay");
            else
            {
                    mysqli_query($db, "insert into tblinfo (intDay) Values(1)");
                    $actDay = 1;
            }
            if (CONST_PER_MATCH_DEADLINE)
            {
                    mysqli_query($db, "UPDATE tblspieltag SET intStatus=1 WHERE intStatus=0 AND intTag=$actDay AND dtmStart <= NOW()");
                    $statusSpielTag = mysqli_num_rows(mysqli_query($db, "SELECT lngIndex FROM tblspieltag WHERE intTag=$actDay AND intStatus<>0")) > 0 ? 1 : 0;
            }
            elseif(date_format($currentDate,'Y-m-d H:i:s') > $closeDate )
            {
                    mysqli_query($db, "UPDATE tblspieltag SET intStatus=1 WHERE intStatus=0 AND intTag=$actDay");
                    $statusSpielTag = 1;
            }
            if ( $action == "wetten" ) {
			   $day = $_GET['day'] ?? ($_POST['day'] ?? null);
               if (isset($day)) {
               	  $actDay = $day;
               }
            }
        }
        /* check if the last match finished, in this case switch to the next match day */
        $dtmEndOfMatchDay = mysql_result($res, 0, "endMatchDay");

        $x = mysql_result($res, 0, "cDate");
 
        if ( (date_format($currentDate,'Y-m-d H:i:s') > $dtmEndOfMatchDay) AND ($action == "login")) 
        {
	    $newSeason = new bl_season();
            $newActDay = $newSeason->switchToNextSpieltag($actDay);            
            if ($newActDay > 0 )
            {
              $actDay = $newActDay;
            }
        }
        if(isset($action))
        {
                if($action == "useroffice")
                {
                        include("useroffice.php");
                }
                if($action=="stat")
                {
                        $team1 = $_GET['team1'];
                        $team2 = $_GET['team2'];
                        $scnd = mysqli_query($db, "select * from tblverein where lngIndex=$team1");
                        $ver1 = mysql_result($scnd, 0, "strName");
                        
                        $scnd = mysqli_query($db, "select * from tblverein where lngIndex=$team2");
                        $ver2 = mysql_result($scnd, 0, "strName");
                        $heim = 0;
                        $gast = 0;
                        $cnt = 0;
                        ?>
                <body style="border:none" topmargin="0" leftmargin="0" bottommargin="0" rightmargin="0" marginheight="0" marginwidth="0">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%" class="ttable">
                                <tr>
                                        <td valign="top" align="right" width="300"><br>
                                                <table border="0" cellpadding="0" cellspacing="1" style="border-right:1px solid gray">
                                                        <tr>
                                                                <td>&nbsp;</td>
                                                                <td colspan="2" align="center"><strong><?php $ver1?></strong></td>
                                                        </tr>
                                                        <tr>
                                                                <td class="stathead" width="90">&nbsp;</td>
                                                                <td class="stathead" width="90" align="center">Heimspiele</td>
                                                                <td class="stathead" width="90" align="center">Auswärtsspiele</td>
                                                        </tr>
                                                        <tr>
                                                                <td><strong>Siege</strong></td>
                                                                <td align="center" class="statdata">
                                                                        <?php
                                                                                $tmp = mysqli_num_rows(mysqli_query($db, "SELECT lngIndex from tblspieltag WHERE intVerein1=$team1 AND intGoal1>intGoal2 and intTag<>$actDay and intStatus=2"));
                                                                                $heim += $tmp * 3;
                                                                                $cnt += $tmp;
                                                                                echo $tmp;
                                                                        ?>
                                                                </td>
                                                                <td align="center" class="statdata">
                                                                        <?php echo mysqli_num_rows(mysqli_query($db, "SELECT lngIndex from tblspieltag WHERE intVerein2=$team1 AND intGoal1<intGoal2 and intTag<>$actDay and intStatus=2"))?>
                                                                </td>
                                                        </tr>
                                                        <tr>
                                                                <td><strong>Unentschieden</strong></td>
                                                                <td align="center" class="statdata">
                                                                        <?php
                                                                                $tmp = mysqli_num_rows(mysqli_query($db, "SELECT lngIndex from tblspieltag WHERE intVerein1=$team1 AND intGoal1=intGoal2 and intTag<>$actDay and intStatus=2"));
                                                                                $heim += $tmp;
                                                                                $cnt += $tmp;
                                                                                echo $tmp;
                                                                        ?>
                                                                </td>
                                                                <td align="center" class="statdata">
                                                                        <?php echo mysqli_num_rows(mysqli_query($db, "SELECT lngIndex from tblspieltag WHERE intVerein2=$team1 AND intGoal1=intGoal2 and intTag<>$actDay and intStatus=2"))?>
                                                                </td>
                                                        </tr>
                                                        <tr>
                                                                <td><strong>Niederlagen</strong></td>
                                                                <td align="center" class="statdata">
                                                                        <?php
                                                                                $tmp = mysqli_num_rows(mysqli_query($db, "SELECT lngIndex from tblspieltag WHERE intVerein1=$team1 AND intGoal1<intGoal2 and intTag<>$actDay and intStatus=2"));
                                                                                $cnt += $tmp;
                                                                                echo $tmp;
                                                                        ?>
                                                                </td>
                                                                <td align="center" class="statdata">
                                                                        <?php echo mysqli_num_rows(mysqli_query($db, "SELECT lngIndex from tblspieltag WHERE intVerein2=$team1 AND intGoal1>intGoal2 and intTag<>$actDay and intStatus=2"))?>
                                                                </td>
                                                        </tr>
                                                        <tr>
                                                                <?php
                                                                        $SQL="SELECT *,IF(intVerein1=$team1, 'H', 'A' ) as ort, IF(intVerein1=$team1, IF(intGoal1>intGoal2,2,IF(intGoal1=intGoal2,1,0)),IF(intGoal1<intGoal2,2,IF(intGoal1=intGoal2,1,0))) AS Stat FROM tblspieltag WHERE intTag>=$actDay-5 and intTag<$actDay and (intVerein1=$team1 OR intVerein2=$team1) and intStatus=2";
																		$res = mysqli_query($db, $SQL);
																		if ($res === false) {
																			echo mysqli_error($db); // oder: error_log(mysqli_error($db));
																		}
                                                                        $cnt = mysqli_num_rows($res);
                                                                ?>
                                                                <td colspan="3" align="right">
                                                                        <table>
                                                                                <tr>
                                                                                        <?php
                                                                                                for($i = 0; $i < $cnt; $i++)
                                                                                                {
                                                                                                        $stat = mysql_result($res, $i, "Stat");
                                                                                                        $tag = mysql_result($res, $i, "intTag");
                                                                                                        $ort = mysql_result($res, $i, "ort");
                                                                                                        $goal1 =mysql_result($res, $i, "intGoal1");
                                                                                                        $goal2 =mysql_result($res, $i, "intGoal2");
                                                                                                        $height="#aa0000";
                                                                                                        switch($stat)
                                                                                                        {
                                                                                                                case 1:
                                                                                                                        $height="#aaaaaa";
                                                                                                                        break;
                                                                                                                case 2:
                                                                                                                        $height="#00aa00";
                                                                                                                        break;
                                                                                                        }
                                                                                                ?>
                                                                                        <td valign="bottom" width="20" align="center" bgcolor="<?php echo $height?>" style="color:#ffffff">
                                                                                                <strong>
                                                                                                        <?php echo $ort?><br>
                                                                                                <?php echo $goal1 ?>:<?php echo $goal2 ?>
                                                                                                <!--<br>
                                                                                                <div style="background-color:#000000;height:<?php echo $height?>px;width:100%">
                                                                                                        &nbsp;
                                                                                                </div>
                                                                                                <br>-->
                                                                                                <?php echo $tag?>
                                                                                                </strong>
                                                                                        </td>
                                                                                        
                                                                                        <?php
                                                                                        }?>
                                                                                </tr>
                                                                        </table>
                                                                </td>
                                                        </tr>
                                                </table>        
                                        </td>
                                        <td valign="top"><br>
                                                <table border="0" cellpadding="0" cellspacing="1" style="border-left:1px solid red">
                                                        <tr>
                                                                <td colspan="2" align="center"><strong><?php echo $ver2?></strong></td>
                                                        </tr>
                                                        <tr>
                                                                <td class="stathead" width="90"align="center">Heimspiele</td>
                                                                <td  class="stathead" width="90" align="center">Auswärtsspiele</td>
                                                        </tr>
                                                        <tr>
                                                                <td align="center" class="statdata">
                                                                        <?php echo mysqli_num_rows(mysqli_query($db, "SELECT lngIndex from tblspieltag WHERE intVerein1=$team2 AND intGoal1>intGoal2 and intTag<>$actDay and intStatus=2"))?>
                                                                </td>
                                                                <td align="center" class="statdata">
                                                                        <?php
                                                                                $tmp = mysqli_num_rows(mysqli_query($db, "SELECT lngIndex from tblspieltag WHERE intVerein2=$team2 AND intGoal1<intGoal2 and intTag<>$actDay and intStatus=2"));
                                                                                $gast += $tmp*3;
                                                                                echo $tmp;
                                                                        ?>
                                                                </td>
                                                        </tr>
                                                        <tr>
                                                                <td align="center" class="statdata">
                                                                        <?php echo mysqli_num_rows(mysqli_query($db, "SELECT lngIndex from tblspieltag WHERE intVerein1=$team2 AND intGoal1=intGoal2 and intTag<>$actDay and intStatus=2"))?>
                                                                </td>
                                                                <td align="center" class="statdata">
                                                                        <?php
                                                                                $tmp = 0;
                                                                                $tmp = mysqli_num_rows(mysqli_query($db, "SELECT lngIndex from tblspieltag WHERE intVerein2=$team2 AND intGoal1=intGoal2 and intTag<>$actDay and intStatus=2"));
                                                                                $gast += $tmp;
                                                                                echo $tmp;
                                                                        ?>
                                                                </td>
                                                        </tr>
                                                        <tr>
                                                                <td align="center" class="statdata">
                                                                        <?php echo mysqli_num_rows(mysqli_query($db, "SELECT lngIndex from tblspieltag WHERE intVerein1=$team2 AND intGoal1<intGoal2 and intTag<>$actDay and intStatus=2"))?>
                                                                </td>
                                                                <td align="center" class="statdata">
                                                                        <?php echo mysqli_num_rows(mysqli_query($db, "SELECT lngIndex from tblspieltag WHERE intVerein2=$team2 AND intGoal1>intGoal2 and intTag<>$actDay and intStatus=2"))?>
                                                                </td>
                                                        </tr>
                                                        <tr>
                                                                <?php
                                                                        $SQL="SELECT *,IF(intVerein1=$team2, 'H', 'A' ) as ort, IF(intVerein1=$team2, IF(intGoal1>intGoal2,2,IF(intGoal1=intGoal2,1,0)),IF(intGoal1<intGoal2,2,IF(intGoal1=intGoal2,1,0))) AS Stat FROM tblspieltag WHERE intTag>=$actDay-5 and intTag<$actDay and (intVerein1=$team2 OR intVerein2=$team2) and intStatus=2";
                                                                        $res = mysqli_query($db, $SQL);
                                                                        $cnt = mysqli_num_rows($res);
                                                                ?>
                                                                <td colspan="2" align="right">
                                                                        <table border="0">
                                                                                <tr>
                                                                                        <?php
                                                                                                for($i = 0; $i < $cnt; $i++)
                                                                                                {
                                                                                                        $stat = mysql_result($res, $i, "Stat");
                                                                                                        $tag = mysql_result($res, $i, "intTag");
                                                                                                        $ort = mysql_result($res, $i, "ort");
                                                                                                        $goal1 =mysql_result($res, $i, "intGoal1");
                                                                                                        $goal2 =mysql_result($res, $i, "intGoal2");
                                                                                                        $height="#aa0000";
                                                                                                        switch($stat)
                                                                                                        {
                                                                                                                case 1:
                                                                                                                        $height="#aaaaaa";
                                                                                                                        break;
                                                                                                                case 2:
                                                                                                                        $height="#00aa00";
                                                                                                                        break;
                                                                                                        }
                                                                                                ?>
                                                                                        <td valign="bottom" width="20" align="center" bgcolor="<?php echo $height?>" style="color:#ffffff">
                                                                                                <strong>
                                                                                                        <?php echo $ort?><br>
                                                                                                <?php echo $goal1?>:<?php echo $goal2?>
                                                                                                <!--<br>
                                                                                                <div style="background-color:#000000;height:<?php echo $height?>px;width:100%">
                                                                                                        &nbsp;
                                                                                                </div>
                                                                                                <br>-->
                                                                                                <?php echo $tag?>
                                                                                                </strong>
                                                                                        </td>
                                                                                        
                                                                                        <?php
                                                                                        }?>
                                                                                </tr>
                                                                        </table>
                                                                </td>
                                                        </tr>
                                                </table>
                                        </td>
                                </tr>
                                <tr>
                                        <td colspan="2">
                                                <table border="0" cellpadding="0" cellspacing="2" align="center" width="90%">
                                                        <tr>
                                                                <td align="center" class="statdata">
                                                                <strong>
                                                                <?php
                                                                        $tmp = 0;
                                                                        if($cnt != 0)
                                                                                $tmp = ($heim/$cnt) - ($gast/$cnt);
                        //                                              echo "$heim;$cnt;$gast;$tmp";
                                                                        if($tmp >= -0.5 && $tmp <= 0.5)
                                                                                echo "Tendenz: Unentschieden";
                                                                        else if($tmp > 0.5)
                                                                                echo "Tendenz: Sieg für $ver1";
                                                                        else
                                                                                echo "Tendenz: Sieg für $ver2";
                                                                ?>
                                                                </strong>
                                                                </td>
                                                        </tr>
                                                        <tr>
                                                                <td align="right" height="30"><img src="pic/close.gif" width="120" height="20" alt="" border="0" style="cursor:pointer" onclick="window.close()"></td>
                                                        </tr>
                                                </table>
                                        </td>
                                </tr>
                        </table>
                        <?php
                        }
                        
                if($action == "login")
                {
                        $user = $_POST['user'];
                        $xx = $_POST['xx'];
						$xx = MD5($user . "*" . $xx);
                        $result=mysqli_query($db, "SELECT * FROM tblgamer WHERE strAlias='$user' and ((strPasswort='$xx') or (strPasswort is null))");
                        $anzahl=mysqli_num_rows($result);
                        if($anzahl > 0)
                        {
                                $session = time();
                                $session = md5($user ."*". $session);
                                $user = mysql_result($result, 0, "lngIndex");
                                mysqli_query($db,"insert into tblsession(strSessionid, intUser) values('$session', $user)");
                                $user = $session;
                                $menuuser = $session;
                                $action = "help";
                        }
                        else
                                $action=NULL;
                }
                
                if($action == "help")
                {
                        if(!isset($user))
                        {
                                $user = $_GET['user'];
                                if(!isset($user))
                                        $user = $_POST['user'];
                        }
                        include("menu.php");
                        
                        $maxpoint = ($pointtendenz + $pointhomegoal + $pointguestgoal + $pointdif) * $gamecount;
                        ?>
                <table border="0" cellpadding="4" cellspacing="8" width="600" class="ttable">
                        <tr>
                                <td>
                                        <strong>Wilkommen im Tippspiel</strong><br>
                                        Es ist ein einfaches Spiel, Sie tippen auf alle Bundesligapartien, wie sie enden könnten und Sie bekommen dafür Punkte.
                                        <ul>
                                                <li>1. richtige Tendenz = <?php echo $pointtendenz?> Punkt</li>
                                                <li>2. richtige Anzahl Tore f&uuml;r Heim-Mannschaft = <?php echo $pointhomegoal?> Punkt<br>Bedingung: Punkt 1</li>
                                                <li>3. richtige Anzahl Tore f&uuml;r Gast-Mannschaft = <?php echo $pointguestgoal?> Punkt<br>Bedingung: Punkt 1</li>
                                                <li>4. richtige Tordifferenz = <?php echo $pointdif?> Punkt<br>Bedingung: Punkt 1</li>
                                        </ul>
                                        Somit können Sie max. <?php echo $maxpoint?> Punkte pro Spieltag erhalten. Sie können bis Samstag Vormittag ihre Tipps abgeben. Um Punkte zu erhalten, müssen Sie auch auf alle Partien einen Tipp abgeben.
                                
                                </td>
                                <td valign="top" rowspan="10"><img src="pic/senkstr.gif" width="2" height="720" alt="" border="0"></td>
                                <td valign="top" rowspan="10" width="150">
                                   <table width="150" cellpadding="0" cellspacing="0" align="right">
                                           <tr><td><img src="pic/shim.gif" width="150" height="10" alt="" border="0"></td></tr>
                                           <tr>
                                                   <td class="topten"><?php include("topten.php")?></td>
                                           </tr>
                                   </table>
                                </td>
                        </TR>
                        <tr><td><hr size="2" color="#496241"></td></tr>
                        <TR>
                                <TD valign="top">
                                        <strong>Hier eine kleine Einführung der Menüstruktur:</strong><br>
                                        <ul>
                                        <li>Über den Punkt "Tippen" kommen Sie in die Tippmaske, wo Sie dann den aktuellen Spieltag tippen konnen. </li>
                                        <li>Hinter dem Menüpunkt "Tabellen" verbirgt sich Die Bundesligatabelle und die Heim- bzw. Auswärtstabellen. Zudem finden Sie dort eine kleinen Statistik.</li>
                                        <li>Unter "Ranking" können Sie sehen auf welcher Position Sie im gesamten Wettbewerb stehen.</li>
                                        <li>
                                                Wenn Sie auf Spieltage klicken, öffnet sich ein Dropdownmenü, wo dann alle eingetragenen Spieltage
                                                aufgelistet sind. Klicken Sie auf einen der Spieltage, und Sie können sehen wie die Spiele ausgegangen sind, 
                                                wieviel Punkte Sie an diesem Spieltag erspielt haben, und wieviele Punkte die anderen Mitspieler erzielt haben.
                                                Liegt der Spieltag noch in der Zukunft, haben Sie hier die Möglichkeit, Ihre Tipps im vorraus abzugeben.
                                                Dies ist hilfreich, wenn Sie einmal für mehrere Spieltage im sonnigen Süden sind. 
                                        </li>
                                        <li>
                                                Klicken Sie auf "Mein Account", um z.B. Ihre Mailadresse bzw. auch das Passwort zu ändern. Zudem haben Sie hier die Möglichkeit, an andere Mitspieler eine kleine Nachricht zu senden.
                                        </li>
                                        </ul>
                                </TD>
                        </TR>
                </table>
                <?php
                }
                if($action == "register")
                {
                        ?>
                        <script language="JavaScript1.2">
                                function checkForm()
                                {
                                        if(document.login.user.value != "")
                                        {
                                        /*
                                                if(document.login.xx.value != "")
                                                {
                                                        if(document.login.mail.value != "")
                                                        {
                                                                mail = document.login.mail.value.split("@");
                                                                if(mail.length >= 2)
                                                                {
                                   */
                                                                        codiere();
                                                                        return;
                                        /*
                                                                }
                                                        }
                                                }
                                        */
                                        }
                                                        
                                        alert("Bitte füllen Sie das Formular komplett aus!");
                                }
                        </script>
<table border="0" cellpadding="0" cellspacing="0" width="600">
        <tr><td bgcolor="#ffffff"><img src="pic/shim.gif" width="1" height="1" alt="" border="0"></td></tr>
        <tr>
                <td><img src="pic/fussball.jpg" width="600" height="100" alt="" border="0"></td>
        </tr>
        <tr><td bgcolor="#ffffff"><img src="pic/shim.gif" width="1" height="1" alt="" border="0"></td></tr>
</table>

<!-- 
<?php include("disclaimer.htm")?>
-->

<table border="0" cellpadding="0" cellspacing="0" width="600" class="ttable">
        <tr>
                <td><img src="pic/shim.gif" width="45" height="120" alt="" border="0"></td>
                <td width="555">
                        <form action="login.php" autocomplete="off" method="post" name="login">
                        <input type=hidden name="action" value="save">
                        <table border="0" cellpadding="2" cellspacing="0">
                                <tr>
                                        <td class="head" colspan="2"><strong>&nbsp;Anmelden</strong></td>
                                </tr>
                                <tr>
                                        <td width="150">Alias (loginname)</td>
                                        <td width="250"><input type="text" name="user" style="width:250px"></td>
                                </tr>
                                <tr>
                                        <td>E-Mail</td>
                                        <td><input type="text" name="mail" style="width:250px"></td>
                                </tr>
                                <tr>
                                        <td>Passwort</td>
                                        <td><input type="password" name="xx" style="width:250px"></td>
                                </tr>
                                <tr>
                                        <td colspan='2' align="right">
                                                <img src="pic/abbrechen.gif" width="120" height="20" alt="" border="0" onclick="document.location.href='login.php'" style="cursor:pointer">&nbsp;
                                                <img src="pic/speichern.gif" width="120" height="20" alt="" border="0" onclick="checkForm()" style="cursor:pointer">
                                        </td>
                                </tr>
                                <tr>
                                        <td colspan='2' align="left">
                                        <br/>
                                        Um dieses Spiel nicht unnötig kompliziert zu machen, ist lediglich die Angabe des Loginnamens notwendig (Groß/Kleinschreibung beachten). 
                                        Wer sichergehen will das niemand seine Tippdaten einsieht oder verändert, kann natürlich auch ein 
                                        Passwort eingeben.
                                        <br> 
                                        Das E-Mail Angabe ist auch nicht zwingend notwendig, allerdings kann man dann auch nicht von anderen Mitspielern 
                                        Mitteilungen erhalten. Diese Möglichkeit ist eigentlich ein ganz nettes Feature des Spieles.
                                        <br>
                                        Alle Angaben können auch noch später, unter dem Menuepunt 'Mein Account'. eingegeben und geändert werden.
                                        <br><br>
                                        na denn viel Spass Roland!                                         
                                        </td>
                                </tr>
                        </table>
                        </form>
                </td>
        </tr>
</table>
                        <?php
                }
                
                if($action == "save")
                {
                        $mail = $_POST['mail'];
                        $user = $_POST['user'];
                        $xx = $_POST['xx'];
                        
                        $SQL = "";
                        if($mail != "")
                                $SQL = "SELECT * FROM tblgamer WHERE strEmail='$mail' OR strAlias='$user'";
                        else
                                $SQL = "SELECT * FROM tblgamer WHERE strAlias='$user'";
                                
                        $result=mysqli_query($db, $SQL);
                        $anzahl=mysqli_num_rows($result);
                        
                        /*
                        if($mail == "" || $user == "" || $xx == "")
                        */
                        if($user == "")
                        {
                                $anzahl = 1;
                        }
                        if($anzahl == 0)
                        {
                                mysqli_query($db, "INSERT INTO tblgamer(strAlias, strEmail, strPasswort) values('$user', '$mail', '$xx')");
                        }
                        else
                        {
                                $error = "Registrierung hat leider nicht funktioniert<br>".
                                "Es existiert bereits ein Usermit d em Usernamen,".
                                "bzw. es existiert bereits ein User mit der angegebenen E-Mail Adresse!->";
                        }
                        $action = null;
                }
                
                if($action == "wetten")
                {
                        $tippid = $_POST['tippid'];
                        $user = $_POST['user'];
                        $session = $user;
                        $res = mysqli_query($db, "select * from tblsession where strSessionid='$session'");
                        $user_id = "";
                        $error = 0;
                        $msg = "Einige ihrer Tipps wurden nicht gespeichert!";
                        if(mysqli_num_rows($res)>0)
                        {
                                $user_id = mysql_result($res, 0, "intUser");
                        }
                        if($user_id != "")
                        {
                                $SQL = CONST_PER_MATCH_DEADLINE
                                        ? "SELECT * FROM tblspieltag WHERE intStatus=0 AND intTag=$actDay AND dtmStart > NOW()"
                                        : "SELECT * FROM tblspieltag WHERE intStatus=0";
                                $result = mysqli_query($db, $SQL);
                                for($i = 0; $i < mysqli_num_rows($result); $i++)
                                {
                                        $gameid = mysql_result($result, $i, "lngIndex");
                                        $user_goal1 = $_POST["goal1_$gameid"];
                                        $user_goal2 = $_POST["goal2_$gameid"];
                                        if($user_goal1 != "" && $user_goal2 != "")
                                        {
                                                $SQL = "SELECT * FROM tblwette WHERE intSpielid=$gameid and intUserid=$user_id";
                                                $ysnupdate = mysqli_query($db, $SQL);
                                                if(mysqli_num_rows($ysnupdate) == 0)
                                                {
                                                        $SQL = "insert into tblwette(intUserid, intTag, intSpielid, intGoal1, intGoal2) values($user_id, $actDay, $gameid, $user_goal1,$user_goal2)";
                                                }
                                                else
                                                        $SQL = "UPDATE tblwette SET intGoal1=$user_goal1, intGoal2=$user_goal2 WHERE intSpielid=$gameid and intUserid=$user_id";
                                                mysqli_query($db, $SQL);
                                                if(mysqli_error($db) != "")
                                                        $error++;
                                        }
                                }
                        }
                        if($error == 0)
                        {
                                $msg = "Ihre Tipps wurden erfolgreich gespeichert!";
                        }

                        if($action != null)
                        {
                                if($_POST['day'] != "")
                                {
                                        $actDay = $_POST['day'];
                                        include("daystat.php");
                                }
                                else
                                        $action="list";
                        }
                }
                
                if($action == "saveuser")
                {
                        if($user == "")
                                $user = $_GET['user'];
                        if($user == "")
                                $user = $_POST['user'];
                        $ymail = $_POST['xmail'];
                        $yrem = $_POST['xremember'];
                        
                        $oldpwd = $_POST['oldpwd'];
                        $newpwd = $_POST['newpwd'];
                        
                        $helpx = mysqli_query($db, "select * from tblsession where strSessionid='$user'");
                        $helpuser= mysql_result($helpx, 0, "intUser");
                        mysqli_query($db, "UPDATE tblgamer SET strEmail=\"$ymail\", boolRemember=$yrem where lngIndex=$helpuser");
                        
                        /*
                        if($oldpwd != "")
                        {
                        */
                                $SQL = "SELECT * FROM tblgamer WHERE lngIndex=$helpuser AND ((strPasswort='$oldpwd') OR (strPasswort is null))";
                                if(mysqli_num_rows(mysqli_query($db, $SQL)) != 0)
                                {
                                        $SQL = "UPDATE tblgamer SET strPasswort='$newpwd' WHERE lngIndex=$helpuser";
                                        mysqli_query($db, $SQL);
                                        $message = "Das Passwort wurde erfolgreich geändert!";
                                }
                                else
                                        $message = "Dass Passwort wurde NICHT erfolgreich geändert!<br>Überprüfen Sie Ihr altes Passwort!"; 
                        /*
                   }
                   */
                
                        //$user="";
                        //$action = "list";
                        include("useroffice.php");
                }
                
                if($action=="bltable")
                {
                        include("menu.php");
                        include("bltable.php");
                }
                
                if($action == "list")
                {
                        if (CONST_LOCAL_PERSISTENT_LOGIN && isset($_SESSION['bl_wette_flash_message'])) {
                                $msg = $_SESSION['bl_wette_flash_message'];
                                unset($_SESSION['bl_wette_flash_message']);
                        }
                        include("menu.php");
                        $matchOrder = CONST_PER_MATCH_DEADLINE ? " ORDER BY dtmStart DESC, lngIndex DESC" : "";
                        $result = mysqli_query($db, "select * from tblspieltag where intTag=$actDay$matchOrder");
                        $anzahl = mysqli_num_rows($result)-1;
                        $matchStartCounts = array();
                        if (CONST_PER_MATCH_DEADLINE)
                        {
                                $groupResult = mysqli_query($db, "SELECT dtmStart, COUNT(*) AS gameCount FROM tblspieltag WHERE intTag=$actDay GROUP BY dtmStart");
                                while ($group = mysqli_fetch_assoc($groupResult))
                                {
                                        $matchStartCounts[$group['dtmStart']] = (int)$group['gameCount'];
                                }
                        }
                        if($user == "")
                                $user = $_GET['user'];
                        if($user == "")
                                $user = $_POST['user'];
                                
//SBE $msg kam rein und $closetime
                        ?>
<table border="0" cellpadding="4" cellspacing="8" width="600" class="ttable">
        <tr>
                <td width="200"><strong>Aktueller Spieltag: <?php echo $actDay?></strong></td>
                <td><strong style="color:red"><?php echo $msg?></strong>&nbsp;</td>
        </tr>
        <tr>
                <td colspan="3">
                        <table border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                        <td>Klicken Sie auf &nbsp;</td>
                                        <td><img src="./pic/info.gif" border="no"> &nbsp;</td>
                                        <td>,um genauere Informationen zu dieser Paarung zu bekommen.</td>
                                </tr>
                                <tr>
                                        <td>Klicken Sie auf &nbsp;</td>
                                        <td><img src="./pic/wetten.gif" border="no" alt="Tippen"> &nbsp;</td>
                                        <td>, um Ihr Tipp für diese Paarung zu speichern.</td>
                                </tr>
                        </table>
                </td>
        </tr>
        <tr>
                <td colspan="2" valign="top">
                        <form action="login.php" method="post" name="xform">
                        <input type=hidden name="tippid" value="<?php echo $tippid?>">
                        <input type=hidden name="user" value="<?php echo $user?>">
                        <input type=hidden name="action" value="wetten">
                        <table cellpadding="2" cellspacing="0" border="0" width="360">
                                <tr class="head">
                                        <td class="head" align="center"><strong><?php echo $closetime?></strong></td>
                                </tr>
                        </table>
                        <table border="0" cellspacing="0" cellpadding="0" class="tablestyle" width="360">
                        <?php
                        $lastMatchStart = null;
                        $weekdays = array('Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag');
                        while($anzahl > -1)
                        {
                                $id = mysql_result($result, $anzahl, "lngIndex");
                                $verx1 = mysql_result($result, $anzahl, "intVerein1");
                                $verx2 = mysql_result($result, $anzahl, "intVerein2");
                                $status = mysql_result($result, $anzahl, "intStatus");
                                $matchStart = mysql_result($result, $anzahl, "dtmStart");

                                if (CONST_PER_MATCH_DEADLINE && $matchStart != $lastMatchStart)
                                {
                                        $matchDate = date_create_from_format('Y-m-d H:i:s', $matchStart);
                                        $matchDateLabel = $weekdays[(int)date_format($matchDate, 'w')] . date_format($matchDate, ', d.m.Y \\u\\m H:i \\U\\h\\r');
                                        $matchGroupText = $matchStartCounts[$matchStart] === 1 ? 'Spiel am ' : 'Alle Spiele am ';
                                        ?>
                                        <tr>
                                                <td colspan="7" style="background-color:#ffffff; padding:3px 4px;"><div style="background:linear-gradient(#ffffff, #f1f3f0); color:#202820; font-size:12px; font-weight:bold; letter-spacing:0.15px; line-height:18px; padding:3px 8px; border:1px solid #aeb8aa; border-radius:2px; box-shadow:inset 0 1px 0 #ffffff, 0 2px 3px rgba(58,72,55,0.25); text-align:left;"><?php echo $matchGroupText . $matchDateLabel?></div></td>
                                        </tr>
                                        <?php
                                        $lastMatchStart = $matchStart;
                                }
                                
                                $scnd = mysqli_query($db, "select * from tblverein where lngIndex=$verx1");
                                $ver1 = mysql_result($scnd, 0, "strName");
                                
                                $scnd = mysqli_query($db, "select * from tblverein where lngIndex=$verx2");
                                $ver2 = mysql_result($scnd, 0, "strName");
                                
                                $session = $user;
                                $helpx = mysqli_query($db, "select * from tblsession where strSessionid='$session'");
                                if(mysqli_num_rows($helpx) > 0)
                                {
                                        $user = mysql_result($helpx, 0, "intUser");
                                        $scnd = mysqli_query($db, "select * from tblwette where intSpielid=$id and intUserid=$user");
                                        $user = $session;
                                        if(mysqli_num_rows($scnd)==1)
                                        {
                                                $goal1 = mysql_result($scnd, 0, "intGoal1");
                                                $goal2 = mysql_result($scnd, 0, "intGoal2");
                                                $tippid = mysql_result($scnd, 0, "lngIndex");
                                        }
                                        else
                                        {
                                                $goal1 = "";
                                                $goal2 = "";
                                                $tippid = "";
                                        }
                                                
                                        {
                                                if($status != 0 || (CONST_PER_MATCH_DEADLINE && $matchStart <= date('Y-m-d H:i:s')))
                                                        $stat = "disabled";
                                                else
                                                        $stat = "";
                                                
                                                $classname = "secondline";
                                                if($anzahl % 2)
                                                        $classname = "firstline";?>
                                                <tr class="<?php echo $classname?>" style="height:23px;" onclick="setHigh(<?php echo $verx1?>,<?php echo $verx2?>)">
                                                        <td align="right" width="130" class="<?php echo $classname?>" style="white-space:nowrap; font-size:11px; vertical-align:middle;"><?php echo $ver1?>&nbsp;</td>
                                                        <td width="25" align="center" class="<?php echo $classname?>">
                                                                <?php 
                                                                if($stat == "")
                                                                {
                                                                ?>
                                                                <input type="text" name="goal1_<?php echo $id?>" style="width:25px" size="2" value="<?php echo $goal1?>" <?php echo $stat?>>
                                                                <?php
                                                                }else{?>
                                                                        <?php echo $goal1?>
                                                                <?php 
                                                                }?>
                                                        </td>
                                                        <td width="5" align="center" class="<?php echo $classname?>"><strong>:</strong></td>
                                                        <td width="25" align="center" class="<?php echo $classname?>">
                                                                <?php 
                                                                if($stat == "")
                                                                {
                                                                ?>
                                                                <input type="text" name="goal2_<?php echo $id?>" style="width:25px" size="2" value="<?php echo $goal2?>" <?php echo $stat?>>
                                                                <?php
                                                                }else{ 
                                                                ?>
                                                                        <?php echo $goal2?>
                                                                <?php
                                                                } ?>
                                                        </td><td align="left" width="130" class="<?php echo $classname?>" style="white-space:nowrap; font-size:11px; vertical-align:middle;">&nbsp;<?php echo $ver2?></td>
                                                        <td align="center" width="20" class="<?php echo $classname?>"><a href="javascript:openStat(<?php echo $verx1?>, <?php echo $verx2?>)" tabindex="-1"><img src="pic/info.gif" width="16" height="16" alt="INFORMATIONEN" border="0"></a></td>
                                                        <td align="center" width="20" class="<?php echo $classname?>">&nbsp;</td>
                                                </tr>
                                                <?php
                                        }
                                }
                                $anzahl--;
                        }
                        $last = $actDay - 1;
                        ?>
                        </table>
                        <table cellpadding="0" cellspacing="0" border="0" width="360" height="60">
                                <tr>
                                        <td align="right" height="60"><img src="pic/tippdrucken.gif" width="120" height="20" alt="" border="0" onclick="window.print()" style="cursor:pointer">&nbsp;<img src="./pic/tippen.gif" border="0" onclick="document.xform.submit()" style="cursor:pointer"></td>
                                </tr>
                                <tr>
                                        <td align="right" height="30"><img src="pic/alletipps.gif" width="120" height="20" alt="" border="0" <?php if ($statusSpielTag == 1) { ?> onclick="openall( <?php echo $actDay ?> )" <?php } ?>  style="cursor:pointer"></td>
                                </tr>
                        </table>
                        </form>
                </td>
                <!--- SBE<td width="10"><img src="../../../pic/shim.gif" style="border:0px solid gray" width="10" height="100%" alt="" border="0"></td>--->
                <td width="150" valign="top" rowspan="3">
                                <table cellpadding="2" cellspacing="0" border="0" class="minitable" width="200">
                                        <tr>
                                                <td class="head">&nbsp;</td>
                                                <td width="100" class="head"><b>Verein</b></td>
                                                <td width="25" class="head"><b>Punkte</b></td>
                                                <td width="25" class="head"><strong>Dif</strong></td>
                                        </tr>
                                        <?php
                                                $result = mysqli_query($db,"SELECT *, (cast(intGoal AS SIGNED) - cast(intGGoal AS SIGNED)) AS Dif FROM tblverein ORDER BY intPoint DESC,Dif DESC, intGoal DESC ");
                                                $anzahl = mysqli_num_rows($result)-1;
                                                for($i=0;$i<=$anzahl;$i++)
                                                {
                                                        $diff = mysql_result($result, $i, "intGoal") - mysql_result($result, $i, "intGGoal");
                                                        $classname = "secondline";
                                                        if($i % 2)
                                                                $classname = "firstline";
                                                        ?>
                                                <tr id="<?php echo mysql_result($result, $i, "lngIndex");?>" class="<?php echo $classname?>">
                                                        <td align="right" class="<?php echo $classname?>"><?php echo $i+1?>.</td>
                                                        <td class="<?php echo $classname?>">
                                                        <a href="http://<?php echo mysql_result($result, $i, "strURL") ?>#" target="_blank"><?php echo mysql_result($result, $i, "strName")?></a></td>
                                                        <td align="center" class="<?php echo $classname?>"><?php echo mysql_result($result, $i, "intPoint")?></td>
                                                        <td width="25" class="<?php echo $classname?>"><?php echo $diff?></td>
                                                </tr>
                                        <?php
                                        }?>
                                </table>
                        </td>
                </tr>
        </table>
        <?php
                }
                
                if($action == "table")
                {
                        include("usertable.php");
                }
        }       
        if($action == null)
        {
                ?>              
                <table border="0" cellpadding="0" cellspacing="0" width="600">
                        <tr>
                                <td><img src="pic/fussball.jpg" width="600" height="100" alt="" border="0"></td>
                        </tr>
                        <tr><td bgcolor="#ffffff"><img src="pic/shim.gif" width="1" height="1" alt="" border="0"></td></tr>
                </table>
                <table border="0" cellpadding="4" cellspacing="8" width="600" class="ttable">
                        <tr>
                                <td>
                                        <strong>Kompetent in Fußballfragen?</strong><br>
                                        Dann sind sie hier richtig, denn dies ist ein spaßiges und einfach zu bedienendes Wettspiel!
                                </td>
                                <td rowspan="10" valign="top"><img src="pic/senkstr.gif" width="2" height="400" alt="" border="0"></td>
                                <td rowspan="3" valign="top">
                                        <table border="0" cellpadding="0" cellspacing="0" width="150">
                                                <tr>
                                                        <td><strong>Login</strong></td>
                                                </tr>
                                                <form action='login.php' id="login" name="login" autocomplete="off" method="post">
                                                <input type="hidden" name="action" value="login" form="login">
                                                <tr><td>Alias:</td></tr>
                                                <tr><td width="150"><input type="text" name="user" style="width:150" form="login"></td></tr>
                                                <tr><td>Passwort:</td></tr>
                                                <tr><td width="150"><input type="password" name="xx" style="width:150" form="login"></td></tr>
                                                <tr><td align="right"><input type="image" src="pic/login.gif" form="login">&nbsp;<tr><td>
                                                <tr><td><img src="pic/shim.gif" width="1" height="10" alt="" border="0"></td></tr>
												<!--
                                                <tr><td height="15"><img src="pic/registrieren.gif" onMouseover="this.style.cursor='pointer'" onclick="javascript:document.location.href='login.php?action=register'" border="0">
												-->
                                                <tr><td>
                                                <tr><td height="15"><img src="pic/passwort.gif" onMouseover="this.style.cursor='pointer'" onclick="forgotpwd()" border="0">
                                                </td>
                                                </tr>
                                                <tr>
                                                        <td><br>
                                                                Wenn Sie hier für Testzwecke reinschauen wollen, 
                                                                dann nutzen Sie bitte den Login "testuser" 
                                                                mit dem Passwort "testuser". Danke
                                                        </td>
                                                </tr>   
                                                </form>
                                        </table>
                                </td>
                        </tr>
                <?php 
                if($error != "")
                {
                ?>
                        <tr>
                                <td class="error">
                                        <strong><?php echo $error?></strong>
                                </td>
                        </tr>
                <?php
                }
                ?>
                        <tr>
                                <td height="20" class="title"><strong>&nbsp;Anleitung</strong></td>
                        </tr>
                        
                        <tr>
                                <td valign="top">
                                        Sie tippen auf Bundesligapartien und bekommen daf&uuml;r Punkte:
                                        <?php 
                                        $maxpoint = $pointtendenz + $pointhomegoal + $pointguestgoal + $pointdif;
                                        ?>
                                        <ul>
                                                <li>1. richtige Tendenz = <?php echo $pointtendenz?> Punkt</li>
                                                <li>2. richtige Anzahl Tore f&uuml;r Heim-Mannschaft = <?php echo $pointhomegoal?> Punkt<br>Bedingung: Punkt 1</li>
                                                <li>3. richtige Anzahl Tore f&uuml;r Gast-Mannschaft = <?php echo $pointguestgoal?> Punkt<br>Bedingung: Punkt 1</li>
                                                <li>4. richtige Tordifferenz = <?php echo $pointdif?> Punkt<br>Bedingung: Punkt 1</li>
                                        </ul>
                                        
                                        Sie k&ouml;nnen max. <?php echo $maxpoint?> Punkte pro Spieltag erhalten.<br>
                                        Bis Samstag Vormittag müssen <strong>alle</strong> Partien getippt sein.
                                </td>
                        </tr>
                </table>
                <?php
        }
?>

<table border="0" cellpadding="0" cellspacing="0" width="600" class="footer2">
        <tr><td class="footer1" colspan="2"><img src="pic/shim.gif" width="1" height="10" alt="" border="0"></td></tr>
        <tr>
                <td height="30"><strong>&nbsp; &nbsp; &nbsp;Bundesliga Tippspiel </strong></td>
        </tr>
</table>
</body>
</html>
