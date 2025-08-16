<html>
<head>
<link rel="stylesheet" type="text/css" href="../common/style.css">
<?php   

//error_reporting(E_ERROR | E_PARSE);
error_reporting(E_ALL);

ini_set('display_errors', 0);

require("../common/include.php");

if(!isset($_POST))
        $HTTP_POST_VARS  = $_POST;
if(!isset($_GET))
        $HTTP_GET_VARS  = $_GET;
$admin = $_GET['admin'];

if ($admin == "roland")
{       
?>      
   <title>Bundesliga-Tippspiel Adminbereich</title>
<?php 
}
else
{
?>
   <title>Nur f�r Administrator erlaubt!!!</title>
<?php 
}
?>

</head>
<body>

<?php

  include_once ("../common/include.php"); 
  if ($admin == "roland")
  {        
           $action = $_GET["action"];
                
           if(!isset($action))
                $action = $_POST["action"];
     
           $parent="admin.php";
           $thisfile="admin2.php";
                
           $db=mysqli_connect("$address", "$dbuser", "$dbpasswd",'bl_wette');
           
           $result = mysqli_query($db, "select intDay as day from tblinfo");
           $actDay = mysql_result($result, 0, "day");
     
           if($actDay=="")
           {
                echo "Es wurde kein Spieltag geschlossen! Somit kann es passiert sein, da� einige geschummelt haben!";
                $actDay=1;
           }
     
           $SQL = "SELECT LngIndex as GamerId from tblgamer  ORDER BY intPoint DESC";
           $resultGamer = mysqli_query($db, $SQL);
           $anzahlGamer = mysqli_num_rows($resultGamer);
           
           if(isset($action))
           {
               $goal1 = $_POST['goal1'];
               $goal2 = $_POST['goal2'];
               $gameTag = $_POST['gameTag'];
               $game = $_POST['game'];
               $point = 0;
               
               mysqli_query($db, "UPDATE tblspieltag Set intStatus=2, intGoal1=$goal1, intGoal2=$goal2 where lngIndex=$game");

               if(($goal1 - $goal2)>0)
                       $point = 3;
               elseif (($goal1 - $goal2) == 0)
                       $point = 1;
               $res = mysqli_query($db, "SELECT * FROM tblspieltag WHERE lngIndex=$game");
               $intVerein1 = mysql_result($res, 0, "intVerein1");
               $intVerein2 = mysql_result($res, 0, "intVerein2");
               
               $sql1 = "UPDATE tblverein SET intGoal=intGoal+$goal1, intGGoal=intGGoal+$goal2, intPoint=intPoint + $point WHERE lngIndex=$intVerein1";
               
               $point=0;
               if(($goal2 - $goal1)>0)
                       $point = 3;
               elseif (($goal1 - $goal2) == 0)
                       $point = 1;
               $sql2 = "UPDATE tblverein SET intGoal=intGoal+$goal2, intGGoal=intGGoal+$goal1, intPoint=intPoint + $point WHERE lngIndex=$intVerein2";
               mysqli_query($db, $sql1);
               mysqli_query($db, $sql2);
               
               /* aktualisieren der punkte in tabelle tblgamer*/  
               $userPoints = getUserResultArray();
               foreach ($userPoints as $item)
               {
               	   $user   = $item[0];
               	   $points = $item[2];
       		   mysqli_query($db, "UPDATE tblgamer SET intPoint=$points, intMoney=0 where lngIndex=$user");
               }                          
           }
                
           $result = mysqli_query($db, "select * from tblspieltag where intStatus=1 OR intTag=$actDay ORDER BY intTag DESC");
           $anzahl = mysqli_num_rows($result)-1;
           ?>
                <table cellpadding="2" cellspacing="0">
           <?php 
           while($anzahl > -1)
           {
                $tag = mysql_result($result, $anzahl, "intTag");
                $id = mysql_result($result, $anzahl, "lngIndex");
                $verx1 = mysql_result($result, $anzahl, "intVerein1");
                $verx2 = mysql_result($result, $anzahl, "intVerein2");
     
                $scnd = mysqli_query($db, "select * from tblverein where lngIndex=$verx1");
                $ver1 = mysql_result($scnd, 0, "strName");
     
                $scnd = mysqli_query($db, "select * from tblverein where lngIndex=$verx2");
                $ver2 = mysql_result($scnd, 0, "strName");
                $stat = mysql_result($result, $anzahl, "intStatus");
                $bgcolor = "";
                $style = "";
                if($tag < $actDay)
                {
                        $bgcolor="bgcolor=\"red\"";
                        $style = "style=\"color:white;font-weight:bold\"";
                }
                if($tag != $oldTag)
                {?>
                <tr bgcolor="#808080">
                        <td colspan="6" style="color:white">
                                <strong>
                                Spieltag <?php  actDay?>
                                </strong>
                        </td>
                </tr>
                <?php }
                $oldTag = $tag;
                if($stat == 2)
                {
                        $goal1 = mysql_result($result, $anzahl, "intGoal1");
                        $goal2 = mysql_result($result, $anzahl, "intGoal2");
                        ?>
                                <tr <?php echo $bgcolor?>>
                                        <td align="center">
                                                <?php echo $ver1?>
                                        </td>
                                        <td align="center">
                                                <?php echo $goal1?>
                                        </td>
                                        <td align="center">
                                                :
                                        </td>
                                        <td align="center">
                                                <?php echo $goal2?>
                                        </td>
                                        <td align="center">
                                                <?php echo $ver2?>
                                        </td>
                                        <td>&nbsp;
                                        </td>
                                </tr>
                        <?php 
                }
                else
                {
                        ?>
                                <form action="<?php echo $thisfile?>?admin=<?php echo $admin?>" method="post">
                                <input type="hidden" name="action" value="wetten">
                                <input type="hidden" name="game" value="<?php echo $id?>">
                                <input type="hidden" name="gameTag" value="<?php echo $tag?>">
                                <tr <?php echo $bgcolor?>>
                                        <td align="center" <?php echo $style?>>
                                                <?php echo $ver1?>
                                        </td>
                                        <td <?php echo $style?>>
                                                <input type="text" name="goal1" size="3">
                                        </td>
                                        <td <?php echo $style?>>
                                                :
                                        </td>
                                        <td <?php echo $style?>>
                                                <input type="text" name="goal2" size="3">
                                        </td>
                                        <td align="center" <?php echo $style?>>
                                                <?php echo $ver2?>
                                        </td>
                                        <td <?php echo $style?>>
                                                <input type="submit" value="set" <?php echo $stat?>>
                                        </td>
                                </tr>
                                </form>
                        <?php 
                }
                $anzahl--;
           }
           ?>
           <tr><td colspan="5"><input type="button" value="zum Hauptmen�" onclick="document.location.href='<?php echo $parent?>?admin=<?php echo $admin?>'"></td></tr>
           </table>
           <?php  
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