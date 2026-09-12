<?php
	if(!isset($_POST))
		$_POST  = $HTTP_POST_VARS;
	if(!isset($_GET))
		$_GET  = $HTTP_GET_VARS;
	include_once ("../common/include.php");
?>
<table border="0" cellpadding="4" cellspacing="8" width="600" class="ttable">
	<tr>
		<td><strong>Gesamttabelle</strong></td>
	</tr>
	<tr>
		<td>
			<table border="0" cellpadding="2" cellspacing="0" class="minitable" width="280">
				<tr>
					<td class="head">&nbsp;</td>
					<td width="170" class="head"><b>Verein</b></td>
					<td width="30" class="head"><b>Tore</b></td>
					<td width="40" class="head"><b>Punkte</b></td>
				</tr>
				<?php
					$result = mysqli_query($db,"SELECT *, cast(`intGoal` AS SIGNED) - cast(`intGGoal` AS SIGNED) AS Dif FROM tblverein ORDER BY intPoint DESC, Dif DESC, lngIndex ASC ");
					$anzahl = mysqli_num_rows($result)-1;
					for($i=0;$i<=$anzahl;$i++)
					{
						$classname = "secondline";
						if($i % 2)
							$classname = "firstline";
				?>
				
					<tr id="<?php echo mysql_result($result, $i, "lngIndex")?>" class="<?php echo $classname?>">
						<td width="25" class="<?php echo $classname?>"><?php echo $i+1?>.</td>
						<td class="<?php echo $classname?>">
						<a href="http://<?php echo mysql_result($result, $i, "strURL")?>#" target="_blank"><?php echo mysql_result($result, $i, "strName")?></a></td>
						<td align="center" class="<?php echo $classname?>"><?php echo mysql_result($result, $i, "intGoal")?>:<?php echo  mysql_result($result, $i, "intGGoal")?></td>
						<td align="center" class="<?php echo $classname?>"><?php echo  mysql_result($result, $i, "intPoint")?></td>
					</tr>
				<?php
				}?>
			</table>
		</td>
		<td valign="top" align="left">
			<table border="0" cellpadding="2" cellspacing="0" class="minitable" width="280">
				<tr>
					<td colspan="2" class="head">
						<b>Kleine Statistik</b>
					</td>
				</tr>
				<tr>
					<td>Heimsiege:</td>
					<?php
					 $result = mysqli_query($db,"SELECT COUNT(lngIndex) AS cnt FROM tblspieltag WHERE intGoal1>intGoal2");?>
					<td><?php echo mysql_result($result, 0, "cnt")?></td>
				</tr>
				<tr>
					<td>Ausw&auml;rtssiege:</td>
					<?php
					$result = mysqli_query($db,"SELECT COUNT(lngIndex) AS cnt FROM tblspieltag WHERE intGoal1<intGoal2");?>
					<td><?php echo mysql_result($result, 0, "cnt")?></td>
				</tr>
				<tr>
					<td>Unentschieden:</td>
					<?php
					$result = mysqli_query($db,"SELECT COUNT(lngIndex) AS cnt FROM tblspieltag WHERE intGoal1=intGoal2 AND intStatus=2");?>
					<td><?php echo  mysql_result($result, 0, "cnt")?></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr><td colspan="2"><a href="http://www.script-fabrik.de/banner/banner.php?link=1&type=fussball" target="_blank"><img src="http://www.script-fabrik.de/banner/banner.php?link=0&type=fussball" border="0"></a></td></tr>


<?php
	$SQLHeim = "SELECT v.*,(cast(v.intGoal as signed) - cast(v.intGGoal as signed)) AS Dif, SUM(IF(intStatus=2,IF(intGoal1>intGoal2,3,IF(intGoal1=intGoal2,1,0)),0)) as Point, SUM(intGoal1-intGoal2) as dif, SUM(intGoal1) as tore, SUM(intGoal2) as gtore FROM tblverein as v
  LEFT JOIN tblspieltag as s ON s.intVerein1=v.lngIndex
GROUP BY v.lngindex
ORDER BY Point DESC, DIF DESC, intGoal DESC, v.lngIndex ASC";

$SQLGast = "SELECT v.*,(cast(v.intGoal as signed) - cast(v.intGGoal as signed)) AS Dif, SUM(IF(intStatus=2,IF(intGoal1<intGoal2,3,IF(intGoal1=intGoal2,1,0)),0)) as Point, SUM(intGoal2-intGoal1) as dif, SUM(intGoal2) as tore, SUM(intGoal1) as gtore FROM tblverein as v
  LEFT JOIN tblspieltag as s ON s.intVerein2=v.lngIndex
GROUP BY v.lngindex
ORDER BY Point DESC,Dif DESC, intGoal DESC, v.lngIndex ASC";
?>
	<tr>
		<td><strong>Heimtabelle</strong></td>
		<td><strong>Auswärtstabelle</strong></td>
	</tr>
	<tr>
		<td>
			<table border="0" cellpadding="2" cellspacing="0" class="minitable" width="280">
				<tr>
					<td class="head">&nbsp;</td>
					<td width="170" class="head"><b>Verein</b></td>
					<td width="30" class="head"><b>Tore</b></td>
					<td width="40" class="head"><b>Punkte</b></td>
				</tr>
				<?php
					$result = mysqli_query($db,$SQLHeim);
					$anzahl = mysqli_num_rows($result)-1;
					for($i=0;$i<=$anzahl;$i++)
					{
						$classname = "secondline";
						if($i % 2)
							$classname = "firstline";
				?>
				
					<tr id="<?php echo  mysql_result($result, $i, "lngIndex")?>" class="<?php echo $classname?>">
						<td align="right" class="<?php echo $classname?>"><?php echo  $i+1?>.</td>
						<td class="<?php echo $classname?>">
						<a href="http://<?php echo  mysql_result($result, $i, "strURL")?>#" target="_blank"><?php echo  mysql_result($result, $i, "strName")?></a></td>
						<td align="center" class="<?php echo $classname?>"><?php echo  mysql_result($result, $i, "tore")?>:<?php echo  mysql_result($result, $i, "gtore")?></td>
						<td align="center" class="<?php echo $classname?>"><?php echo  mysql_result($result, $i, "Point")?></td>
					</tr>
				<?php
				}?>
			</table>
		</td>
		<td>
			<table border="0" cellpadding="2" cellspacing="0" class="minitable" width="280">
				<tr>
					<td class="head">&nbsp;</td>
					<td width="170" class="head"><b>Verein</b></td>
					<td width="30" class="head"><b>Tore</b></td>
					<td width="40" class="head"><b>Punkte</b></td>
				</tr>
				<?php
					$result = mysqli_query($db,$SQLGast);
					$anzahl = mysqli_num_rows($result)-1;
					for($i=0;$i<=$anzahl;$i++)
					{
						$classname = "secondline";
						if($i % 2)
							$classname = "firstline";
				?>
				
					<tr id="<?php echo  mysql_result($result, $i, "lngIndex")?>" class="<?php echo $classname?>">
						<td align="right" class="<?php echo $classname?>"><?php echo  $i+1?>.</td>
						<td class="<?php echo $classname?>">
						<a href="http://<?php echo  mysql_result($result, $i, "strURL")?>#" target="_blank"><?php echo  mysql_result($result, $i, "strName")?></a></td>
						<td align="center" class="<?php echo $classname?>"><?php echo  mysql_result($result, $i, "tore")?>:<?php echo  mysql_result($result, $i, "gtore")?></td>
						<td align="center" class="<?php echo $classname?>"><?php echo  mysql_result($result, $i, "Point")?></td>
					</tr>
				<?php
				}?>
			</table>
		</td>
	</tr>
</table>