<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Unbenannt</title>
<link rel="stylesheet" type="text/css" href="../common/style.css">
</head>
<body>
<?php
	require("../common/include.php");
	$db=mysqli_connect("$address", "$dbuser", "$dbpasswd",'bl_wette');
	$SQL = "UPDATE tblgamer SET intpoint=0, intMoney=40";
	mysqli_query($db, $SQL);
	echo mysqli_error();

	$SQL = "UPDATE tblverein SET intGGoal=0, intGoal=0, intPoint=0";
	mysqli_query($db, $SQL);
	echo mysqli_error();

	$SQL = "UPDATE tblspieltag SET intStatus=1, intGoal1=0, intGoal2=0 WHERE intStatus=2";
	mysqli_query($db, $SQL);
	echo mysqli_error();
?>
<b>Bitte den erstenspieltag nochmals auswerten</b>
</body></html>