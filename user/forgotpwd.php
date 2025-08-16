<?php
	if(!isset($_POST))
		$_POST  = $HTTP_POST_VARS;
	if(!isset($_GET))
		$_GET  = $HTTP_GET_VARS;
	include_once ("../common/include.php");
	
	$action = $_POST['action'];
	
	if($action=="sendpwd")
	{
		$db=mysqli_connect("$address", "$dbuser", "$dbpasswd",'bl_wette');
		
		$username = $_POST['username'];
		$SQL="SELECT * FROM tblgamer WHERE strAlias=\"$username\"";
		$result = mysqli_query($db, $SQL);
		
		if(mysqli_num_rows($result)==1)
		{
			$mail = mysql_result($result,0,"strEmail");
			$id = mysql_result($result,0,"lngIndex");
			$key = "fussball";
			$Length = 10; 
			$key = md5($key); 
		
			$keylen = strlen($key); 
		
			srand ((double) microtime() * 1000000); 
			$Begin = rand(0,($keylen-$Length-1)); 
			$Password = substr($key, $Begin, $Length); 
			$newpwd = md5($username ."*". $Password);
			//echo $newpwd ."<br>";
			
			$SQL = "UPDATE tblgamer SET strPasswort=\"$newpwd\" WHERE lngIndex=$id";
			mysqli_query($db, $SQL);
			
			$Text = "<html>
			<style>
				td {font-family:arial;font-size:8pt;color:#242424;}
			</style>
			
			<table>
				<tr><td>
			Guten Tag,<br>
			Sie haben ein neues Passwort angefordert. Bitte loggen Sie sich in das Tippspiel von $hp mit folgenden Daten ein:<br><br>
			Username: $username <br>
			Passwort: $Password <br><br>
			�ndern Sie das Passwort bitte umgehend, und merken Sie sich ihr neues Passwort gut.</td></tr></table></html>";
			
			$header = "From: \"$hp\" <$email>\r\nContent-Type: text/html; charset=iso-8859-1\r\nContent-Transfer-Encoding: quoted-printable";
			?>
				<html>
					<head>
					<link rel="stylesheet" type="text/css" href="../common/style.css">
					<script language="JavaScript1.2">
						alert("Das Passwort wurde per E-Mail an Sie versandt!");
						window.close();
					</script>
					</head>
					<body topmargin="5" leftmargin="5" bottommargin="0" rightmargin="0" marginheight="20" marginwidth="20">
					</body>
				</HTML>
			<?php
			mail($mail,"Bundesliga-wette Passwort vergessen",$Text,$header);
		}
		else
		{
		?>
				<html>
					<head>
					<link rel="stylesheet" type="text/css" href="../common/style.css">
					<script language="JavaScript1.2">
						window.close();
					</script>
					</head>
					<body topmargin="5" leftmargin="5" bottommargin="0" rightmargin="0" marginheight="20" marginwidth="20">
					</body>
				</HTML>
			<?php
		}
		
	}
	else
	{
	?>
		<html>
			<head>
				<title>Passwort vergessen</title>
				<link rel="stylesheet" type="text/css" href="../common/style.css">
				<script language="JavaScript1.2">
				</script>
			</head>
			<body style="border:none" topmargin="0" leftmargin="0" bottommargin="0" rightmargin="0" marginheight="0" marginwidth="0">
				<table border="0" cellpadding="4" cellspacing="0" width="100%" height="100%" align="center" class="ttable">
					<FORM method="post">
					<input type="Hidden" name="action" value="sendpwd">
					<tr>
						<td><strong>Passwort zusenden</strong></td>
					</tr>
					<tr>
						<td>Bitte geben Sie hier Ihren Usernamen an, von dem Sie das Passwort vergessen haben, und es wird Ihnen umgehend ein neues Passwort per E-Mail zugesandt.</td>
					</tr>
					<tr>
						<td><input type="Text" name="username" style="width:100%"></td>
					</tr>
					<tr>
						<td align="right"><input type="image" src="pic/sendmessage.gif"></td>
					</tr>
					</FORM>
				</table>
			</body>
		</html>
	<?php
	}
?>