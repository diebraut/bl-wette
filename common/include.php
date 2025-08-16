<?php

error_reporting(E_ALL);

date_default_timezone_set("Europe/Berlin");

define('CONST_NUMBER_OF_MATCH_DAYS','34'); 
define('CONST_LIGA','bl1'); 
define('CONST_LIGA_SEASON','2024'); 

define ('DB_NAME','bl_wette');
define ('HOST','localhost');
define ('DB_USER','root');
define ('DB_PASSWD','castell');

$dbname=DB_NAME;
$address=HOST;
$dbuser=DB_USER;
$dbpasswd=DB_PASSWD;

$gamecount="9"; 
$numberOfMatchDays="34";
$pointtendenz="1";
$pointdif="1";
$pointhomegoal="1";
$pointguestgoal="1";
$email="";
$hp="";


if (!function_exists('mysql_result')) {
	function mysql_result($result, $number, $field=0) {
		mysqli_data_seek($result, $number);
		$row = mysqli_fetch_array($result);
		return $row[$field];
	}
}	 		

function debug ($txt) {
        
   echo $txt ?> </br> <?php
} 

function isWebsiteOnline($url) {

    $ch = curl_init(); // Startet eine cURL-Session
    
    if (!$ch) { die("Konnte keine cURL-Session starten. Ist auf deinem Server cURL installiert/aktiviert?"); } // falls die cURL-Session nicht gestartet werden konnte
    
    // curl_setopt setzt im Folgenden mehrere Optionen für den cURL-Transfer
    
    curl_setopt ($ch, CURLOPT_URL, $url ); // die aufzurufende URL

    // einige seiten setzen einen gesetzten agent voraus
    $agent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; de; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13";
    curl_setopt ($ch, CURLOPT_USERAGENT, $agent); // der zu benutzende User Agent, Erklaerung oben

    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true); // gibt den Transfer als String zurueck, gibt ihn somit nicht direkt aus
    curl_setopt ($ch, CURLOPT_VERBOSE, false); // verhindert, dass ausfuehrliche Informationen ausgegeben werden; uns reicht der HTTP-Statuscode
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 10); // Script hat 5 Sekunden Zeit, die Verbindung aufzubauen
    curl_setopt ($ch, CURLOPT_TIMEOUT, 10); // Script hat 5 Sekunden Zeit um alle Aufgaben auszufuehren
    curl_exec($ch); // Fuehrt die cURL-Session aus
    
    $info = curl_getinfo($ch); // Holt sich Informationen des Transfers
    
    $http_statuscode = $info['http_code']; // Waehlt den zuletzt empfangenen HTTP-Statuscode und speichert ihn
    curl_close($ch); // Beendet die cURL-Session
    
    //debug ("stuscode =" . $http_statuscode );
    if ($http_statuscode >= 200 AND $http_statuscode < 400) { // ggf. kann man die Grenze auch auf < 300 setzen, sofern Weiterleitung als "offline" gelten    
        return true;    
    } else {    
        return false;
    
    }
}

/* Liefert eine nach punkte geordnete liste aller mitspieler) */
 
function getUserResultArray() {

     $dbLink = mysqli_connect(HOST, DB_USER,DB_PASSWD,DB_NAME);
	 
     $SQL = "SELECT  strAlias as Gamer ,LngIndex as GamerId, intPoint as gamerScore  from tblgamer  ORDER BY intPoint DESC";
     $resultGamer = mysqli_query($dbLink,$SQL);
     $anzahlGamer = mysqli_num_rows($resultGamer);
     for ($y = 0; $y < $anzahlGamer; $y++)
     {
         $gamerId   = mysql_result($resultGamer,$y, "GamerId");
         $gamerName = mysql_result($resultGamer,$y, "Gamer");

         $SQL_CREA = "SELECT SUM( ".
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
                     "        W.`intUserid` = ${gamerId} ";
         
         $res = mysqli_query($dbLink,$SQL_CREA);
                   
         $res_array[ ] = array($gamerId,$gamerName,mysql_result($res,0,"POINTS"));
     }
     RETURN $res_array;
}

class bl_season {

    static $options = array('encoding'           => 'UTF-8',
                       'connection_timeout' => 5,
                       'exceptions'         => 1,
                       );
        
    static $location = 'https://api.openligadb.de/webservices/sportsdata.asmx';

    static $db_liga_client;
    static $db_liga_params;
    
    static $isOpenDBAvailable;

    /* id Verein aus OpenLigaDB zu eigener ID */
    
    static $ArrIDVerein = array( 

                       array('91','1'),    // Eintracht Frankfurt
                       array('40' ,'12'),  // Bayern München
                       array('95' ,'5'),   // FC Augsburg
                       array('112','4'),   // SC Freiburg                      
                       array('129','11'),  // VFL Bochum 
                       array('81','15'),   // 1. FC Mainz 05                       
                       array('87' ,'3'),   // Borussia Mönchengladbach
                       array('175','14'),  // Hoffenheim                       
                       array('80','8'),    // 1. FC Union Berlin
                       array('98','2'),    // Pauli                   
                       array('134','17'),  // Werder Bremen
                       array('131','65'),  // Wolfsburg                       
                       array('7' ,'7'),    // Borussia Dortmund
                       array('6','10'),    // Bayer Leverkusen                       
                       array('16' ,'6'),   // VFB Stuttgart
                       array('1635','18'), // RB Leipzig                      
                       array('104','16'),  // Kiel
                       array('199','9'));  // 1 FC Heidenheim 1846 
 
    static function lookupVereinsID ( $idOpenDB )
    {
       foreach (self::$ArrIDVerein as $item)
       {
          if ( $idOpenDB == $item[0] )
          {
             return $item[1]; 
          }
       }
       return '1111';
    }

    function callSoapFct($methodName, $client, $param)
    {

      try
      { 
         $response = call_user_func(array(&$client, $methodName), $param ); 
      }
      catch (SoapFault $e)
      {
          die($e->faultcode . ': ' . $e->faultstring);
      }
      catch (Exception $e)
      {
          die($e->getCode() . ': ' . $e->getMessage());
      }                
      return $response;
    }

    function __construct() {
        
       try
       {                            
          /* check if the service is available */
          if (isWebsiteOnline(self::$location)) {            
                self::$isOpenDBAvailable = 1;
           } else{            
              self::$isOpenDBAvailable = 0;   
                echo "Service (OpenLigaDB ist offline. Auswertung im Moment nicht möglich ";
                RETURN;            
          }            
          self::$isOpenDBAvailable = 1;
          self::$db_liga_client = new SoapClient(self::$location, self::$options);
          self::$db_liga_params = new stdClass;
       }
       catch (SoapFault $e)
       {
          die($e->faultcode . ': ' . $e->faultstring);
       }
       catch (Exception $e)
       {
          die($e->getCode() . ': ' . $e->getMessage());
       }
    }
    
    
    /* erzeugt alle Eintraege (spiele einer Saison) */
    function createTblSpieltageForSeason ( ) {
         
      $db = mysqli_connect(HOST, DB_USER,DB_PASSWD,DB_NAME);		 
      if (self::$isOpenDBAvailable == 0 ) {
           RETURN;
      }
        
      /* hole alle spieldaten der akt. saison */
      self::$db_liga_params->leagueShortcut = CONST_LIGA;
      self::$db_liga_params->leagueSaison   = CONST_LIGA_SEASON;
      $response = self::callSoapFct ( 'GetMatchdataByLeagueSaison',
                                      self::$db_liga_client,
                                      self::$db_liga_params
                                    );
      foreach ($response->GetMatchdataByLeagueSaisonResult->Matchdata as $item)
      {        
        $id1 = self::lookupVereinsID ($item->idTeam1);
        $id2 = self::lookupVereinsID ($item->idTeam2);
                
        $query = "INSERT INTO ";
        $query.= "tblspieltag (intVerein1,intVerein2,intGoal1,intGoal2,intTag,intStatus,intMatchIdFromOpenLigaDB) ";
        $query.= "Values ($id1,$id2,0,0,$item->groupOrderID,0,$item->matchID) ";
        $result = mysqli_query($db,$query);          
      }        
   }

   function restoreResults ( ) 
   {
	  $db = mysqli_connect(HOST, DB_USER,DB_PASSWD,DB_NAME); 
      $result = mysqli_query($db,"select * from tblspieltag where intStatus=2 ");
      $anzahl = mysqli_num_rows($result) - 1;

      while($anzahl > -1)
      {
         // get the matchdata from OpenLigaDB
         $matchID =  mysql_result($result, $anzahl, "intMatchIdFromOpenLigaDB");
         $primKey =  mysql_result($result, $anzahl, "lngIndex");
         
         if ( $matchID > 0 )
         {   
             self::$db_liga_params->MatchID   = $matchID;             
             $response = self::callSoapFct ( 'GetMatchByMatchID',
                                         self::$db_liga_client,
                                         self::$db_liga_params);
             
             
             if ( $response->GetMatchByMatchIDResult->matchIsFinished )
             {
				foreach ($response->GetMatchByMatchIDResult->matchResults->matchResult as $matchResult){
				  if ($matchResult->resultTypeId === 2) {	
					$goal1 = $matchResult->pointsTeam1;
					$goal2 = $matchResult->pointsTeam2;
					echo "udate -";
					//update tblspieltag
					$query = "UPDATE ";
					$query.= "tblspieltag ";
					$query.= "SET intStatus = '2', ";
					$query.=     "intGoal1  = $goal1, ";
					$query.=     "intGoal2  = $goal2 ";
				  }
                }  
                $query.= "WHERE lngIndex = '$primKey' ";  
                   
                mysqli_query($db,$query);
             }
           }
           $anzahl--;
      }
      /* aktualisieren der punkte in tabelle tblgamer*/  
	  
      $userPoints = getUserResultArray();
      
      foreach ($userPoints as $item)
      {
      	$user   = $item[0];
      	$points = $item[2];
      	mysqli_query($db, "UPDATE tblgamer SET intPoint=$points, intMoney=0 where lngIndex=$user");
      } 
	  	  
      return;
	   
   }

    
   /* wertet alle spiele des aktuellen Spieltages aus und   */
   /* erhöht den Spieltag um 1. Die Anfangszeit des ersten  */
   /* Spieles wird ermittelt und als Wettschluss datum/zeit */
   /* festgelegt. Ausserdem wird das Ende Datum(Zeit) des   */
   /* letzten Spieles gespeichert (ab dann wird wieder zum */
   /* nächsten Spieltag geschaltet                            */
    
   function switchToNextSpieltag ( $currentDay ) 
   {
      IF ( $currentDay == CONST_NUMBER_OF_MATCH_DAYS )
      {
         /* Nothing to do */
         echo "season is finished!!";
         RETURN 0;
      }
	  $db = mysqli_connect(HOST, DB_USER,DB_PASSWD,DB_NAME);		 
	  
      $currentDate = date_create_from_format('Y-m-d H:i:s', date('Y-m-d H:i:s'));
      if (self::$isOpenDBAvailable == 0 ) {
         /* wenn der dienst nicht verfügbar ist wird  */
         /* das Spieltagsendedatum (dtmEndOfMatchDay) */
         /* auf aktuelles Datum + 6 stunden gesetzt   */
         /* damit nicht ständig geprüft wird          */
              
         /*update table => tblinfo */
         /* at 6 hours to delay the evaluation */
         $currentDate->add(new DateInterval('PT6H'));
           
         $query = "UPDATE ";
         $query.= "tblinfo ";
         $query.= "SET  dtmEndOfMatchDay = '" . date_format($currentDate,'Y-m-d H:i:s') . "' ";
         
         $result = mysqli_query($db,$query);  
         RETURN 0;
      }
      /* get the current group from openeligadb */
      
      self::$db_liga_params->leagueShortcut = CONST_LIGA;
      $response = self::callSoapFct ( 'GetCurrentGroup',
                                       self::$db_liga_client,
                                       self::$db_liga_params);
      $currentGroupId = $response->GetCurrentGroupResult->groupOrderID;
      echo "\currentDay = " . $currentDay;
      $xy = $currentGroupId;
      do 
      {              
         echo "currentGroupId = " . $currentGroupId;
         
         self::$db_liga_params->groupOrderID   = $currentGroupId;
         self::$db_liga_params->leagueShortcut = CONST_LIGA;
         self::$db_liga_params->leagueSaison   = CONST_LIGA_SEASON;
         
         $response = self::callSoapFct ( 'GetMatchdataByGroupLeagueSaison',
                                   self::$db_liga_client,
                                   self::$db_liga_params);
         /* get the date of the first match and the last match */

         $dtFirstMatchDate = "2100-01-01T00:00:00"; 
         $dtLastMatchDate  = "1900-01-01T00:00:00"; 

         //ermittung des Datum/Zeit vom ersten und letzten Spieles des Spieltages            
 	     foreach ($response->GetMatchdataByGroupLeagueSaisonResult as $matches){
	        foreach ($matches as $match)  {
                //echo "\n match=". $match->matchID . " finished=" . $match->matchIsFinished;
                //if ($currentDay == $currentGroupId && $match->matchIsFinished === TRUE) echo "\n match=". $match->matchID . " finished ";
				if ($currentDay != $currentGroupId || ($currentDay == $currentGroupId && $match->matchIsFinished)) { 
					if ( $dtFirstMatchDate > $match->matchDateTime )
					{
						$dtFirstMatchDate = $match->matchDateTime;
					}    
					if ( $dtLastMatchDate < $match->matchDateTime )
					{
						$dtLastMatchDate = $match->matchDateTime;
					}
				}	
            }      
         } 		 
         $dateFirst = date_create_from_format('Y-m-d*H:i:s', $dtFirstMatchDate);      
         $dateLast = date_create_from_format('Y-m-d*H:i:s', $dtLastMatchDate);
         $str_dtLast  = date_format($dateLast,'Y-m-d H:i:s');  
         
         /* falls letztes spiel des spieltages in der vergangenheit liegt */
         /* muss der aktuelle spieltag um eins erhöht werden              */

         $nextTry = 0;
         if ( $str_dtLast < date('Y-m-d H:i:s') )
         { 
            $currentGroupId++;
			if ($currentGroupId < CONST_NUMBER_OF_MATCH_DAYS) 
	        {
				$nextTry = 1;
			}
			else {
				$nextTry = 0;
			}
         }
      } while ( $nextTry == 1 );
            
      /*update table => tblinfo */

      /* at 2 hours to the start date of the last matchtime */
      $dateLast->add(new DateInterval('PT2H'));
        
      $query = "UPDATE ";
      $query.= "tblinfo ";
      $query.= "SET intDay = '$currentGroupId', ";
      $query.=     "dtmClose = '" . date_format($dateFirst,'Y-m-d H:i:s') . "', ";
      $query.=     "dtmEndOfMatchDay = '" . date_format($dateLast,'Y-m-d H:i:s') . "' ";
      
      $result = mysqli_query($db,$query);  

      // hole alle auszuwertenden matches. (alle des aktuellen spieltages und alle 
      // ev. noch nicht ausgewerteten)
            
      $fromDay = $currentDay;
      $toDay   = $currentGroupId - 1;
      $result = mysqli_query($db,"select * from tblspieltag where intStatus=1 OR intTag BETWEEN $fromDay AND $toDay ");
      $anzahl = mysqli_num_rows($result) - 1;

      while($anzahl > -1)
      {
         // get the matchdata from OpenLigaDB
         $matchID =  mysql_result($result, $anzahl, "intMatchIdFromOpenLigaDB");
         $intStat =  mysql_result($result, $anzahl, "intStatus");
         $primKey =  mysql_result($result, $anzahl, "lngIndex");
         $verein1 =  mysql_result($result, $anzahl, "intVerein1");
         $verein2 =  mysql_result($result, $anzahl, "intVerein2");
         
         if ( $matchID > 0 )
         {   
             self::$db_liga_params->MatchID   = $matchID;
             
             $response = self::callSoapFct ( 'GetMatchByMatchID',
                                         self::$db_liga_client,
                                         self::$db_liga_params);
             
             
             if ( $response->GetMatchByMatchIDResult->matchIsFinished )
             {

		 foreach ($response->GetMatchByMatchIDResult->matchResults->matchResult as $matchResult){
				  if ($matchResult->resultName === 'Endergebnis') {	
					$goal1 = $matchResult->pointsTeam1;
					$goal2 = $matchResult->pointsTeam2;
				  }
                }  
                if ( $intStat != 2 )
                {     
                   //update tblspieltag
                   $query = "UPDATE ";
                   $query.= "tblspieltag ";
                   $query.= "SET intStatus = '2', ";
                   $query.=     "intGoal1  = $goal1, ";
                   $query.=     "intGoal2  = $goal2 ";
                   $query.= "WHERE lngIndex = '$primKey' ";  
                   
                   mysqli_query($db,$query);
                   
                   /* aktualisiere die Punkte von verein1 in tblVerein*/
                   
                   $sql_str = "select sum(if((intVerein1 = $verein1 AND (intGoal1 > intGoal2)) OR (intVerein2 = $verein1 AND (intGoal2 > intGoal1)),3,if(intGoal1 = intGoal2,1,0))) AS POINTS, ".
                              "       sum(if(intVerein1  = $verein1, intGoal1,intGoal2)) AS GOALS1, ".
                              "       sum(if(intVerein2  = $verein1, intGoal1,intGoal2)) AS GOALS2 ". 
                              "from tblspieltag ".
                              "where intStatus = 2 and ( intVerein1 = $verein1 OR intVerein2 = $verein1)";
                   
                   $resPts = mysqli_query($db,$sql_str);
                   
                   $points = mysql_result($resPts,0,"POINTS");
                   $goals1 = mysql_result($resPts,0,"GOALS1");
                   $goals2 = mysql_result($resPts,0,"GOALS2");
                   
                   mysqli_query( $db,"UPDATE tblverein SET intGoal  = $goals1, ".
                 	 	                     "intGGoal = $goals2, ".
                 	 	                     "intPoint = $points ".
                 	 	 "where lngIndex=$verein1");
                   
                   /* aktualisiere die Punkte von verein2 in tblVerein*/
                   
                   $sql_str = "select sum(if((intVerein1 = $verein2 AND (intGoal1 > intGoal2)) OR (intVerein2 = $verein2 AND (intGoal2 > intGoal1)),3,if(intGoal1 = intGoal2,1,0))) AS POINTS, ".
                              "       sum(if(intVerein1  = $verein2, intGoal1,intGoal2)) AS GOALS1, ".
                              "       sum(if(intVerein2  = $verein2, intGoal1,intGoal2)) AS GOALS2 ". 
                              "from tblspieltag ".
                              "where intStatus = 2 and ( intVerein1 = $verein2 OR intVerein2 = $verein2)";
                   
                   $resPts = mysqli_query($db,$sql_str);
                   $points = mysql_result($resPts,0,"POINTS");
                   $goals1 = mysql_result($resPts,0,"GOALS1");
                   $goals2 = mysql_result($resPts,0,"GOALS2");
                   
                   mysqli_query($db, "UPDATE tblverein SET intGoal  = $goals1, ".
                 		                     "intGGoal = $goals2, ".
                 		                     "intPoint = $points ".
                 	        "where lngIndex=$verein2");
                }                  
             }
           }
           $anzahl--;
         }
         /* aktualisieren der punkte in tabelle tblgamer*/  
         $userPoints = getUserResultArray();

         foreach ($userPoints as $item)
         {
         	$user   = $item[0];
         	$points = $item[2];
       		mysqli_query($db, "UPDATE tblgamer SET intPoint=$points, intMoney=0 where lngIndex=$user");
         }                                   
         return $currentGroupId;
     } 
}

?>
