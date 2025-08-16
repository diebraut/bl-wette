<?php
	if(!isset($_POST))
		$_POST  = $HTTP_POST_VARS;
	if(!isset($_GET))
		$_GET  = $HTTP_GET_VARS;
	include_once("../common/include.php");
	$db=mysqli_connect("$address", "$dbuser", "$dbpasswd",'bl_wette');
	$action = $_POST['action'];
	$session = $_GET['session'];
	if(!isset($session))
		$session = $_POST['session'];
	
	if(!isset($action))
		$action = $_GET['action'];
	$helpx = mysqli_query($db, "select * from tblsession where strSessionid='$session'");
	$userid= mysql_result($helpx, 0, "intUser");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<link rel="stylesheet" type="text/css" href="../common/style.css">
	<title>Tippspiel Message</title>
	<script language="javascript">
	</script>
</head>
<body style="border:none" topmargin="2" leftmargin="2" bottommargin="2" rightmargin="2" marginheight="0" marginwidth="0" bgcolor="whitesmoke" style="background-image:url(./pic/sw_h.gif); background-repeat:repeat-y;">
<?php
	if(!isset($action))
	{?>
<table border="0" cellpadding="4" cellspacing="8" width="100%" class="ttable">
	<tr>
		<td style="color:white">
			<strong>Betreff</strong>
		</td>
		<td style="color:white">
			<strong>Von</strong>
		</td>
		<td style="color:white">
			<strong>gesendet am</strong>
		</td>
		<td>
			&nbsp;
		</td>
	</tr>
	<?php
		$SQL = "SELECT msg.*, tblgamer.strAlias as strFromName from tblmessage as msg".
				" LEFT JOIN tblgamer on tblgamer.lngIndex=msg.lngUserFromID".
				" WHERE lngUserToID=$userid ORDER BY dtmCreate DESC";
		$result = mysqli_query($db, $SQL);
		$cnt = mysqli_num_rows($result);
		
		for($i = 0; $i < $cnt; $i++)
		{
		?>
			<tr style="<?php if(mysql_result($result, $i, "ysnRead") == 0) {echo "font-weight:bold;";}?>" onclick="document.location.href='message.php?action=read&session=<?php echo $session?>&id=<?php echo mysql_result($result, $i, "lngIndex")?>'" onmouseover="this.style.backgroundColor='lightyellow'; this.style.cursor='hand'" onmouseout="this.style.backgroundColor=''">
				<td width="150" style="border-bottom: 1px solid gray">
					<?php echo mysql_result($result, $i, "strTitle")?>
				</td>
				<td width="100" style="border-bottom: 1px solid gray">
					<?php echo mysql_result($result, $i, "strFromName")?>
				</td>
				<td style="border-bottom: 1px solid gray">
					<?php echo mysql_result($result, $i, "dtmCreate")?>
				</td>
				<td style="border-bottom: 1px solid gray">
					<a href="message.php?session=<?php echo $session?>&action=delete&id=<?php echo mysql_result($result, $i, "lngIndex")?>">
					<img src="trash.gif" border="0" alt="l&ouml;schen" align="absmiddle">
					</a>
				</td>
			</tr>
		<?php
		}
	?>
	<tr>
		<td>
			<strong><a href="message.php?action=new&session=<?php echo $session?>">Neue Message</a></strong>
		</td>
	</tr>
	</table>
	<?php
	}
	else if($action == "delete")
	{
		$id = $_GET['id'];
		$SQL = "DELETE FROM tblmessage WHERE lngIndex=$id";
		mysqli_query($db, $SQL);
		?>
		<script>
			window.close();
		</script>
		<?php
	}
	else if($action == "read")
	{
		$id = $_GET['id'];
		$SQL = "UPDATE tblmessage SET ysnRead=1 WHERE lngIndex=$id";
		mysqli_query($db, $SQL);
		$SQL = "SELECT msg.*, tblgamer.strAlias as strFromName from tblmessage as msg".
				" LEFT JOIN tblgamer on tblgamer.lngIndex=msg.lngUserFromID".
				" WHERE msg.lngIndex=$id";
		$result = mysqli_query($db, $SQL);
	?>
		<table border="0" cellpadding="4" cellspacing="8" width="100%" height="100%" class="ttable">
			<tr>
				<td align="center">
					<table width="420" height="100%" border="0" cellpadding="1" cellspacing="0">
						<tr>
							<td width="100" height="25" valign="top">Nachricht von:</td>
							<td height="25" width="320" valign="top"><strong><?php echo mysql_result($result, $i, "strFromName")?></strong>&nbsp; &nbsp; &nbsp;<em>[vom: <strong></strong>]</em></td>
						</tr>
						<tr>
							<td width="100" height="25" valign="top">Betreff:</td>
							<td height="25" valign="top"><strong><?php echo mysql_result($result, $i, "strTitle")?></strong></td>
						</tr>
						<tr>
							<td colspan="2" bgcolor="#f5f5f5" valign="top" style="padding:5px"><?php echo mysql_result($result, $i, "strMessage")?></td>
						</tr>
						<tr>
							<td colspan="2" align="right" height="30"><img src="pic/close.gif" width="120" height="20" alt="" border="0" style="cursor:hand" onclick="window.close()"></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	<?php
	}
	else if($action == "send")
	{
		$lngTo = $_POST['lngTo'];
		$strSubject = $_POST['strSubject'];
		$strMessage = nl2br($_POST['strMessage']);
		$SQL = "INSERT INTO tblmessage(lngUserToID, lngUserFromID, strTitle, strMessage, dtmCreate) VALUES($lngTo, $userid, \"$strSubject\", \"$strMessage\", NOW())";
		mysqli_query($db, $SQL);
		?>
		<script>
			window.close();
		</script>
		<?php
	}
	else if($action == "new")
	{?>
		<script>
			function checkUser()
			{
				if(document.messageFrm.lngTo.value != -1)
					document.messageFrm.submit()
				else
					alert("Bitte w�hlen Sie einen User aus!");
			}
		</script>
		
<table border="0" cellpadding="4" cellspacing="8" width="100%" height="100%" bgcolor="#d4e6cb">
	<tr>
		<td align="center">
			<form method="post" action="message.php" name="messageFrm">
			<input type="Hidden" name="session" value="<?php echo $session?>">
			<input type="Hidden" name="action" value="send">
			<table border="0" cellpadding="4" cellspacing="0" width="420">
				<tr>
					<td colspan="2"><strong>Neue Nachricht schreiben</strong></td>
				</tr>
				<tr>
					<td width="50">An:</td>
					<td width="370">
						<?php
							$SQL = "SELECT * FROM tblgamer WHERE lngIndex<>$userid ORDER BY strAlias";
							$result = mysqli_query($db, $SQL);
							$cnt = mysqli_num_rows($result);
						?>
						<select style="width:100%" name="lngTo">
							<option value="-1">Bitte Ausw&auml;hlen</option>
							<?php
								for($i = 0; $i < $cnt; $i++)
								{
									?><option value="<?php echo mysql_result($result, $i, "lngIndex")?>"><?php echo mysql_result($result, $i, "strAlias")?></option><?php
								}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td>Betreff:</td>
					<td><input type="Text" name="strSubject" style="width:100%"></td>
				</tr>
				<tr>
					<td colspan="2"><textarea style="width:100%" name="strMessage" rows="10"></textarea></td>
				</tr>
				<tr>
					<td colspan="2" align="right"><img src="pic/close.gif" width="120" height="20" alt="" border="0" style="cursor:hand" onclick="window.close()">&nbsp;<img src="pic/sendmessage.gif" width="120" height="20" alt="" border="0" onclick="checkUser()" style="cursor:hand"></td>
				</tr>
			</table>
			</form>
		</td>
	</tr>
</table>
	<?php
	}
	?>
</body>
</html>
