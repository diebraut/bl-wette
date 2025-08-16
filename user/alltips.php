<?php
header('Content-type: text/html; charset=utf-8');

if(!isset($_POST))
        $_POST  = $HTTP_POST_VARS;
if(!isset($_GET))
        $_GET  = $HTTP_GET_VARS;
require("../common/include.php");

$db=mysqli_connect("$address", "$dbuser", "$dbpasswd",'bl_wette');
mysqli_query($db,"SET NAMES utf8");

$user = $_GET['user'];
$day  = $_GET['day'];
  
$sqlRes = mysqli_query($db, "SELECT intStatus FROM tblspieltag WHERE intTag=$day");
$status = mysql_result($sqlRes, 0, "intStatus");

$call_allowed = 0;
if (($status >= 1) OR ($user == "roland"))
{
   $call_allowed = 1;
      
   $SQL = "SELECT v1.strName as Team1, v2.strName as Team2, s.lngIndex as SpielId ".
                        "from tblspieltag as s ".
                   "LEFT JOIN tblverein as v1 ON (v1.lngIndex=s.intVerein1)".
                        "LEFT JOIN tblverein as v2 ON (v2.lngIndex=s.intVerein2)".
                        " WHERE  s.intTag=$day ".
                        " GROUP BY s.lngIndex ORDER BY s.lngIndex DESC";
   
   $result = mysqli_query($db, $SQL);
   $anzahl = mysqli_num_rows($result);
   
   $SQL = "SELECT  SUBSTRING(concat(strAlias,\"........\"),1,8) as Gamer,LngIndex as GamerId, intPoint as gamerScore  from tblgamer  ORDER BY intPoint DESC";
   $resultGamer = mysqli_query($db, $SQL);
   $anzahlGamer = mysqli_num_rows($resultGamer);     

   /* hole liste aller spieler mit scores  */  
   $userPoints = getUserResultArray();
}       


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//DE">
<html lang="de">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"> 

<?php
if ( $call_allowed == 1 )
{
?>
        
    <head>
        <link rel="stylesheet" type="text/css" href="../common/style.css">
        <title>Usertip</title>
    </head>
    <body leftmargin="2" rightmargin="2" topmargin="2" bottommargin="0" style="border:none">
    <table  border="0" width="100%" cellpadding="0" cellspacing="0">
        <tr>
                <td align="center" height="25" class="title">
                        Die Tipps von <strong>allen Spielern</strong> vom <?php echo $day?> Spieltag
                </td>
        </tr>
    </table>
    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="tablestyle">
                        <tr>
                                <td colspan="3" align="center" class="head"><strong>Paarungen</strong></td>
                                <td align="center" class="head" ></td>
                                <?php
                                for ($i = 0; $i < $anzahlGamer; $i++)
                                {
                                   $gamer = mysql_result($resultGamer,$i, "Gamer");
                                   ?>
                                   <td align="center" colspan="3" class="head" width="5%" ><strong><?php echo $gamer?></strong></td>
                                         <td align="center" class="head" ></td>
                                   <?php
                                }   
                                ?>
                        </tr>
                        <tr>
                                <td colspan="3" align="center" class="head"><strong></strong></td>
                                <td align="center" class="head" ></td>
                                <?php
                                for ($i = 0; $i < $anzahlGamer; $i++)
                                {
                                	
                                   $gamerId = mysql_result($resultGamer,$i, "GamerId");
                                   foreach ($userPoints as $item)
                                   {
                                       if ( $item[0] == $gamerId )
                                       {
                                   	 $points = $item[2];
                                   	 break;
       	                               }
                                   }                          
                                   $txtHlp = "(".$points.")";
                                   ?>
                                   <td align="center" colspan="3" class="head" width="5%" ><strong><?php echo $txtHlp?></strong></td>
                                         <td align="center" class="head" ></td>
                                   <?php
                                }   
                                ?>
                
                        </tr>
                        <tr>
                                <td colspan="3" align="center" class="head"><img color="black" src="pic/horzstr.gif" height = "1" width = "100%"></td>
                                <td align="center" class="head" ></td>
                                <?php
                                for ($i = 0; $i < $anzahlGamer; $i++)
                                {
                                   ?>
                                   <td align="center" colspan="3" class="head" width="5%" ><img color="black" src="pic/horzstr.gif" height = "1" width = "100%"></td>
                                         <td align="center" class="head" ></td>
                                   <?php
                                }   
                                ?>
                        </tr><?php
                        //while($anzahl > -1)
                        for($i = 0; $i < $anzahl; $i++)
                        {
                                $ver1 = mysql_result($result,$i, "Team1");
                                $ver2 = mysql_result($result,$i, "Team2");
                                //$ver1 = utf8_encode($ver1);
                                //$ver2 = utf8_encode($ver2);
                                if($i % 2)
                                        $classname = "firstline";
                                ?>
                                  <tr class="<?php echo $classname?>">
                                        <td align="center" class="<?php echo $classname?>" width="15%"><?php echo $ver1?></td>
                                        <td align="center" class="<?php echo $classname?>">:</td>
                                        <td align="center" class="<?php echo $classname?>" width="15%"><?php echo  $ver2?></td>
                                        <td align="center" class="rowdelimiter"><img color="black" src="pic/senkstr.gif" height = "20" width = "1"></td>
                                   <?php
                                   for ($y = 0; $y < $anzahlGamer; $y++)
                                   {
                                      $gamerId = mysql_result($resultGamer,$y, "GamerId");
                                      $spielId = mysql_result($result,$i, "SpielId");
                                      $SQL = "SELECT intGoal1,intGoal2 from tblwette where intUserid = $gamerId and intSpielid = $spielId";
                                      $resultWette = mysqli_query($db,$SQL);
                                      $num_rows = mysqli_num_rows($resultWette);
                                      if ($num_rows > 0)
                                      {
                                          $goal1 = mysql_result($resultWette,0, "intGoal1");
                                          $goal2 = mysql_result($resultWette,0, "intGoal2");
                                      }
                                      else 
                                      {
                                          $goal1 = '-';
                                          $goal2 = '-';
                                      }
                                      $classname = "secondcolumn";
                                      if($y % 2)
                                         $classname = "firstcolumn";
                                      ?>
                                      <td align="center" class="<?php echo $classname?>"><?php echo $goal1?></td>
                                      <td align="center" class="<?php echo $classname?>">:</td>
                                      <td align="center" class="<?php echo $classname?>"><?php echo $goal2?></td>
                                      <td align="center" class="rowdelimiter"><img color="black" src="pic/senkstr.gif" height = "20" width="1" ></td>
                                      <?php
                                    }
                                 ?> 
                                </tr>
                                <tr class="<?php echo $classname?>">
                                <?php
                                $anzCols = ($anzahlGamer + 1) * 4   
                                ?>
                                <td colspan="<?php echo $anzCols?>" align="center" class="head"><img height="1" src="pic/horzstr_1.gif" width = "100%"></td>
                                </tr><?php
                        }
                        ?>
        </table>
        <table cellpadding="0" cellspacing="0" border="0" width="100%" height="50">
                <tr>
                        <td align="right" height="30"></td>
                </tr>
        </table>
        <table cellpadding="0" cellspacing="0" border="0" width="100%" height="30">
                <tr>
                        <td align="left" height="30"><img src="pic/tippsdrucken.gif" width="120" height="20" alt="" border="0" onclick="javascript:window.print()" style="cursor:pointer"></td>
                </tr>
        </table>
                
    </body>
<?php
}
else
{
?>
    <head>
    <link rel="stylesheet" type="text/css" href="../common/style.css">
    <title>Nicht erlaubt solange noch getippt werden kann!!!!!</title>
    </head> 
<?php
}
?>
</html>
