<?php
	include("menu.php");
	$user = $menuuser;
	$helpx = mysqli_query($db, "select * from tblsession where strSessionid='$user'");
	//echo mysqli_error();
	$helpuser= mysql_result($helpx, 0, "intUser");
	$result = mysqli_query($db, "select * from tblgamer where lngIndex=$helpuser");
	$xmail = mysql_result($result,0, "strEmail");
	$xrem =  mysql_result($result,0, "boolRemember");
	$username = mysql_result($result,0, "strAlias");
	
	$subaction = $_POST['subaction'];
	
	if($subaction == "delete")
	{
		$id = $_POST['id'];
		$SQL = "DELETE FROM tblmessage WHERE lngIndex=$id";
		mysqli_query($db, $SQL);
	}
?>
<script>
	function checkform()
	{
		var user = "<?php echo $username?>";
		var fehler = 1;
		/*
		if(document.userdata.oldpwd.value != "")
		{
		*/   
			if(document.userdata.newpwd.value != "")
			{
				if(document.userdata.newpwd.value == document.userdata.newpwd2.value)
				{
					fehler = 0;
					var str = "";
					
					str = user + "*" + document.userdata.oldpwd.value;
					document.userdata.oldpwd.value = MD5(str);
					
					str = user + "*" + document.userdata.newpwd.value;
					document.userdata.newpwd.value = MD5(str);
					document.userdata.newpwd2.value = document.userdata.newpwd.value;
					
					document.userdata.submit();
				}
			}
		/*
		}
		else
		{
			fehler=0;
			document.userdata.submit();
		}
		*/
		if(fehler == 1)
			alert("Bitte geben Sie altes Passwort ein, bzw. überprüfen Sie, ob die neuen passwörter gleich sind!");
	}
	
	function openMail(ysnRead,mid)
	{
		val="";
		
		if(ysnRead)
		{
			val="message.php?session=<?php echo $user?>&action=read&id=" + mid;
		}
		else
		{
			val="message.php?session=<?php echo $user?>&action=new";
		}
		window.open(val,"Message","status=0,width=450, height=300");
	}
</script>
	<?php
	if(isset($message) || $message != ""){?>
	<table width="600" border="1" cellpadding="1" cellspacing="1">
		<tr>
			<td class="error">
				<strong><?php echo $message?></strong>
			</td>
		</tr>
	</table><?php
	}?>
<table border="0" cellpadding="4" cellspacing="8" width="600" class="ttable">
	<tr>
		<td>
			<table border="0" cellpadding="1" cellspacing="0" width="380">
				<form method="post" action="login.php" name="userdata">
				<input type=hidden name="user" value="<?php echo $user?>">
				<input type=hidden name="action" value="saveuser">
				<tr>
					<td colspan="2"><strong>Meine Daten</strong></td>
				</tr>
				<tr><td><img src="pic/shim.gif" width="1" height="10" alt="" border="0"></td></tr>
				<tr>
					<td width="130">E-Mail</td>
					<td>
					<input type="text" name="xmail" value="<?php echo $xmail?>" style="width:250px">
					</td>
				</tr>
				<tr>
					<td>altes Passwort</td>
					<td><input type="Password" name="oldpwd" value="" style="width:250px"></td>
				</tr>
				<tr>
					<td>neues Passwort</td>
					<td><input type="Password" name="newpwd" value="" style="width:250px"></td>
				</tr>
				<tr>
					<td>Passwort bestätigen</td>
					<td><input type="Password" name="newpwd2" value="" style="width:250px"></td>
				</tr>
				<tr><td><img src="pic/shim.gif" width="1" height="10" alt="" border="0"></td></tr>
				<tr>
					<td valign="top"><strong>Erinnerung per Mail?</strong></td>
					<td colspan="2">
						<input type="radio" value="0" name="xremember" <?php if($xrem==0){echo "checked";}?>>Nein<br>
						<input type="radio" value="1" name="xremember" <?php if($xrem==1){echo "checked";}?>>Ja
					</td>
				</tr>
				<tr>
					<td colspan="2" align="right"><img src="pic/datasave.gif" width="120" height="20" alt="" border="0" onclick="checkform()" style="cursor:pointer"></td>
				</tr>
				</form>
			</table>
		</td>
	</tr>
	<tr>
		<td align="left">
			<a href="http://www.script-fabrik.de/banner/banner.php?link=1&type=fussball" target="_blank"><img src="http://www.script-fabrik.de/banner/banner.php?link=0&type=fussball" border="0"></a>
		</td>
	</tr>
	<tr>
		<td>
			<table border="0" cellpadding="0" cellspacing="0" width="570">
				<tr>
					<td class="head" width="300"><strong>&nbsp;Betreff</strong></td>
					<td class="head" width="170"><strong>Von</strong></td>
					<td class="head" width="80"><strong>gesendet am</strong></td>
					<td class="head" width="20">&nbsp;</td>
				</tr>
				<?php
				$SQL = "SELECT msg.*, tblgamer.strAlias as strFromName from tblmessage as msg".
						" LEFT JOIN tblgamer on tblgamer.lngIndex=msg.lngUserFromID".
						" WHERE lngUserToID=$helpuser ORDER BY dtmCreate DESC";
				$result = mysqli_query($db, $SQL);
				$cnt = mysqli_num_rows($result);
				
				for($i = 0; $i < $cnt; $i++)
				{
				?>
				<tr class="statdata" style="<?php if(mysql_result($result, $i, "ysnRead") == 0) {echo "font-weight:bold;";}?>" onmouseover="this.style.backgroundColor='lightyellow'; this.style.cursor='pointer'" onmouseout="this.style.backgroundColor=''">
				<form method="post" name="frm<?php echo $i?>">
				<input type="Hidden" name="user" value="<?php echo $user?>">
				<input type="Hidden" name="action" value="useroffice">
				<input type="Hidden" name="subaction" value="delete">
				<input type="Hidden" name="id" value="<?php echo mysql_result($result, $i, "lngIndex")?>">
					<td onclick="openMail(1,<?php echo mysql_result($result, $i, "lngIndex")?>)" colspan="3">
						<table border="0" width="100%" cellpadding="2" cellspacing="0">
							<tr>
								<td width="300" style="border-bottom: 1px solid gray" valign="top">
									<?php echo mysql_result($result, $i, "strTitle")?>&nbsp;
								</td>
								<td width="170" style="border-bottom: 1px solid gray" valign="top">
									<?php echo mysql_result($result, $i, "strFromName")?>
								</td>
								<td width="80" style="border-bottom: 1px solid gray" valign="top">
									<?php echo mysql_result($result, $i, "dtmCreate")?>
								</td>
							</tr>
						</table>
					</td>
					<td style="border-bottom: 1px solid gray" valign="top">
						<img src="trash.gif" border="0" alt="l&ouml;schen" align="absmiddle" onclick="document.frm<?php echo $i?>.submit()" onmouseover="this.style.cursor='pointer'">
					</td>
				</form>
				</tr>
				<?php
					}
				?>
				<tr>
					<td align="right" colspan="4" height="40"><img src="pic/newmessage.gif" width="120" height="20" alt=" Eine neue Message schreiben" border="0" onclick="openMail(false)" style="cursor:pointer"></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
