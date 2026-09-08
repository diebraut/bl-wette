<?php
	if(!isset($_POST))
		$_POST  = $HTTP_POST_VARS;
	if(!isset($_GET))
		$_GET  = $HTTP_GET_VARS;
	
	$action = $_POST['action'] ?? '';
	include_once ("../common/include.php");
	$user = $_GET['user'] ?? ($_POST['user'] ?? ($menuuser ?? ''));
	
	$value = $_GET['value'] ?? '';
	$session = $user;
	$res = mysqli_query($db, "select * from tblsession where strSessionid='$session'");
	
        //Maximale user ausgeben, die getippt haben
        $getcount = "SELECT g.lngIndex FROM tblgamer as g
        	    LEFT JOIN tblwette as w ON(w.intUserid = g.lngIndex)
        	    LEFT JOIN tblspieltag as s ON (w.intSpielid=s.lngIndex)
        	WHERE  s.intTag=$actDay
        	and s.intStatus=2
        	GROUP BY g.lngIndex";
	
	$maxcnt = mysqli_num_rows(mysqli_query($db, $getcount));
	$pstart = $_POST['xpage'] ?? null;
	if(!isset($pstart))
		$pstart = 1;
		
	$end = $pstart * 9;
	$pstart = $end - 9;
	//$maxrow = intval($maxcnt/9) + 1;
	$maxrow = 1;

//hier ist noch ein fehler
$top = $pointtendenz + $pointdif + $pointhomegoal + $pointguestgoal;
$home = $pointtendenz + $pointhomegoal;
$guest = $pointtendenz + $pointguestgoal;
$diff = $pointtendenz + $pointdif;

                $SQLtab = "SELECT   (SELECT strAlias FROM tblgamer WHERE W.intUserId = lngIndex) AS USER, ".
                          "          W.intUserId AS USERID, ".
         	          "          SUM( ".
                          "            IF ".
                          "            ( ".
                          "               (W.`intGoal1` - W.`intGoal2` > 0  AND ". 
                          "                S.`intGoal1` - S.`intGoal2` > 0) ".   
                          "                  OR ".
                          "               (W.`intGoal1` - W.`intGoal2` < 0  AND ".
                          "                S.`intGoal1` - S.`intGoal2` < 0) ".  
                          "                  OR ".
                          "               (W.`intGoal1` - W.`intGoal2` = 0  AND ".
                          "                S.`intGoal1` - S.`intGoal2` = 0),1,0 ".
                          "            ) + ".
                          "            if ".
                          "            ( ".
                          "              ((W.`intGoal1` - W.`intGoal2` > 0   AND ".
                          "                S.`intGoal1` - S.`intGoal2` > 0 ) ".
                          "                  OR ".
                          "               (W.`intGoal1` - W.`intGoal2` < 0   AND ". 
                          "                S.`intGoal1` - S.`intGoal2` < 0) ". 
                          "                  OR ".
                          "               (W.`intGoal1` - W.`intGoal2` = 0   AND ".
                          "                S.`intGoal1` - S.`intGoal2` = 0)) AND ". 
                          "                W.`intGoal1` = S.`intGoal1` ,1,0 ".
                          "             ) + ".
                          "             if ".
                          "             ( ".
                          "                 ((W.`intGoal1` - W.`intGoal2` > 0   AND ". 
                          "                   S.`intGoal1` - S.`intGoal2` > 0) ".
                          "                     OR ".
                          "                  (W.`intGoal1` - W.`intGoal2` < 0   AND ". 
                          "                   S.`intGoal1` - S.`intGoal2` < 0) ". 
                          "                     OR ".
                          "                  (W.`intGoal1` - W.`intGoal2` = 0   AND  ".
                          "                   S.`intGoal1` - S.`intGoal2` = 0)) AND ". 
                          "                   W.`intGoal2` = S.`intGoal2` ,1,0 ".
                          "              ) + ".
                          "              if ".
                          "              ( ".              
                          "               ((W.`intGoal1` - W.`intGoal2` > 0   AND ".
                          "                 S.`intGoal1` - S.`intGoal2` > 0) ".
                          "                   OR ".
                          "                (W.`intGoal1` - W.`intGoal2` < 0   AND ".
                          "                 S.`intGoal1` - S.`intGoal2` < 0) ".
                          "                   OR ".
                          "                (W.`intGoal1` - W.`intGoal2` = 0   AND ".
                          "                 S.`intGoal1` - S.`intGoal2` = 0)) AND ".
                          "                 W.`intGoal1` - W.`intGoal2` = S.`intGoal1` - S.`intGoal2` ,1,0 ".
                          "               ) ".
                          "           ) AS POINTS ".
                          "FROM `tblwette` W, `tblspieltag` S ".
                          "WHERE S.`lngIndex` = W.`intSpielid` AND ".
                          "        S.`intStatus` = 2           AND ".  
                          "        S.`intTag`    = $actDay     AND ".
                          "        W.`intTag`    = $actDay ".
                          "GROUP BY W.intUserID ".        
                          "ORDER BY POINTS DESC ";
                
		
		if(mysqli_num_rows($res)>0)
		{
			include("menu.php");
			$user = mysql_result($res, 0, "intUser");
			
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
											left Join tblwette as w ON(w.intSpielid = s.lngIndex AND w.intUserid=$user)
										      LEFT JOIN tblverein as v1 ON (v1.lngIndex=s.intVerein1)
										     LEFT JOIN tblverein as v2 ON (v2.lngIndex=s.intVerein2)
												 WHERE  s.intTag=$actDay
												 GROUP BY s.lngIndex ORDER BY s.lngIndex DESC";
			
			$result = mysqli_query($db, $SQL);
			$anzahl = mysqli_num_rows($result);
			?>
<table border="0" cellpadding="4" cellspacing="8" width="600" class="ttable">
	<form action="login.php" method="post" name="pageform">
	<input type="Hidden" name="actDay" value="<?php echo $actDay?>">
	<input type="Hidden" name="user" value="<?php echo $session?>">
	<input type="Hidden" name="action" value="">
	<input type="Hidden" name="xpage" value="">
	</form>
	<tr>
		<td colspan="2"><strong>Spieltags√ºbersicht</strong></td>
	</tr>

	<tr>
		<td colspan="2" class="title"><strong>Spieltag  <?php echo $actDay?></strong></td>
	</tr>
	<tr>
		<td valign="top">
			<form method="post" name="xform">
				<input type=hidden name="tippid" value="<?php echo $tid?>">
				<input type=hidden name="user" value="<?php echo $session?>">
				<input type=hidden name="action" value="wetten">
				<input type="hidden" name="day" value="<?php echo $actDay?>">
				<input type=hidden name="game" value="<?php echo $id?>">
			<table width="400" border="0" cellpadding="2" cellspacing="0" class="tablestyle">
				<tr>
					<td colspan="3" class="head"><strong>Paarung</strong></td>
					<td colspan="3" class="head"><strong>Ihr Tipp</strong></td>
					<td colspan="3" class="head" align="center"><strong>Ergebnis</strong></td>
					<td class="head"><strong>Punkte</strong></td>
				</tr>
				<?php
				for($i = 0; $i< $anzahl; $i++)
				{
					$id  = mysql_result($result,$i, "lngIndex");
					$tid  = mysql_result($result,$i, "tippid");
					$team1 = mysql_result($result,$i, "Team1");
					$team2 = mysql_result($result,$i, "Team2");
					$mgoal1 = mysql_result($result,$i, "myg1");
					$mgoal2 = mysql_result($result,$i, "myg2");
					
					$rgoal1 = mysql_result($result,$i, "rg1");
					$rgoal2 = mysql_result($result,$i, "rg2");
					
					$status = mysql_result($result,$i, "intStatus");
					$point = mysql_result($result,$i, "xPOINT");
					$style = "secondline";
					if($i%2)
						$style = "firstline";
					if($status != 0)
					{
						if($status != 2)
						{
							$point="-";
							$rgoal1 = "-";
							$rgoal2 = "-";
							$style = "failedline";
						}
					?>
						
					<tr>
						<td class="<?php echo $style?>">
							<?php echo $team1?>
						</td>
						<td align="center" class="<?php echo $style?>" width="5">:</td>
						<td class="<?php echo $style?>">
							<?php echo $team2?>
						</td>
						
						
						<td align="right" class="<?php echo $style?>">									
							<?php echo $mgoal1?>
						</td>
						<td align="center" class="<?php echo $style?>" width="5">:</td>
						<td align="left" class="<?php echo $style?>">
							<?php echo $mgoal2?>
						</td>
						
						
						
						<td align="right" class="<?php echo $style?>" width="25">
							<?php echo $rgoal1?>
						</td>
						<td align="center" class="<?php echo $style?>" width="5">:</td>
						<td align="left" class="<?php echo $style?>" width="25">
							<?php echo $rgoal2?>
						</td>
						<td align="center" class="<?php echo $style?>">
							<strong><?php echo $point?></strong>
						</td>
					</tr>
					<?php
					}
					else
					{
					//SBE hier weitermachen f¸r im vorraustippen
					?>
					<tr>
						<td class="<?php echo $style?>">
							<?php echo $team1?>
						</td>
						<td align="center" class="<?php echo $style?>" width="5">:</td>
						<td class="<?php echo $style?>">
							<?php echo $team2?>
						</td>
						
						
						<td align="right" class="<?php echo $style?>">									
							<input type="text" name="goal1_<?php echo $id?>" style="width:25px" value="<?php echo $mgoal1?>">
						</td>
						<td align="center" class="<?php echo $style?>" width="5">:</td>
						<td align="left" class="<?php echo $style?>">
							<input type="text" name="goal2_<?php echo $id?>" style="width:25px" value="<?php echo $mgoal2?>">
						</td>
						
						
						
						<td align="right" class="<?php echo $style?>" width="25">
							<?php echo $rgoal1?>
						</td>
						<td align="center" class="<?php echo $style?>" width="5">:</td>
						<td align="left" class="<?php echo $style?>" width="25">
							<?php echo $rgoal2?>
						</td>
						<td align="center" class="<?php echo $style?>">
							&nbsp;
						</td>
					</tr>
					<?php
					}
				}
				?>
			</table>
			<table width="400" border="0" cellpadding="2" cellspacing="0">
				<tr>
					<td colspan="10" align="right">
						<img src="./pic/tippen.gif" border="0" onclick="document.xform.submit()" style="cursor:pointer">
					</td>
				</tr>
			</table>
			</form>
		</td>
		<td valign="top">
			<table width="150" border="0" cellpadding="2" cellspacing="0" class="tablestyle">
				<tr>
					<td class="head"><strong>User</strong></td>
					<td class="head" align="center"><strong>Punkte</strong></td>
				</tr>
				<?php
					$dayres = mysqli_query($db, $SQLtab);
					$cnt = mysqli_num_rows($dayres);
					for($i=0; $i < $cnt; $i++)
					{
					    $classname = "secondline";
					    if($i % 2)
					    	$classname = "firstline";
					    if($user == mysql_result($dayres, $i, "USERID"))
					    {?>
					    	<tr bgcolor="#000000">
					    		<td style="color:white"><strong><?php echo mysql_result($dayres,$i, "USER")?></strong></td>
					    		<td style="color:white" align="center"><strong><?php echo mysql_result($dayres,$i, "POINTS")?></strong></td>
					    	</tr>
					    <?php
					    }
					    else{
					    ?>
					    	<tr onmouseover="this.style.cursor='pointer';this.style.backgroundColor='lightyellow'" onmouseout="this.style.backgroundColor=''" class="<?php echo $classname?>" onclick="openuser(<?php echo  mysql_result($dayres, $i, "USERID")?>,<?php echo $actDay?>)">
					    		<td class="<?php echo $classname?>">
					    			<?php echo mysql_result($dayres,$i, "USER")?>
					    		</td>
					    		<td align="center" class="<?php echo $classname?>"><?php echo mysql_result($dayres,$i, "POINTS")?></td>
					    	</tr>
					    <?php
					    }					
					}
					?>
			</table>
		</td>
	</tr>
	<tr>
		<td align="right" colspan="2">
			<table border="0" cellpadding="3" cellspacing="0">
				<tr>
				<?php
				for($xyzi = 1; $xyzi <= $maxrow; $xyzi++)
				{
					if(($xyzi % 10)==0)
						echo "</tr><tr>";
				?>
				<td class="head"><input type="Button" value="<?php echo $xyzi?>" onclick="document.pageform.xpage.value=<?php echo $xyzi?>;document.pageform.submit()" style="width:20px"></td>
				<?php
				}
				?>
				</tr>
			</table>	
		</td>
	</tr>
	<tr>
		<td align="center"><a href="http://www.script-fabrik.de/banner/banner.php?link=1&type=fussball" target="_blank"><img src="http://www.script-fabrik.de/banner/banner.php?link=0&type=fussball" border="0"></a></td>
	</tr>
</table>
			<?php
			
			$action = " 1";
		}
			?>