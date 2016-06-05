<?php

//giant header plus body start tag
echo "<html><head><title>1v1 Prediction</title><link rel=\"stylesheet\" href=\"css/jquery-pie-loader.css\"><script src=\"js/jquery-1.12.3.min.js\"></script><script src=\"js/jQueryRotate.js\"></script><script src=\"js/jquery-pie-loader.js\"></script></head><body>";

//session for error handling, best i could do :(
session_start();


//also, setup the white overlay here
echo "<div class=\"faderCenterImg\"></div>";
echo "<div class=\"faderCenterImgRight\"></div>";
echo "<div class=\"faderCenterText\"></div>";

$_SESSION['error'] = '';

function redirect($url)
{
    $string = '<script type="text/javascript">';
    $string .= 'window.location = "' . $url . '"';
    $string .= '</script>';

    echo $string;
}

function summInfo($summoner, $server) {

  $summoner_encoded = rawurlencode($summoner);
  $summoner_lower = strtolower($summoner_enc);
  $curl = curl_init('https://' . $server . '.api.pvp.net/api/lol/' . $server . '/v1.4/summoner/by-name/' . $summoner . '?api_key=<API_KEY_HERE>');
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  $result = curl_exec($curl);
  curl_close($curl);
  return $result;
  
}

function summoner_info_array_name($summoner) {

  $summoner_lower = mb_strtolower($summoner, 'UTF-8');
  $summoner_nospaces = str_replace(' ', '', $summoner_lower);
  return $summoner_nospaces;

}

function summChampData($summId, $server) {

  if($server == "lan") {
    
    $serverFix = "la1";
    
  }
  else if($server == "las") {
    
    $serverFix = "la2";
    
  }
  else if($server == "eune") {
    
    $serverFix = "eun1";
    
  }
  //skip euw because no actual change is made (euw -> euw1)
  else {
    
    $serverFix = $server . "1";
    
  }
  $curl = curl_init('https://' . $server . '.api.pvp.net/championmastery/location/' . mb_strtoupper($serverFix) . '/player/' . $summId .'/topchampions' . '?api_key=7c9bb750-2c52-48c3-95d1-9d6d81a8e226');
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  $result = curl_exec($curl);
  curl_close($curl);
  return $result;

}

function summRankedData($summId, $server) {
  
  $curl = curl_init('https://' . $server . '.api.pvp.net/api/lol/' . $server . '/v1.3/stats/by-summoner/' . $summId .'/ranked?api_key=<API_KEY_HERE>');
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  $result = curl_exec($curl);
  curl_close($curl);
  return $result;

}

function summLeagueData($summId, $server) {
  
  $curl = curl_init('https://' . $server . '.api.pvp.net/api/lol/' . $server . '/v2.5/league/by-summoner/' . $summId .'/entry?api_key=<API_KEY_HERE>');
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  $result = curl_exec($curl);
  curl_close($curl);
  return $result;

}

function champInfo($champId, $server) {
  
  $curl = curl_init('https://global.api.pvp.net/api/lol/static-data/' . $server . '/v1.2/champion/' . $champId . '?api_key=<API_KEY_HERE>');
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  $result = curl_exec($curl);
  curl_close($curl);
  return $result;
  
}

//for image support
function getVersionAsJson($server) {
  
  $curl = curl_init('https://global.api.pvp.net/api/lol/static-data/' . $server . '/v1.2/versions?api_key=<API_KEY_HERE>');
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  $result = curl_exec($curl);
  curl_close($curl);
  return json_decode($result)[0];
  
}

function determineWinLoss($rankedStats, $masteredChamp) {

  foreach($rankedStats as $item) {
      foreach($item as $id) {
        if($id["id"] == $masteredChamp) {
          
          return $id["stats"]["totalSessionsWon"] / $id["stats"]["totalSessionsPlayed"];
          
        }
      }
  }

  return false;
  
}

function determineMinionScore($rankedStats, $masteredChamp) {
  
  foreach($rankedStats as $item) {
      foreach($item as $id) {
        if($id["id"] == $masteredChamp) {
          
          return $id["stats"]["totalMinionKills"] / $id["stats"]["totalSessionsPlayed"];
          
        }
      }
  }

  return false;
  
}

function determineKDA($rankedStats, $masteredChamp) {
  
  foreach($rankedStats as $item) {
    foreach($item as $id) {
      if($id["id"] == $masteredChamp) {
        
        return ($id["stats"]["totalChampionKills"] + $id["stats"]["totalAssists"]) / $id["stats"]["totalDeathsPerSession"];
        
      }
    }
  }
  
}

function determineLeagueTier($rankedStats) {
  
    foreach($rankedStats as $item) {
      foreach($item as $id) {
        if($id["queue"] == "RANKED_SOLO_5x5") {
          
          return $id["tier"];
          
        }
      }
  }

  return false;
  
}

function determineLeagueDivision($rankedStats) {
  
    foreach($rankedStats as $item) {
      foreach($item as $id) {
        if($id["queue"] == "RANKED_SOLO_5x5") {
          return $id["entries"][0]["division"];
          
        }
      }
  }

  return false;
  
}

function determineLeaguePoints($rankedStats) {
  
    foreach($rankedStats as $item) {
      foreach($item as $id) {
          if($id["queue"] == "RANKED_SOLO_5x5") {
            

            return $id["entries"]["leaguePoints"];

        }
      }
    }

  return false;
  
}

function summ($summonerUnformatted, $server, $isSummA){

  $summoner = summoner_info_array_name($summonerUnformatted);
  $summoner_info = summInfo($summoner, $server);
  $summoner_info_array = json_decode($summoner_info, true);
  $summoner_id = $summoner_info_array[$summoner]['id'];

  if (empty($summoner_id)) {
    
    $_SESSION['error'] = 'No data on summoner ' . $summonerUnformatted . '. Did you make a typo?';
    redirect("error.php");
    return;
    
  }

  $masteryData = summChampData($summoner_id, $server);

  $masteryDataArray = json_decode($masteryData, true);

  $masteredChampionId = $masteryDataArray[0]['championId'];
  
  if(empty($masteredChampionId)){
    
    $_SESSION['error'] = "No mastered champions on summoner " . $summonerUnformatted . ' according to the Riot API.';
    redirect("error.php");
    return;
    
  }
  
  //no empty checking here, uses data thats already been checked

  $championInfo = champInfo($masteredChampionId, $server);

  $championInfoArray = json_decode($championInfo, true);

  $championName = $championInfoArray["name"];
  
  $championKey = $championInfoArray["key"];

  $winLossRankedAll = summRankedData($summoner_id, $server);

  $winLossRankedAllArray = json_decode($winLossRankedAll, true);

  $winLossPerMasteredChamp = determineWinLoss($winLossRankedAllArray, $masteredChampionId);

  $winLossPerMasteredChampPercent = round((float)$winLossPerMasteredChamp * 100 );
  
  //same as above, no need for emptiness checks
  
  $KDAPerMasteredChamp = determineKDA($winLossRankedAllArray, $masteredChampionId);
  
  $KDAPerMasteredChampRound = round((float)$KDAPerMasteredChamp * 100) / 100;
  
  //CS

  $creepScoreAvg = determineMinionScore($winLossRankedAllArray, $masteredChampionId);

  $creepScoreAvgRound = round((float)$creepScoreAvg);
  
  $leagueData = summLeagueData($summoner_id, $server);
  
  $leagueDataArray = json_decode($leagueData, true);
  
  $leagueTier = determineLeagueTier($leagueDataArray);
  
  if (empty($leagueTier)) {
    
    $_SESSION['error'] = "No ranked data available on summoner " . $summonerUnformatted .  ". Has a ranked game been played?";
    redirect("error.php");
    return;
    
  }
  if(empty($winLossPerMasteredChamp)) {
    
    $_SESSION['error'] = "No win/loss data available on summoner " . $summonerUnformatted . ". Has a ranked game been played with a mastered champion?";
    redirect("error.php");
    return;
    
  }
  

  
  // same as above, no empty check, uses data that's already been checked
  
  $leagueDivision = determineLeagueDivision($leagueDataArray);
  
  $currentVersion = getVersionAsJson($server);
  
  if($isSummA){
    
    //THIS SECTION IS COPY/PASTED FOR EACH CHART, WITH MODIFICATIONS AS NEEDED
    
    // summoner name
    
    echo '<div class="contentLayerOne"><div class="summonerName">';
    
    echo '<p style="display: none;">' . $summonerUnformatted . '</p>';
    
    echo '</div></div>';
    
    //champ image
    
    echo '<img class="img-circle layerOne left" src="http://ddragon.leagueoflegends.com/cdn/' . $currentVersion . '/img/champion/' . $championKey . '.png" style="display: none;"/>';
    
    //matchup label
    
    echo '<center><div class="content"><p class="centerLayer">Mastery Matchup</p></div></center>';
    
    //chart
    
    echo '<div id="summAWinrate" class="svg-pie"></div>';
  
    echo "<script>";

    echo '$(document).ready(function() {
      $(".axe").rotate({
              duration: 2500,
              angle: 0,
              animateTo: 1800,
              callback: function() {
              
                $(".axe").fadeOut(function(){
                
                  $(\'#summAWinrate\').svgPie({
                  dimension: 200,
                  percentage: ' . $winLossPerMasteredChampPercent . '});
                  
                  $(\'.img-circle.layerOne.left\').show();
                  $(\'.img-circle.layerOne.right\').show();
                  $(\'.plusMinusSignLeft\').show();
                  $(\'.plusMinusSignRight\').show();
                  $(\'.rankImageLeft\').show();
                  $(\'.rankImageRight\').show();
                  $(\'.centerLayer\').show();
                  $(\'.megaphoneLeft\').show();
                  $(\'.megaphoneRight\').show();
                  $(\'.centerLayer\').show();
                  $(\'.creepScoreImageLeft\').show();
                  $(\'.creepScoreImageRight\').show();
                  $(\'p\').show();
                });
              
              }
      });
   });';
   
    echo "</script>";
    
    echo '<center><div class="contentLayerTwo"><p class="centerLayer">Champion Winrate</p></div></center>';
    
    echo '<div class="contentLayerThree"><center><p class="centerLayer">K/D/A ratio</p></center><img class="plusMinusSignLeft" src="img/plus.png" style="display: none;"/><div class="textLayerLeft"><p style="display: none;">' . $KDAPerMasteredChampRound . ' KDA</p></div></div>';
    
    echo '<div class="contentLayerFour"><center><p class="centerLayer">Creep Score</p></center>';
    
    echo '<img class="creepScoreImageLeft" src="img/MinionLeft.png" style="display: none;"/><div class="creepTextLayerLeft"><p style="display: none; color: white;">' . $creepScoreAvgRound . ' CS</p></div></div>';
    
    //set up contentLayer and "Rank" title, this will be done differently in summonerTwo because we will have already set up the title
    echo '<div class="contentLayerFive"><center><p class="centerLayer">Rank</p></center>';
    
    //next, put ranked tier and division text above image
    echo '<div class="rankTextLayerLeft"><p style="display:none";>' . ucwords($leagueTier) . ' ' . $leagueDivision . '</p></div>';
    
    //giant bunch if/else statements to determine rank and output it nicely. i should have used a switch statement. Oops! I'll change it if I get around to it.
    
    if($leagueTier == "BRONZE") {
      
      echo '<img class="rankImageLeft" width="200px" height="200px" style="display:none;" src="img/Bronze.png"/>';
      
    }
    else if($leagueTier == "SILVER") {
      
      echo '<img class="rankImageLeft" width="200px" height="200px" style="display:none;" src="img/Silver.png"/>';
      
    }
    else if($leagueTier == "GOLD") {
      
      echo '<img class="rankImageLeft" width="200px" height="200px" style="display:none;" src="img/Gold.png"/>';
      
    }
    else if($leagueTier == "PLATINUM") {
      
      echo '<img class="rankImageLeft" width="200px" height="200px" style="display:none;" src="img/Plat.png"/>';
      
    }
    else if($leagueTier == "DIAMOND") {
      
      echo '<img class="rankImageLeft" width="200px" height="200px" style="display:none;" src="img/Diamond.png"/>';
      
    }
    else if($leagueTier == "MASTER") {
      
      echo '<img class="rankImageLeft" width="200px" height="200px" style="display:none;" src="img/Master.png"/>';
      
    }
    else if($leagueTier == "CHALLENGER") {
      
      echo '<img class="rankImageLeft" width="200px" height="200px" style="display:none;" src="img/Challenger.png"/>';
      
    }
    
    echo "</div>";
    
  }
  else {
    
    //summoner B, output accordingly
    
    //summoner name
    
    echo '<div class="contentLayerOne"><div class="summonerTwoName">';
    
    echo '<p style="display: none;">' . $summonerUnformatted . '</p>';
    
    echo '</div></div>';
    
    echo '<img class="img-circle layerOne right" src="http://ddragon.leagueoflegends.com/cdn/' . $currentVersion . '/img/champion/' . $championKey . '.png" style="display: none;"/>';
    
    echo '<div id="summBWinrate" class="svg-pie"></div>';
      
    //rotate axes on the RIGHT side, but don't unhide anything, as it's already been done
    
    echo "<script>";
    
    echo '$(document).ready(function() {
      $(".axeTwo").rotate({
              duration: 2500,
              angle: 0,
              animateTo: -1800,
              callback: function() {
              
                $(".axeTwo").fadeOut(function(){
                
                  $(\'#summBWinrate\').svgPie({
                  dimension: 200,
                  percentage: ' . $winLossPerMasteredChampPercent . '});
                  
                });
              
              }
      });
   });';

    echo "</script>";
  
    echo '<div class="contentLayerThree"><img class="plusMinusSignRight" src="img/plus.png" style="display: none;"/><div class="textLayerRight"><p style="display: none;">' . number_format($KDAPerMasteredChampRound, 2) . ' KDA</p></div></div>';
    
    echo '<div class="contentLayerFour">';
    
    echo '<img class="creepScoreImageRight" src="img/MinionRight.png" style="display: none;"/><div class="creepTextLayerRight"><p style="display: none; color: white;">' . $creepScoreAvgRound . ' CS</p></div>';
    
    echo '</div>';
    
    echo '<div class="contentLayerFive">';
    
    echo '<div class="rankTextLayerRight"><p style="display:none";>' . ucwords($leagueTier) . ' ' . $leagueDivision . '</p></div>';
    
    if($leagueTier == "BRONZE") {
      
      echo '<img class="rankImageRight" width="200px" height="200px" style="display:none;" src="img/Bronze.png"/>';
      
    }
    else if($leagueTier == "SILVER") {
      
      echo '<img class="rankImageRight" width="200px" height="200px" style="display:none;" src="img/Silver.png"/>';
      
    }
    else if($leagueTier == "GOLD") {
      
      echo '<img class="rankImageRight" width="200px" height="200px" style="display:none;" src="img/Gold.png"/>';
      
    }
    else if($leagueTier == "PLATINUM") {
      
      echo '<img class="rankImageRight" width="200px" height="200px" style="display:none;" src="img/Plat.png"/>';
      
    }
    else if($leagueTier == "DIAMOND") {
      
      echo '<img class="rankImageRight" width="200px" height="200px" style="display:none;" src="img/Diamond.png"/>';
      
    }
    else if($leagueTier == "MASTER") {
      
      echo '<img class="rankImageRight" width="200px" height="200px" style="display:none;" src="img/Master.png"/>';
      
    }
    else if($leagueTier == "CHALLENGER") {
      
      echo '<img class="rankImageRight" width="200px" height="200px" style="display:none;" src="img/Challenger.png"/>';
      
    }
    
    echo "</div>";
    
  }
  
  //this is the structure of the array we will use for comparisons. [0] = champName, [1] = winrate, etc
  return array($championName, $winLossPerMasteredChampPercent, $KDAPerMasteredChampRound, $leagueTier, $leagueDivision, $creepScoreAvgRound, $summonerUnformatted);

}

//AXES FOR DRAVEN

echo "<div class=\"contentLayerOneAxe\">";

echo "<img class=\"axe\" src=\"img/DRAAAAAAAVEN_static_axe.png\"/>";
echo "<img class=\"axeTwo\" src=\"img/DRAAAAAAAVEN_static_axe_two.png\"/>";

echo "</div>";

echo "<div class=\"contentAxe\">";

echo "<img class=\"axe\" src=\"img/DRAAAAAAAVEN_static_axe.png\"/>";
echo "<img class=\"axeTwo\" src=\"img/DRAAAAAAAVEN_static_axe_two.png\"/>";

echo "</div>";


echo "<div class=\"contentLayerTwoAxe\">";

echo "<img class=\"axe\" src=\"img/DRAAAAAAAVEN_static_axe.png\"/>";
echo "<img class=\"axeTwo\" src=\"img/DRAAAAAAAVEN_static_axe_two.png\"/>";

echo "</div>";


echo "<div class=\"contentLayerThreeAxe\">";

echo "<img class=\"axe\" src=\"img/DRAAAAAAAVEN_static_axe.png\"/>";
echo "<img class=\"axeTwo\" src=\"img/DRAAAAAAAVEN_static_axe_two.png\"/>";

echo "</div>";

echo "<div class=\"contentLayerFourAxe\">";

echo "<img class=\"axe\" src=\"img/DRAAAAAAAVEN_static_axe.png\"/>";
echo "<img class=\"axeTwo\" src=\"img/DRAAAAAAAVEN_static_axe_two.png\"/>";

echo "</div>";

echo "<div class=\"contentLayerFiveAxe\">";

echo "<img class=\"axe\" src=\"img/DRAAAAAAAVEN_static_axe.png\"/>";
echo "<img class=\"axeTwo\" src=\"img/DRAAAAAAAVEN_static_axe_two.png\"/>";

echo "</div>";


//get summ1 via post

$summoner = $_POST["summ"];

$server = $_POST["summRegion"];

$summonerOneData = summ($summoner, $server, true);

$summonerOneData = array_values($summonerOneData);

//get summ2 via post

$summonerTwo = $_POST["summTwo"];

$serverTwo = $_POST["summTwoRegion"];

$summonerTwoData = summ($summonerTwo, $serverTwo, false);

$summonerTwoData = array_values($summonerTwoData);

//REAL QUICK: We have to assign ranked tiers and divisions a numerical value to help with comparison.

abstract class Tiers
{
    const BRONZE = 0;
    const SILVER = 1;
    const GOLD = 2;
    const PLATINUM = 3;
    const DIAMOND = 4;
    const MASTER = 5;
    const CHALLENGER = 6;

}

abstract class Divisions
{
    const V = 0;
    const IV = 1;
    const III = 2;
    const II = 3;
    const I = 4;
}

//SCORING TIME

//SCORING TIME IS CURRENTLY VERY MESSY. IM LOOKING FOR A WAY TO DO THIS WITH LOOPS.

$summOneScore = 0;

$summTwoScore = 0;

//ranked tier scoring comparison


if(constant('Tiers::' . $summonerOneData[3]) > constant('Tiers::' . $summonerTwoData[3])) {
  
  $summOneScore += 75;
    if((constant('Tiers::' . $summonerOneData[3]) - constant('Tiers::' . $summonerTwoData[3])) >= 2) {
      
      $summOneScore += 40;
     
      if((constant('Tiers::' . $summonerOneData[3]) - constant('Tiers::' . $summonerTwoData[3])) >= 3) {
        
        $summOneScore += 70;
       
        if((constant('Tiers::' . $summonerOneData[3]) - constant('Tiers::' . $summonerTwoData[3])) >= 4) {
          
          $summOneScore += 125; //if theyre that far ahead they get a few bonus points, because bronze might beat silver, but bronze will very rarely beat platinum
          
        }
         
      }
       
    }

}
else if(constant('Tiers::' . $summonerTwoData[3]) > constant('Tiers::' . $summonerOneData[3])) {
  
  $summTwoScore += 75;
    if((constant('Tiers::' . $summonerTwoData[3]) - constant('Tiers::' . $summonerOneData[3])) >= 2) {
      
      $summTwoScore += 40;
     
      if((constant('Tiers::' . $summonerTwoData[3]) - constant('Tiers::' . $summonerOneData[3])) >= 3) {
        
        $summTwoScore += 70;
       
        if((constant('Tiers::' . $summonerTwoData[3]) - constant('Tiers::' . $summonerOneData[3])) >= 4) {
          
          $summTwoScore += 125; // same as above, they get a few bonus points, because for ex. bronze might beat silver, but bronze will very rarely beat platinum
          
        }
         
      }
       
    }

}

//equal tier. check division now. division is only checked when tiers are equal.
else if(constant('Divisions::' . $summonerOneData[4]) > constant('Divisions::' . $summonerTwoData[4])) {
  
  $summOneScore += 35;
  
}
else if(constant('Divisions::' . $summonerTwoData[4]) > constant('Divisions::' . $summonerOneData[4])) {
  
  $summTwoScore += 35;
  
}
else {
  
  //tiers are equal too? close matchup, huh.
  //nothing is done in this else, just a little comment here. it is possible that a tricky user intentionally typed the same summoner in the two boxes.
  
}

//winrate scoring comparison

if($summonerOneData[1] > $summonerTwoData[1]) {
  
  $summOneScore += 40;
  if($summonerOneData[1] - $summonerTwoData[1] >= 3) {
    
    $summOneScore += 4;
    if($summonerOneData[1] - $summonerTwoData[1] >= 5) {
      
      $summOneScore += 4;
      if($summonerOneData[1] - $summonerTwoData[1] >= 7) {
        
        $summOneScore += 4;
        if($summonerOneData[1] - $summonerTwoData[1] >= 9) {
          
          $summOneScore += 4;
          if($summonerOneData[1] - $summonerTwoData[1] >= 11) {
            
            $summOneScore += 4;
            if($summonerOneData[1] - $summonerTwoData[1] >= 13) {
              
              $summOneScore += 4;
              if($summonerOneData[1] - $summonerTwoData[1] >= 15) {
                
                $summOneScore += 4;
                if($summonerOneData[1] - $summonerTwoData[1] >= 17) {
                  
                  $summOneScore += 4;
                  if($summonerOneData[1] - $summonerTwoData[1] >= 19) {
                    
                    $summOneScore += 4;
                    
                  }
                  
                }
                
              }
              
            }
            
          }
          
        }
        
      }
      
    }
    
  }
  
}

if($summonerTwoData[1] > $summonerOneData[1]) {
  
  $summTwoScore += 40;
  if($summonerTwoData[1] - $summonerOneData[1] >= 3) {
    
    $summTwoScore += 4;
    if($summonerTwoData[1] - $summonerOneData[1] >= 5) {
      
      $summTwoScore += 4;
      if($summonerTwoData[1] - $summonerOneData[1] >= 7) {
        
        $summTwoScore += 4;
        if($summonerTwoData[1] - $summonerOneData[1] >= 9) {
          
          $summTwoScore += 4;
          if($summonerTwoData[1] - $summonerOneData[1] >= 11) {
            
            $summTwoScore += 4;
            if($summonerTwoData[1] - $summonerOneData[1] >= 13) {
              
              $summTwoScore += 4;
              if($summonerTwoData[1] - $summonerOneData[1] >= 15) {
                
                $summTwoScore += 4;
                if($summonerTwoData[1] - $summonerOneData[1] >= 17) {
                  
                  $summTwoScore += 4;
                  if($summonerTwoData[1] - $summonerOneData[1] >= 19) {
                    
                    $summTwoScore += 4;
                    
                  }
                  
                }
                
              }
              
            }
            
          }
          
        }
        
      }
      
    }
    
  }
  
}

// KDA scoring comparison

if($summonerOneData[2] > $summonerTwoData[2]) {
  
  $summOneScore += 10;
    if(($summonerOneData[2] - $summonerTwoData[2]) >= 0.5) {
      
      $summOneScore += 20;
     
      if(($summonerOneData[2] - $summonerTwoData[2]) >= 1.0) {
        
        $summOneScore += 30;
       
        if(($summonerOneData[2] - $summonerTwoData[2]) >= 2.0) {
          
          $summOneScore += 30;
          
        }
         
      }
       
    }

}

else if($summonerTwoData[2] > $summonerOneData[2]) {
  
  $summTwoScore += 10;
    if(($summonerTwoData[2] - $summonerOneData[2]) > 0.5) {
      
      $summTwoScore += 20;
     
      if(($summonerTwoData[2] - $summonerOneData[2]) > 1.0) {
        
        $summTwoScore += 30;
       
        if(($summonerTwoData[2] - $summonerOneData[2]) > 2.0) {
          
          $summTwoScore += 30;
          
        }
         
      }
       
    }

}

//KDA scoring general

if($summonerOneData[2] >= 1) {
  
  $summOneScore += 5;
  
  if($summonerOneData[2] >= 1.2) {
    
    $summOneScore += 4;
   
    if($summonerOneData[2] >= 1.5) {
      
      $summOneScore += 7;
      
      if($summonerOneData[2] >= 2) {
        
        $summOneScore += 7;
        
        if($summonerOneData[2] >= 2.5) {
          
          $summOneScore += 7;
          
          if($summonerOneData[2] >= 3.5) {
            
            $summOneScore += 7;
            
          }
          
        }
        
      }
      
    }
    
  }
  
}

if($summonerTwoData[2] >= 1) {
  
  $summTwoScore += 5;
  
  if($summonerTwoData[2] >= 1.2) {
    
    $summTwoScore += 4;
   
    if($summonerTwoData[2] >= 1.5) {
      
      $summTwoScore += 7;
      
      if($summonerTwoData[2] >= 2) {
        
        $summTwoScore += 7;
        
        if($summonerTwoData[2] >= 2.5) {
          
          $summTwoScore += 7;
          
          if($summonerTwoData[2] >= 3.5) {
            
            $summTwoScore += 7;
            
          }
          
        }
        
      }
      
    }
    
  }
  
}

//CS scoring comparison

if($summonerOneData[5] > $summonerTwoData[5]) {
  
  $summOneScore += 55;
    if($summonerOneData[5] - $summonerTwoData[5] >= 15) {
      
      $summOneScore += 7;
      if($summonerOneData[5] - $summonerTwoData[5] >= 25) {
        
        $summOneScore += 10;
        if($summonerOneData[5] - $summonerTwoData[5] >= 50) {
          
          $summOneScore += 15;
          
          if($summonerOneData[5] - $summonerTwoData[5] >= 75) {
            
            //well someone either is very bad or very good haha
            $summOneScore += 15;
            
          }
          
        }
        
      }
      
    }
  
}
else if($summonerTwoData[5] > $summonerOneData[5]) {
  
  $summTwoScore += 55;
    if($summonerTwoData[5] - $summonerOneData[5] >= 15) {
      
      $summTwoScore += 7;
      if($summonerTwoData[5] - $summonerOneData[5] >= 25) {
        
        $summTwoScore += 10;
        if($summonerTwoData[5] - $summonerOneData[5] >= 50) {
          
          $summTwoScore += 15;
          if($summonerTwoData[5] - $summonerOneData[5] >= 75) {
            
            //well someone either is very bad or very good haha
            $summTwoScore += 15;
            
          }
          
        }
        
      }
      
    }
  
}

echo '<img style="display: none;" class="megaphoneLeft" src="img/HeaderLeft.png"/><div class="contentLayerOne"><center><p style="display: none;" class="centerLayerWinner">';

if($summOneScore > $summTwoScore) {
  
  echo $summonerOneData[6] . ' wins!';
  echo '<br><p style="font-size: 14; display: none; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif;">Scroll down for complete info.</p>';
  
}
else if($summOneScore < $summTwoScore) {
  
  echo $summonerTwoData[6] . ' wins!';
  echo '<br><p style="font-size: 14; display: none; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif;">Scroll down for complete info.</p>';
  
}

else {
  
  //a tie??? Highly unlikely, the user probably typed the same summoner in the different boxes. Regardless, let the user know it's a tie game.
  echo 'Unbelievable! Tie game!';
  echo '<br><p style="font-size: 14; display: none; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif;">Scroll down for complete info.</p>';
  
}


echo "</p></center></div>";

echo '<img style="display: none;" class="megaphoneRight" src="img/HeaderRight.png"/>';

?>

</body>
</html>
