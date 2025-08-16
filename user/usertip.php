<?php
	if(!isset($_POST))
		$_POST  = $HTTP_POST_VARS;
	if(!isset($_GET))
		$_GET  = $HTTP_GET_VARS;
	include_once("../common/include.php");
	$db=mysqli_connect("$address", "$dbuser", "$dbpasswd",'bl_wette');
	$user = $_GET['user'];
	$day = $_GET['day'];
	
	/*$SQL = "SELECT w.lngIndex as tippid, s.lngIndex, s.intStatus, v1.strName as Team1, v2.strName as Team2, s.intGoal1 as rg1, s.intGoal2 as rg2, w.intGoal1 as myg1, w.intGoal2 as myg2,".
			"(IF(".
		       "(w.intGoal1 < w.intGoal2)<>(s.intGoal1 < s.intGoal2) OR (w.intGoal1=w.intGoal2)<>(s.intGoal1=s.intGoal2) OR (w.intGoal1>w.intGoal2)<>(s.intGoal1>s.intGoal2)".
		        ",0".
		        ",IF((w.intGoal1=s.intGoal1) AND (w.intGoal2=s.intGoal2),4,".
		        "IF((w.intGoal1=s.intGoal1) || (w.intGoal2=s.intGoal2) || ((w.intGoal1-w.intGoal2)=(s.intGoal1-s.intGoal2)),2,1)".
		       	 "))) as xPOINT ".
						 "from tblspieltag as s ".
						"LEFT JOIN tblwette as w ON(w.intSpielid = s.lngIndex)".
					 " LEFT JOIN tblgamer as g ON(w.intUserid = g.lngIndex)".
					      " LEFT JOIN tblverein as v1 ON (v1.lngIndex=s.intVerein1)".
					     " LEFT JOIN tblverein as v2 ON (v2.lngIndex=s.intVerein2)".
							 " WHERE  s.intTag=$day AND g.lngIndex=$user".
							 " GROUP BY s.lngIndex ORDER BY s.lngIndex DESC";*/

$top = $pointtendenz + $pointdif + $pointhomegoal + $pointguestgoal;
$home = $pointtendenz + $pointhomegoal;
$guest = $pointtendenz + $pointguestgoal;
$diff = $pointtendenz + $pointdif;
	$SQL = "SELECT w.lngIndex as tippid, s.lngIndex, s.intStatus, v1.strName as Team1, v2.strName as Team2, s.intGoal1 as rg1, s.intGoal2 as rg2, w.intGoal1 as myg1, w.intGoal2 as myg2,
					IF(
					      w.intGoal1 IS NOT NULL AND w.intGoal2 IS NOT NULL,
					      IF
					      (
							    (w.intGoal1<w.intGoal2)<>(s.intGoal1<s.intGoal2) OR (w.intGoal1=w.intGoal2)<>(s.intGoal1=s.intGoal2) OR (w.intGoal1>w.intGoal2)<>(s.intGoal1>s.intGoal2)
							        		,0
						        			,IF
					                (
					                    (w.intGoal1=s.intGoal1) AND (w.intGoal2=s.intGoal2),$top,
							         	    	 IF
					                      (
					                          (w.intGoal1=s.intGoal1),$home,
					                              IF
					                              (
					                                 (w.intGoal2=s.intGoal2),$guest,
					                                    IF(
					                                          ((w.intGoal1-w.intGoal2)=(s.intGoal1-s.intGoal2)),$diff,$pointtendenz
					                                      )
					                              )
					                      )
							       			 )
										     ),0)
											 as xPOINT
											 from  tblspieltag as s
											LEFT JOIN tblwette as w ON(w.intSpielid = s.lngIndex AND w.intUserid=$user)
												LEFT JOIN tblgamer as g ON(w.intUserid = g.lngIndex)
											      LEFT JOIN tblverein as v1 ON (v1.lngIndex=s.intVerein1)
												     LEFT JOIN tblverein as v2 ON (v2.lngIndex=s.intVerein2)
												 WHERE  s.intTag=$day
												 	 AND g.lngIndex=$user
												 GROUP BY s.lngIndex ORDER BY s.lngIndex DESC";
							 
	
	$res = mysqli_query($db,"SELECT * FROM tblgamer WHERE lngIndex=$user");
	$name = mysql_result($res, 0, "strAlias");
	//$result = mysqli_query($db, "select * from tblspieltag where intTag=$day");
	$result = mysqli_query($db, $SQL);
	$anzahl = mysqli_num_rows($result);
//	echo $SQL;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<link rel="stylesheet" type="text/css" href="../common/style.css">
	<title>Usertip</title>
</head>
<body leftmargin="2" rightmargin="2" topmargin="2" bottommargin="0" style="border:none" class="ttable">
<table width="100%" cellpadding="0" cellspacing="0" class="ttable">
	<tr>
		<td align="center" height="25" class="title">
			Die Tips von <strong><?php echo $name?></strong> vom <strong><?php echo $day?> Spieltag</strong>
		</td>
	</tr>
</table>
<table width="100%" border="0" cellpadding="2" cellspacing="0" class="tablestyle">
			<tr>
				<td colspan="3" class="head"><strong>Paarung</strong></td>
				<td colspan="3" class="head"><strong>Tipp</strong></td>
				<td colspan="3" class="head"><strong>Endergebnis</strong></td>
				<td class="head"><strong>Punkte</strong></td>
			</tr><?php
			//while($anzahl > -1)
			for($i = 0; $i < $anzahl; $i++)
			{
				$id  = mysql_result($result,$i, "lngIndex");
				$ver1 = mysql_result($result,$i, "Team1");
				$ver2 = mysql_result($result,$i, "Team2");
				$goal1 = mysql_result($result,$i, "myg1");
				$goal2 = mysql_result($result,$i, "myg2");
				
				$abs1 = mysql_result($result,$i, "rg1");
				$abs2 = mysql_result($result,$i, "rg2");
				
				$point = mysql_result($result,$i, "xPOINT");
				$style = "secondline";

				$status = mysql_result($result,$i, "intStatus");
				
				if($i%2)
					$style = "firstline";

				$classname = "secondline";
				if($i % 2)
					$classname = "firstline";

				if($status != 2)
				{
					$point="-";
					$abs1 = "-";
					$abs2 = "-";
					$classname = "failedline";
				}

				?><tr class="<?php echo $classname?>">
					<td align="center" class="<?php echo $classname?>"><?php echo $ver1?></td>
					<td align="center" class="<?php echo $classname?>">:</td>
					<td align="center" class="<?php echo $classname?>"><?php echo  $ver2?></td>
					<td align="right" class="<?php echo $classname?>"><?php echo  $goal1?></td><td align="center" class="<?php echo $classname?>">:</td><td align="left" class="<?php echo $classname?>"><?php echo  $goal2?></td>
					<td align="right" class="<?php echo $classname?>"><?php echo  $abs1?></td><td align="center" class="<?php echo $classname?>">:</td><td align="left" class="<?php echo $classname?>"><?php echo  $abs2?></td>
					<td align="center" class="<?php echo $classname?>"><strong><?php echo  $point?></strong></td>
				</tr>
				<?php
				$goal1 = 0;
				$goal2 = 0;
			}
			?>
	</table>
</body>
</html>
