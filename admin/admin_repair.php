<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
<?php


//error_reporting(E_ERROR | E_PARSE);
error_reporting(E_ALL);

ini_set('display_errors', 1);

include ("../common/include_repair.php");

if(!isset($_POST))
	$HTTP_POST_VARS  = $_POST;
if(!isset($_GET))
	$HTTP_GET_VARS  = $_GET;

$admin = $_GET['admin'];

if ($admin == "roland")
{	
?>	
   <title>Bundesliga-Tippspiel Adminbereich </title>
<?php
}
else
{
?>
   <title>Nur f�r Administrator erlaubt!!!!</title>
<?php
}
?>

<link rel="stylesheet" type="text/css" href="../common/style.css">
</head>
<body>


<?php
  
  if ($admin == "roland")
  {
  	   
	   $action = $_GET['action'];
	   if(!isset($action))
	   	$action = $_POST['action'];
           
	   $thisfile="admin_repair.php";

	   $db = mysqli_connect("$address", "$dbuser", "$dbpasswd",'bl_wette');
	   mysqli_select_db("$dbname", $db);

	   $result = mysqli_query($db,"select intDay, dtmClose from tblinfo");

	   if( !$result )
	   {
	   	mysqli_query($db,"INSERT INTO tblinfo (intDay, dtmClose) VALUES(1, NULL)");
	   	$newDay=1;
	   }
	   else
	   {
	   	$newDay = mysql_result($result, 0, "intDay");
	   	$close = mysql_result($result, 0, "dtmClose");
	   	if($newDay == "")
	   	{
	   		mysqli_query($db,"UPDATE tblinfo SET intDay=1, dtmClose=NULL");
	   		$newDay=1;
	   	}
	   }
	   if ( $action != "startNewSeason" )
	   {
	   ?>	  
	      <b>Aktueller Spieltag: <?php echo $newDay?></b>	   
	   <?php
	   }
	   if(!isset($action))
	   {
	   	$error = $_GET['error'];
	   	switch($error)
	   	{
	   		case "datetime":
	   			?>
	   			<br>
	   			<br>
	   			<strong style="color:red">Ung�ltiges Datum</strong>
	   			<br>
	   			<?php
	   			break;
	   	}
	   	?>
     
	   	<table>
	   		</tr><tr>
	   		<td><input type="button" value="Start neue Saison" style="width:600px" onclick="document.location.href='<?php echo $thisfile?>?action=startNewSeason&admin=<?php echo $admin?>'"></td>
	   		</tr>
	   	</table><br/><br/><br/>
     
	   	<table>
	   	<tr>
	   	<td><input type="button" value="Spieltag eintragen" style="width:150px" onclick="document.location.href='<?php echo $thisfile?>?action=newDay&admin=$admin?>'"></td>
	   	</tr><tr>
	   	<td><input type="button" value="Spieltag schlie�en" style="width:150px" onclick="document.location.href='<?php echo $thisfile?>?action=makeClosed'"></td>
	   	</tr><tr>
	   	<td><input type="button" value="Erinnerung senden" style="width:150px" onclick="document.location.href='<?php echo $thisfile?>?action=sendRemember'"></td>
	   	</tr>
	   	 <tr>
	   	<td><input type="button" value="Spieltag auswerten" style="width:150px" onclick="document.location.href='admin2.php?admin=<?php echo $admin?>'"></td>
	   	</tr>
	   	<tr>
	   	<td><input type="button" value="n�chster Spieltag" style="width:150px" onclick="document.location.href='<?php echo $thisfile?>?action=makeOpen'"></td>
	   	</tr>
	   	<tr>
	   	<td><input type="button" value="Rolfs Mondtaste" style="width:150px" onclick="document.location.href='<?php echo $thisfile?>?action=reOpenSpieltag&admin=<?php echo $admin?>'"></td>
	   	</tr>
	   		<td><hr></td>
	   	</tr>
	   	<tr>
	   		<td>
	   			<table>
	   				<form method="post">
	   				<input type="Hidden" name="action" value="setCloseDate">
	   				<tr>
	   					<td>
	   						Automatisches schlie�en am
	   					</td>
	   					<td colspan="3">
	   						<?php echo $close?>
	   					</td>
	   				</tr>
	   				<tr>
	   					<td>
	   						N�chsten Spieltag automatisch am
	   					</td>
	   					<td>
	   						<select name="aday">
	   							<?php
	   							for($i=1; $i <= 31; $i++)
	   							{
	   								$seltext = "";
	   								if($i == date("d"))
	   									$seltext = "selected";
	   								?>
	   								<option value="<?php echo $i?>" <?php echo $seltext?> ><?php echo $i?></option>
	   							<?php
	   							}?>
	   						</select>
	   						<select name="amonth">
	   							<?php
	   							for($i=1; $i <= 12; $i++)
	   							{
	   								$seltext = "";
	   								if($i == date("m"))
	   									$seltext = "selected";
	   								?>
	   								<option value="<?php echo $i?>" <?php echo $seltext?> ><?php echo $i?></option>
	   							<?php
	   							}?>
	   						</select>
	   						<select name="ayear">
	   							<option value="<?php echo date("Y")-1?>" <?php echo $seltext?> ><?php echo date("Y")-1?></option>
	   							<option selected value="<?php echo date("Y")?>" <?php echo $seltext?> ><?php echo date("Y")?></option>
	   							<option value="<?php echo date("Y")+1?>" <?php echo $seltext?> ><?php echo date("Y")+1?></option>
	   						</select>
	   					</td>
	   					<td>
	   						<select name="fhour">
	   							<?php
	   								for($i=0; $i<24; $i++)
	   								{
	   							?>
	   							<option value="<?php echo $i?>"><?php echo $i?></option>
	   							<?php
	   							}?>
	   						</select> Uhr
	   					</td>
	   					<td>
	   						schlie�en
	   					</td>
	   				</tr>
	   				<tr>
	   					<td colspan="4">
	   						<input type="Submit" value="Setzen">
	   					</td>
	   				</tr>
	   				</form>
	   			</table>
	   		</td>
	   	</tr>
	   	<tr>
	   		<td><hr></td>
	   	</tr>
	   	<tr>
	   	<td><input type="button" value="Mannschaften Bearbeiten" style="width:150px" onclick="document.location.href='<?php echo $thisfile?>?action=setTeam&admin=<?php echo $admin?>'"></td>
	   	</tr>
<!   --
#    neuer Button f�r MailText Bearbeitung
#    JayDee 7/04
#    post@jd-gerke.de
--   >
     
	   	<tr>
	   	<td><input type="button" value="Mailtext �ndern" style="width:150px" onclick="document.location.href='<?php echo $thisfile?>?action=changeMailText&admin=<?php echo $admin?>'"></td>
	   	</tr>
<!   -- Ende JayDee -->
	   	<tr>
	   		<td><input type="button" value="Userverwaltung" style="width:150px" onclick="document.location.href='<?php echo $thisfile?>?action=userlist&admin=<?php echo $admin?>'"></td>
	   	</tr>
	   	<tr>
	   	<td><input type="button" value="Alles l�schen (ausser die vereine)" style="width:150px" onclick="document.location.href='<?php echo $thisfile?>?action=reset&admin=<?php echo $admin?>'"></td>
	   	</tr>
	   	<tr>
	   	<td><fieldset>
	   		<form action="<?php echo $thisfile?>" method="post" name="import" enctype="multipart/form-data">
	   		<input type="hidden" name="action" value="import">
	   		<table>
	   			<tr>
	   				<td>Importdatei:</td>
	   				<td><input type="file" name="binImportScript">
	   			</tr>
	   			<tr>
	   				<td colspan="2" align="right">
	   					<input type="submit" value="Import">
	   				</td>
	   			</tr>
	   		</table>
	   		</form>
	   	</fieldset>
	   	</tr>
	   	</table><?php
	   }
	   if($action == "import")
	   {
	   	$datei = $HTTP_POST_FILES["binImportScript"]["tmp_name"];
	   	$file = fopen($datei, "r+");
	   		$scriptdatei = fread($file, filesize($datei));
	   	fclose($file);
	   	$scriptdatei = split(";", $scriptdatei);
	   	
	   	$SQL = "DELETE FROM tblverein";
	   	mysqli_query($db, $SQL );
	   	echo mysqli_error();
	   	
	   	$SQL = "DELETE FROM tblspieltag";
	   	mysqli_query($db, $SQL );
	   	echo mysqli_error();
	   	
	   	$SQL = "DELETE FROM tblwette";
	   	mysqli_query($db, $SQL);
	   	echo mysqli_error();
	   	
	   	foreach($scriptdatei as $SQL)
	   	{
	   		if($SQL != "")
	   		{
	   			mysqli_query($db,$SQL);
	   			echo mysqli_error();
	   		}
	   	}
	   	?>
	   	
	   	<table>
	   		<tr>
	   			<td>
	   			<b>Der import wurde ausgef�hrt!</b>
	   			</td>
	   		</tr>
	   		
	   	</table>
	   	
	   	<?php
	   }
	   if($action == "setCloseDate")
	   {
	   	$fhour = $_POST['fhour'];
	   	if(strlen($fhour) == 1)
	   		$fhour = "0" + $fhour;
	   	/*$hour = $_POST['ahour'];
	   	$minute = $_POST['aminute'];*/
     
	   	$year = $_POST['ayear'];
	   	$month = $_POST['amonth'];
	   	$day = $_POST['aday'];
	   	if(checkdate($month, $day, $year))
	   	{
	   		$datetime = "$year-$month-$day $fhour:00:00";
	   //		$datetime = "$fdate $fhour:00:00";
	   		mysqli_query($db,"UPDATE tblinfo set dtmClose=\"$datetime\"" );
	   		?>
	   		<script language="JavaScript1.2">
	   			document.location.href="admin.php?admin=<?php echo $admin?>";
	   		</script>
	   		<?php
	   	}
	   	else
	   	{
	   		?>
	   		<script language="JavaScript1.2">
	   			document.location.href="admin.php?error=datetime&admin=<?php echo $admin?>";
	   		</script>
	   		<?php
	   	}
	   }
	   if($action=="deleteuser")
	   {
	   	$userid = $_POST['userid'];
     
	   	//alle tipps des users l�schen
	   	$SQL = "DELETE FROM tblwette WHERE intUserid=$userid";
	   	$result = mysqli_query($db,$SQL );
	   	echo mysqli_error();
     
	   	//alle nachrichten des users l�schen
	   	$SQL = "DELETE FROM tblmessage WHERE lngUserToID=$userid OR lngUserFromID=$userid";
	   	$result = mysqli_query($db, $SQL);
	   	echo mysqli_error();
     
	   	//alle nachrichten des users l�schen
	   	$SQL = "DELETE FROM tblgamer WHERE lngIndex=$userid";
	   	$result = mysqli_query($db, $SQL);
	   	echo mysqli_error();
     
	   	include("userlist.php");
	   }
	   if($action=="userlist")
	   {
	   	include("userlist.php");
	   }
     
     
	   if($action == "sendRemember")
	   {
	   	$tpl = tplload("mailtext.html");
	   	$result = mysqli_query($db,"select strEmail, strAlias from tblgamer where boolRemember=1");
	   	$mailcnt = mysqli_num_rows($result);
	   	$good = 0;
	   	for($i = 0; $i < $mailcnt; $i++)
	   	{
	   		$mail = mysql_result($result, $i, "strEmail");
	   		$name = mysql_result($result, $i, "strAlias");
	   		$text = tplprint($tpl, array(
	   			"NEWDAY" => $newDay,
	   			"NAME" => $name));
     
	   		$header = "from: \"$hp\" <$email>\r\nContent-Type: text/html";
	   		if(mail($mail,"Bundesliga-wette",$text,$header) == 1)
	   		{
	   			echo "<br>Email an $mail erfolgreich gesendet";
	   			$good++;
	   		}
	   	}
	   	echo "<br>Es wurden $good E-mails erfolgreich versendet";
	   	?><br><br><input type="button" onclick="document.location.href='<?php echo $thisfile?>?admin=<?php echo $admin?>'" value="zum Hauptmen�"><?php
	   }
     
#    neu eingef�hrt....
#    erm�glicht das Ver�ndern der MailText-Vorlage
#    JayDee 7/04
#    post@jd-gerke.de
     
	   if($action == "changeMailText")
	   {
     
	   	$fp = fopen("mailtext.html", "r");
	   	$text = fread($fp, filesize("mailtext.html"));
	   	fclose($fp);
?>   
     
	   	<table border="0" width="75%">
	   	  <tr>
	   	    <td width="43%" valign="top">
	   	      <form method="POST" action="admin.php?action=MailTextSpeichern">
	   	      <p><textarea rows="12" name="MailText" cols="60"><?php echo stripslashes($text); ?></textarea></p>
	   	      <p><input type="submit" value="Speichern" name="MailTextSpeichern">
	   	       <input type="reset" value="Zur�cksetzen" name="B2">
	   	       <input type="button" onclick="window.open('mailtext.html','Fenster2');" value="Vorschau"></p>
	   	      </form>
	   	    </td>
	   	    <td width="57%" valign="top" height="376"> Erlaubte Inserts:
	   	     <table border="0" width="59%">
	   	      <tr>
	   	        <td width="37%" valign="top">{NAME}</td>
	   	        <td width="63%">Name des Mitspielers</td>
	   	      </tr>
	   	      <tr>
	   	        <td width="37%" valign="top">{NEWDAY}</td>
	   	        <td width="63%">des aktuelle Spieltag</td>
	   	      </tr>
	   	     </table>
	   	      <br>
	   	      Erweiterung zusammengestellt durch:
	   	      <br>
	   	      JayDee - <a href="http://www.spzg-mauritz-erpho.de.ms" target="_blank">Spielmannszug Mauitz-Erpho</a>
	   	    </td>
	   	  </tr>
	   	</table>
	   	<br>&nbsp;<input type="button" onclick="document.location.href='<?php echo $thisfile?>?admin=<?php echo $admin?>'" value="zum Hauptmen�">
 <   ?
	   }
     
	   if($action == "MailTextSpeichern")
	   {
	   	$mailtext = $_POST['MailText'];
	   	$mailtext = stripslashes($mailtext);
	   	$hdl = fopen("mailtext.html", "w");
	   	fputs ($hdl, $mailtext);
	   	flock ($hdl, 3);
	   	fclose  ($hdl);
?>   
                    <br><br>gespeichert .....
	   	<br><br><input type="button" onclick="document.location.href='<?php echo $thisfile?>?admin=<?php echo $admin?>'" value="zum Hauptmen�">
 <   ?
     
	   }
     
     
#    Ende JayDee
     
     
	   if($action=="setTeam")
	   {
	   	?><table cellpadding="2" cellspacing="0">
	   	<tr>
	   		<td style="border-bottom:2px solid darkgray" align="center"><b>Vereinsname</b></td>
	   		<td style="border-bottom:2px solid darkgray" align="center"><b>Homepage des Vereins</b></td>
	   		<td style="border-bottom:2px solid darkgray" align="center">&nbsp;</td>
	   	</tr><?php
	   	for($i=1;$i<=($gamecount*2);$i++)
	   	{
	   		$result = mysqli_query($db, "select * from tblverein where lngIndex='$i'");
	   		echo mysqli_error();
	   		if(mysqli_num_rows($result)!=0)
	   		{
	   			$verein = mysql_result($result, 0, "strName");
	   			$url = mysql_result($result, 0, "strURL");
	   		}
	   		else
	   		{
	   			$verein="";
	   			$url = "";
	   		}
	   		?>
	   		<tr>
	   		<form action="<?php echo $thisfile?>" method="post"><input type="hidden" name="id" value="<?php echo $i?>">
	   		<input type="hidden" name="action" value="saveTeam">
	   		<td>
	   			<input type="text" name="strName" value="<?php echo $verein?>" style="width:200px">
	   		</td>
	   		<td>
	   			<input type="text" name="strURL" value="<?php echo $url?>" style="width:200px">
	   		</td>
	   		<td>
	   		<input type="submit" value="speichern">
	   		</td></form></tr>
	   		<?php
	   	}
	   	?>
	   	<tr><td colspan="2"><input type="button" onclick="document.location.href='<?php echo $thisfile?>?admin=<?php echo $admin?>'" value="zum Hauptmen�"></td></tr>
	   	</table>
	   	<?php
	   }
     
	   if($action=="saveTeam")
	   {
	   	$id = $_POST['id'];
	   	$strName = $_POST['strName'];
	   	$strURL = $_POST['strURL'];
	   	$result = mysqli_query($db, "select * from tblverein where lngIndex=$id");
	   	if(mysqli_num_rows($result)!=0)
	   		mysqli_query($db,"UPDATE tblverein SET strName='$strName', strURL='$strURL' WHERE lngIndex=$id");
	   	else
	   		mysqli_query( $db,"INSERT INTOtblverein(lngIndex,strName,strURL)Values($id,'$strName','$strURL')");
     
	   	echo "<script>document.location.href='$thisfile?action=setTeam'</script>";
	   }
     
	   if($action=="newDay")
	   {
	   	$newDay = $_GET['newday'];
	   	if(!isset($newDay))
	   		$newDay = $_POST['newday'];
     
	   	?>
	   	<script language="Javascript">
	   		function checkTeam(formid)
	   		{
	   			if(document.forms[formid].ver1.value == document.forms[formid].ver2.value)
	   				alert("Ein Team kann nicht gegen sich selbst spielen!!!");
	   			else
	   				document.forms[formid].submit();
	   		}
     
	   		function deletePair(id)
	   		{
	   			document.deleteForm.lngid.value = id;
	   			document.deleteForm.submit();
	   		}
	   	</script>
	   	<table width="600" cellpadding="2" cellspacing="0">
	   	<?php
	   	if(!isset($newDay))
	   	{
	   		$result = mysqli_query($db,"select intDay as day from tblinfo");
	   		if(mysqli_num_rows($result)==0)
	   		{
	   			mysqli_query($db,"INSERT INTO tblinfo (intDay) VALUES(1)");
	   			$newDay=1;
	   		}
	   		else
	   		{
	   			$newDay = mysql_result($result, 0, "day");
     
	   			if($newDay == "")
	   				$newDay=1;
     
	   		}
	   		$result = mysqli_query($db,"SELECT * FROM tblspieltag WHERE intTag=$newDay");
	   		$tmpcnt = mysqli_num_rows($result);
	   		while($tmpcnt == $gamecount)
	   		{
	   			$newDay++;
	   			$result = mysqli_query($db,"SELECT * FROM tblspieltag WHERE intTag=$newDay");
	   			$tmpcnt = mysqli_num_rows($result);
	   		}
	   	}
	   	else
	   	{
	   		$result = mysqli_query($db,"SELECT * FROM tblspieltag WHERE intTag=$newDay");
	   		$tmpcnt = mysqli_num_rows($result);
	   	}
     
/*   		if(mysqli_num_rows($result)==9)
	   	{
	   		$newDay=$newDay+1;
	   		echo "<tr><td>Spieltag:</td><td colspan=\"2\"><b>$newDay</b></td></tr><tr><td colspan=\"3\"><hr></td></tr>";
	   	}
	   	else*/
	   	{
	   		?>
	   		<tr>
	   			<td>Spieltag:</td>
	   			<td colspan="3"><b><?php echo $newDay?></b></td>
	   		</tr>
	   		<tr>
	   			<td colspan="4">
	   				<hr>
	   			</td>
	   		</tr>
	   		<?php
	   		$cnt = mysqli_num_rows($result);
	   		$cntold = $cnt;
	   		for($j=0; $j < $cnt; $j++)//while($cnt > -1)
	   		{
	   			$id1 = mysql_result($result, $j, "intVerein1");
	   			$hlp = mysqli_query($db,"select * from tblverein WHERE lngIndex=$id1");
	   			$verein = mysql_result($hlp, 0, "strName");
     
	   			$id2 = mysql_result($result, $j, "intVerein2");
	   			$hlp = mysqli_query($db,"select * from tblverein WHERE lngIndex=$id2");
	   			$verein2 = mysql_result($hlp, 0, "strName");
	   			?>
	   			<tr>
	   				<td width="290" align="center"><?php echo $verein?></td>
	   				<td>:</td>
	   				<td width="290" align="center"><?php echo $verein2?></td>
	   				<td>
	   					<input type="Button" value="l&ouml;schen" onclick="deletePair(<?php echo mysql_result($result, $j,"lngIndex")?>)" <?php if(mysql_result($result, $j, "intStatus") != 0){echo "disabled";}?>>
	   				</td>
	   			</tr>
	   			<?php //$cnt--;
	   		}
	   	}
	   	for($i=0;$i<($gamecount-$cntold);$i++)
	   	{
	   		?>
	   		<tr>
	   			<form name="<?php echo $i?>" action="<?php echo $thisfile?>">
	   			<input type="hidden" name="action" value="saveDay">
	   			<input type="hidden" name="newDay" value="<?php echo $newDay?>">
	   		<?php
	   		$result= mysqli_query($db,"select * from tblverein");
	   		$cnt = mysqli_num_rows($result);
	   		?>
	   		<td align="center">
	   			<select name="ver1" style="width:200"><?php
	   		for($j = 0; $j < $cnt; $j++)//while($cnt > -1)
	   		{
	   			$verein = mysql_result($result, $j, "strName");
	   			$id = mysql_result($result, $j, "lngIndex");
	   			$hlp = mysqli_query($db,"select * from tblspieltag WHERE (intVerein1=$id OR intVerein2=$id) AND intTag=$newDay");
	   			if(mysqli_num_rows($hlp)==0)
	   				echo "<option value=\"$id\">$verein</option>";
	   		}
	   		?></select>
	   		</td>
	   		<td>:</td>
	   		<td align="center">
	   			<select name="ver2" style="width:200">
	   		<?php $cnt = mysqli_num_rows($result);
	   		for($j = 0; $j < $cnt; $j++)
	   		{
	   			$verein = mysql_result($result, $j, "strName");
	   			$id = mysql_result($result, $j, "lngIndex");
	   			$hlp = mysqli_query($db,"select * from tblspieltag WHERE (intVerein1=$id OR intVerein2=$id) AND intTag=$newDay");
	   			if(mysqli_num_rows($hlp)==0)
	   				echo "<option value=\"$id\">$verein</option>";
	   			//$cnt--;
	   		}
	   		?></select>
	   		</td>
	   		<td>
	   			<input type="button" value="Speichern" onclick="checkTeam(<?php echo $i?>)">
	   		</td>
	   		</form>
	   		</tr>
	   	</table>
	   	<table>
	   	<?php
	   	}
	   	$result = mysqli_query($db,"select DISTINCT(intTag) FROM tblspieltag");
	   	$anzahl = mysqli_num_rows($result)-1;
	   	echo "<tr><td colspan='3'>";
	   	for($i=0; $i <= $anzahl; $i++)
	   	{
	   		$nDay = mysql_result($result, $i, "intTag");
	   		?>
	   			<input type="button" onClick="document.location.href='admin.php?action=newDay&newday=<?php echo $nDay?>'" value="<?php echo $nDay?>. Spieltag">
	   		<?php
	   	}
	   	?><input type="button" onClick="document.location.href='admin.php?action=newDay'" value="neuer Spieltag"></td>
	   	<form action="<?php echo $thisfile?>" method="get" name="deleteForm">
	   		<input type="Hidden" name="lngid">
	   		<input type="hidden" name="action" value="deleteDay">
	   		<input type="hidden" name="newDay" value="<?php echo $newDay?>">
	   	</form>
	   	<?php
	   	echo "</tr>";
	   	echo "<tr><td colspan=\"3\"><input type=\"button\" value=\"zum Hauptmen�\" onclick=\"document.location.href='$thisfile'\"></td></tr>";
	   	echo "</table>";
     
	   }
	   if($action=="saveDay")
	   {
	   	$ver1 = $_GET['ver1'];
	   	$ver2 = $_GET['ver2'];
	   	$newDay = $_GET['newDay'];
	   	if(mysqli_num_rows(mysqli_query($db,"SELECT * FROM tblspieltag WHERE ((intVerein1=$ver1 OR intVerein2=$ver1) OR (intVerein1=$ver2 OR intVerein2=$ver2)) AND intTag=$newDay"))==0)
	   	{
	   		mysqli_query($db,"$dbname", "INSERT INTO tblspieltag(intVerein1,intVerein2,intTag,intStatus) VALUES($ver1,$ver2,$newDay,0)");
	   		echo mysqli_error();
	   	}
     
	   	echo "<script>document.location.href='$thisfile?action=newDay';</script>";
	   }
     
	   if($action=="deleteDay")
	   {
	   	$id = $_GET['lngid'];
	   	$newDay = $_GET['newDay'];
	   	mysqli_query($db,"DELETE FROM tblwette WHERE intSpielid=$id");
	   	mysqli_query($db,"DELETE FROM tblspieltag WHERE lngIndex=$id");
	   	echo mysqli_error();
	   	echo "<script>document.location.href='$thisfile?action=newDay';</script>";
	   }
     
	   if($action == "makeClosed")
	   {
	   	echo "<table>";
	   	$result= mysqli_query($db,"select DISTINCT(intTag)as DAY from tblspieltag where intStatus=1");
	   	if(mysqli_num_rows($result)!=0)
	   	{
	   		//$disable="disabled";?>
	   		<tr>
	   			<td>
	   				<strong>Achtung: ein oder mehrere Spieltage wurden noch nicht ausgewertet!</strong>
	   			</td>
	   		</tr>
	   		<?php
	   	}
	   	//else
	   	$disable="";
	   	$result= mysqli_query($db,"select DISTINCT(intTag)as DAY from tblspieltag where intStatus=0 ORDER BY intTag DESC");
	   	$cnt = mysqli_num_rows($result)-1;
	   	while($cnt>-1)
	   	{
	   		echo "<tr>";
	   		for($i=0;$i<5 && $cnt > -1;$i++)
	   		{
	   			echo "<td>";
	   			$id = mysql_result($result, $cnt, "DAY");
	   			echo "<input type=\"button\" value=\"$id\" onclick=\"document.location.href='$thisfile?action=setClosed&id=$id'\" $disable>";
	   			echo "</td>";
	   			$cnt--;
	   		}
	   		echo "</tr>";
	   		//$cnt--;
	   	}
	   	echo "<tr><td colspan=\"5\"><input type=\"button\" value=\"zum Hauptmen�\" onclick=\"document.location.href='$thisfile?admin=<?php echo $admin?>'\"></td></tr>";
	   	echo "</table>";
	   }
	   if($action=="setClosed")
	   {
	   	$id = $_GET['id'];
	   	mysqli_query($db,"UPDATE tblspieltag SET intStatus=1 WHERE intTag=$id");
	   	echo "<script>document.location.href='$thisfile?action=makeClosed';</script>";
	   }
	   if($action=="reOpenSpieltag")
	   {
			$newSeason = new bl_season();
            debug ("Start" . $newDay );
            $newSeason->setScores();
	   }
	   if($action=="startNewSeason")
	   {
			$newSeason = new bl_season();
			echo ("Start" . $newDay );
			$newSeason->createTblSpieltageForSeason();
			RETURN;
	   }
     
	   if($action=="makeOpen2")
	   {
	   	?>
	   	<form method="post" action="admin.php?action=makeOpen2">
	   	<table>
	   		<tr>
	   			<td align="right">
	   				Automatisches Schlie&szlig;en am:
	   			</td>
	   			<td>
	   				<select name="aday">
	   					<?php
	   					for($i=1; $i <= 31; $i++)
	   					{
	   						$seltext = "";
	   						if($i == date("d"))
	   							$seltext = "selected";
	   						?>
	   						<option value="<?php echo $i?>" <?php echo $seltext?> ><?php echo $i?></option>
	   					<?php
	   					}?>
	   				</select>
	   				<select name="amonth">
	   					<?php
	   					for($i=1; $i <= 12; $i++)
	   					{
	   						$seltext = "";
	   						if($i == date("m"))
	   							$seltext = "selected";
	   						?>
	   						<option value="<?php echo $i?>" <?php echo $seltext?> ><?php echo $i?></option>
	   					<?php
	   					}?>
	   				</select>
     
	   				<select name="ayear">
	   					<option value="<?php echo date("Y")-1?>" <?php echo $seltext?> ><?php echo date("Y")-1?></option>
	   					<option selected value="<?php echo date("Y")?>" <?php echo $seltext?> ><?php echo date("Y")?></option>
	   					<option value="<?php echo date("Y")+1?>" <?php echo $seltext?> ><?php echo date("Y")+1?></option>
	   				</select>
	   			</td>
	   		</tr>
	   		<tr>
	   			<td align="right">um:</td>
	   			<td>
	   				<select name="ahour">
	   					<?php
	   					for($i=0; $i <= 24; $i++)
	   					{
	   						$seltext = "";
	   						if($i == date("h"))
	   							$seltext = "selected";
	   						?>
	   						<option value="<?php echo $i?>" <?php echo $seltext?> ><?php echo $i?></option>
	   					<?php
	   					}?>
	   				</select>
	   				<select name="aminute">
	   					<?php
	   					for($i=0; $i <= 59; $i++)
	   					{
	   						$seltext = "";
	   						if($i == date("i"))
	   							$seltext = "selected";
	   						?>
	   						<option value="<?php echo $i?>" <?php echo $seltext?> ><?php echo $i?></option>
	   					<?php
	   					}?>
	   				</select>
	   			</td>
	   		</tr>
	   		<tr>
	   			<td colspan="2" align="right">
	   				<input type="Submit" value="&Ouml;ffnen">
	   			</td>
	   		</tr>
	   	</table>
	   	</form>
	   	<?php
	   }
	   if($action=="makeOpen")
	   {
	   	mysqli_query($db,"UPDATE tblinfo set intDay=intDay+1, dtmClose=NULL");//, dtmClose=\"$datetime\"");
	   	echo "<script>document.location.href='$thisfile?admin=<?php echo $admin?>';</script>";
	   }
	   if($action=="reset")
	   {
	   	?>
	   	wollen sie wirklich l�schen???
	   	<form action="admin.php?action=reset2" method="post">
	   	<input type="submit" value="wirklich???">
	   	</form>
	   	<?php
	   }
	   if($action=="reset2")
	   {
	   	mysqli_query($db,"DELETE FROM tblwette");
	   	mysqli_query($db,"DELETE FROM tblspieltag");
	   	mysqli_query($db,"DELETE FROM tblsession");
	   	mysqli_query($db,"UPDATE tblgamer SET intPoint=0, intMoney=40");
	   	mysqli_query($db,"UPDATE tblverein SET intGoal=0, intGGoal=0, intPoint=0");
	   	mysqli_query($db,"UPDATE tblinfo SET intDay=1, dtmClose=NULL");
	   	echo "<script>alert(\"Alle Spieler haben jetzt 0 Punkte und 40 Spieleinheiten!\");document.location.href='$thisfile';</script>";
	   }
     
	   if($action=="editDay")
	   {
	   }
   }  
?>
<br>
<table width="500" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td align="right">
	<a href="http://www.script-fabrik.de">Copyright (c) 2003 by www.Script-Fabrik.de</a>
	</td>
</tr>
</table>
</body></html>


<?php

# Funktionen zur Einb�ndung des Mailtemplates
# JayDee 7/04
# post@jd-gerke.de

  function tplload($file)
  {
    if($fp = @fopen($file, "r"))
    {
      $tpl = fread($fp, filesize($file));
      fclose ($fp);
    }
    else $tpl = "Template konnte nicht ge�ffnet werden ($file)";
    return $tpl;
  }

  function tplprint($tpl, $repla)
  {
    foreach($repla as $key=>$elem)
    {
      $tpl = str_replace("{".$key."}", $elem, $tpl);
    }
    return $tpl;
  }

  ?>
