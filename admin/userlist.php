<?php 
	if(!isset($_POST))
		$_POST  = $HTTP_POST_VARS;
	if(!isset($_GET))
		$_GET  = $HTTP_GET_VARS;
	require("../common/include.php");
	$db=mysqli_connect("$address", "$dbuser", "$dbpasswd",'bl_wette');
	
	
	$SQL = "SELECT g.*, MAX(s.intTag) as intTag FROM tblgamer as g
			  LEFT JOIN tblwette as w ON intUserid=g.lngIndex
			    LEFT JOIN tblspieltag as s ON w.intSpielid=s.lngIndex
			
			GROUP BY g.lngIndex
			
			ORDER BY intTag DESC";
    
	$result = mysqli_query($db, $SQL);
?>
<br>
<table width="600">
	<tr>
		<td>
			<strong>Die User, die Rot hinterlegt sind, haben seit mindestens 3 Spieltagen nicht getippt.
			Es kann davon ausgegangen werden, dass diese User inaktiv sind, und gel�scht werden k�nnten.<br><br>
			Sobald sie auf l�schen klicken, wird der User sofort gel�scht. Dr�cken Sie diesen Button nur, wenn sie sich absolut sicher sind, da� sie das wollen.
			</strong>
		</td>
	</tr>
	<tr>
		<td>
			<table cellpadding="2" cellspacing="0">
				<?php 
					for($i=0; $i < mysqli_num_rows($result); $i++)
					{
						$dif = $newDay - mysql_result($result, $i, "intTag");
						$bgcolor = "";
						$color = "";
						if($dif >= 3)
						{
							$bgcolor="FF0000";
							$color="FFFFFF";
						}
					?>
						<tr bgcolor="<?php echo  $bgcolor?>">
							<form method="post" action="admin.php">
							<input type="Hidden" name="action" value="deleteuser">
							<input type="Hidden" name="userid" value="<?php echo  mysql_result($result, $i, "lngIndex")?>">
							<td style="color:<?php echo  $color?>">
								<?php echo  $i+1?>
							</td>
							<td style="color:<?php echo  $color?>">
								<?php echo  mysql_result($result, $i, "strAlias")?>
							</td>
							<td style="color:<?php echo  $color?>">
								<?php echo  $dif?>
							</td>
							<td>
								<td><input type="Submit" value="L�schen"></td>
							</td>
							</form>
						</tr>
					<?php }
				?>
			</table>
		</td>
	</tr>
	<tr>
		<td><input type="button" onclick="document.location.href='<?php echo $thisfile?>'" value="zum Hauptmen�"></td>
	</tr>
</table>