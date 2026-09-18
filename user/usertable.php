<?php
	if(!isset($_POST))
		$_POST  = $HTTP_POST_VARS;
	if(!isset($_GET))
		$_GET  = $HTTP_GET_VARS;
	include_once("../common/include.php");
	$user = $_GET['user'] ?? ($_POST['user'] ?? ($menuuser ?? ''));
		
	if(isset($user))
	{
		include("menu.php");
		$session = $user;
		$hlp = mysqli_query($db,"select * from tblsession where strSessionid='$session'");
		if(mysqli_num_rows($hlp)>0)
			$user = mysql_result($hlp, 0, "intUser");
		else
			$user=-1;
	}
	
	$pstart = $_POST['xpage'] ?? null;
	if(!isset($pstart))
		$pstart = 1;
		
	$end = $pstart * 30;
	$pstart = $end - 30;
	/*$start = 0;
	$end = $start+20;*/
	$result = mysqli_query($db, "select * from tblgamer ORDER BY intPoint, strAlias ASC");
	$maxrow = intval(mysqli_num_rows($result)/30) + 1;
	
	$result = mysqli_query($db,"SELECT LngIndex as GamerId, intPoint as gamerScore, strAlias, intMoney from tblgamer ORDER BY intPoint DESC, strAlias ASC");
	$anzahl = mysqli_num_rows($result);
	
        /* hole liste aller spieler mit scores  */  
        $userPoints = getUserResultArray();

        /* Die Trefferquote bezieht sich auf alle bereits beendeten
           Begegnungen, nicht nur auf die vom jeweiligen Spieler getippten
           Partien. Pro Begegnung sind maximal vier Punkte moeglich. */
        $finishedMatchesResult = mysqli_query($db, "SELECT COUNT(*) AS cnt FROM tblspieltag WHERE intStatus=2");
        $finishedMatches = (int)mysql_result($finishedMatchesResult, 0, "cnt");
        $maximumPlayedPoints = $finishedMatches * 4;
        
	$rank=0;
	$previousPoints=null;

        ?>

<table border="0" cellpadding="4" cellspacing="8" width="600" class="ttable">
	<form action="login.php" method="post" name="pageform">
		<input type="Hidden" name="actDay" value="">
		<input type="Hidden" name="user" value="<?php echo $session?>">
		<input type="Hidden" name="action" value="table">
		<input type="Hidden" name="xpage" value="">
	</form>
	<tr>
		<td><strong>Die Tabelle</strong></td>
	</tr>
	<tr>



			<table width="600" border="0" cellpadding="2" cellspacing="0" class="minitable">
				<tr>
					<td class="head" width="35"><strong>Rank</strong></td>
					<td class="head" width="290"><strong>User</strong></td>
					<td class="head" width="60"><strong>Punkte</strong></td>
					<td class="head" width="80"><strong>Trefferquote</strong></td>
					<td class="head" width="120"><strong>Getippte Spieltage</strong></td>
				</tr>
				<?php
				for($i = 0; $i < $anzahl; $i++)
				{
					$id = mysql_result($result, $i, "GamerId");
					$alias = mysql_result($result, $i, "strAlias");
					$point = 0;

                                        foreach ($userPoints as $item)
                                        {
                                            if ( $item[0] == $id )
                                            {
                                        	 $point = $item[2];
                                        	 break;
       	                                    }
                                        }                          
					if($previousPoints === null || (int)$point !== $previousPoints)
						$rank = $i + 1;
					$previousPoints = (int)$point;
						
					$money = mysql_result($result, $i, "intMoney");
					$hlp = mysqli_query($db, "select COUNT(intTag) AS cnt from tblwette Where intUserid=$id");
					$tipCount = (int)mysql_result($hlp, 0, "cnt");
					$rel = $maximumPlayedPoints > 0
						? round(($point / $maximumPlayedPoints) * 100, 2)
						: 0;
					if($user == $id)
					{
					?>
					<tr bgcolor="black">
						<td align="right" style="color:white"><strong><?php echo $rank?>.&nbsp;</strong></td>
						<td style="color:white"><strong><?php echo $alias?></strong></td>
						<td align="center" style="color:white"><strong><?php echo $point?></strong></td>
						<td align="center" style="color:white"><strong><?php echo $rel?>%</strong></td>
						<td align="center" style="color:white"><strong><?php echo round($tipCount/9,0)?></strong></td>
					</tr>
					<?php
					}
					else
					{


"secondline";
						if($i % 2)
							$classname = "firstline";
					?>
					<tr class="<?php echo $classname?>">
						<td align="right" class="<?php echo $classname?>"><strong><?php echo  $rank?>.&nbsp;</strong></td>
						<td class="<?php echo $classname?>"><strong><?php echo  $alias?></strong></td>
						<td align="center" class="<?php echo $classname?>"><strong><?php echo  $point?></strong></td>
						<td align="center" class="<?php echo $classname?>"><strong><?php echo  $rel?>%</strong></td>
						<td align="center" class="<?php echo $classname?>"><strong><?php echo  round($tipCount/9,0)?></strong></td>
					</tr>
					<?php
					}
				}			
				?>
			</table>
		</td>
	</tr>
	<tr>
	</tr>
</table>
