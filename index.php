<html>
<head>
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Cabin" />
<link rel="stylesheet" type="text/css" href="css/rito.css">
<title>Who wins...</title>
</head>
<body>
<div id="content">
<!--<img src="img/WouldIWin.png" class="left"/>-->
<br>
<center><h1>Two summoners. 1v1. Favorite champions.</h1></center>
<center>Who wins? Find out now. Enter two ranked summoners and click on<br>DRAAAAAAVEN's head to predict the winner.</center>
<div width="100%" height="100%">
<form name="summs" id="container" method="POST" action="matchup.php">
  <select name="summRegion">
  <option value="na">NA</option>
  <option value="euw">EUW</option>
  <option value="eune">EUNE</option>
  <option value="br">BR</option>
  <option value="jp">JP</option>
  <option value="kr">KR</option>
  <option value="lan">LAN</option>
  <option value="las">LAS</option>
  <option value="oce">OCE</option>
  <option value="ru">RU</option>
  <option value="tr">TR</option>
</select>
<input type="text" name="summ" id="summOne" placeholder="Summoner A"> &nbsp;vs&nbsp;
<input type="text" name="summTwo" id="summonTwo" placeholder="Summoner B">
<select name="summTwoRegion">
  <option value="na">NA</option>
  <option value="euw">EUW</option>
  <option value="eune">EUNE</option>
  <option value="br">BR</option>
  <option value="jp">JP</option>
  <option value="kr">KR</option>
  <option value="lan">LAN</option>
  <option value="las">LAS</option>
  <option value="oce">OCE</option>
  <option value="ru">RU</option>
  <option value="tr">TR</option>
</select>
<div width="100%" height="100%">
<br>
<center><p id="clickDRAAAAAAVEN">Type in two summoners, then click on the one and only<br>DRAAAAAAAVEN.</p></center>
<img src="img/DRAAAAAAAVEN.png" alt="Not Draven. DRAAAAAAAAAVEN." class="DRAAAAAAAAAVEN" onclick="document.forms['summs'].submit();" />
</form>
<script>
document.getElementById('summonTwo').onkeydown = function(e){
   if(e.keyCode == 13){
      document.forms['summs'].submit();
   }
};
document.getElementById('summOne').onkeydown = function(e){
   if(e.keyCode == 13){
      document.getElementById("summonTwo").focus();
   }
};

</script>
</div>
</div>
</div>
<p id="footer">1v1 Predictor isn't endorsed by Riot Games and doesn't reflect the views or opinions of Riot Games or anyone officially involved in producing or managing League of Legends. League of Legends and Riot Games are trademarks or registered trademarks of Riot Games, Inc. League of Legends &copy; Riot Games, Inc.</p>
</body>

<?php 

  //wow we didn't even need php for this page! I'll keep it in the PHP format for the sake of consistency.

?>

</html>
