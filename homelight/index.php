<html>
<head>
<title>Home Light</title>
<meta name="viewport" content="user-scalable=no, width=device-width, minimum-scale=1.0 initial-scale=1.0">
<link rel="apple-touch-icon" sizes="114x114" href="HSIcon114x114px.png" />
<link rel="apple-touch-icon" sizes="144x144" href="HSIcon144x144px.png" />
<link rel="shortcut icon" href="HSIcon32x32px.png" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<link rel="stylesheet" href="w3.css">
<style>

* {
	font-family: sans-serif;
}

.sinput[type=button], .sinput[type=submit], .sinput[type=reset] {
	-webkit-appearance: none;
   -webkit-border-radius: 0px;
    border: none;
    color: white;
    padding: 4vh 8vw;
    text-decoration: none;
    margin: 4px 2px;
    cursor: pointer;
}


h1 {
  text-align: center;
  width:100%;
  font-size: 2.5em;
}


html, body {
  overflow-x: hidden;
}
body {
  position: relative
}

.icon {
  position: absolute;
  height: 4em;
}

element:hover, element:active { CSS-Eigenschaften; -webkit-user-select: none; -webkit-touch-callout: none}



</style>

<script>

  document.addEventListener("touchstart", function(){}, true);

  function openRoom(room) {
    var i;
    var x = document.getElementsByClassName("room");
    for (i = 0; i < x.length; i++) {
        x[i].style.display = "none"; 
    }
    document.getElementById(room).style.display = "block"; 
}
</script>
</head>

	
<?php

$controls = [
["10101", "4", "Regal", "Wohnzimmer"],
["10101", "3", "Klavier", "Wohnzimmer"],
["10101", "1", "Esszimmer", "Wohnzimmer"],
["11111", "1", "Leselampe", "Niklas"],
["11111", "2", "Linke Lampe", "Niklas"],
["11111", "3", "Fenster", "Niklas"],
["11111", "4", "Rechte Lampe", "Niklas"],
["10011", "2", "Ecke", "Niklas"]
];

$rooms = [];

foreach ($controls as $x) {
  $rooms[$x[3]] =1;
}

echo '<body onload="document.getElementById(\'room_'. array_keys($rooms)[0] .'\').click();">' ;

// openRoom(\''. array_keys($rooms)[0] .'\')">'
?>


  <iframe id="invisible" name="invisible" style="display:none;"></iframe>

  <img src="HSIconw114x114px.png" class="icon" />
  <h1>Home Light</h1>

<?php
echo '<div class="w3-bar w3-blue">'. PHP_EOL;
foreach (array_keys($rooms) as $x) {
  echo '<button id="room_'. $x .'" class="w3-bar-item w3-button" onclick="openRoom(\'' . $x . '\')">' . $x. '</button>'. PHP_EOL;
}

echo '</div>'. PHP_EOL;


foreach (array_keys($rooms) as $room) {
  echo '<div class="room" id="' . $room . '">' . PHP_EOL;
  echo '<form>'. PHP_EOL;
  echo '<table  width="100%">'. PHP_EOL;
  foreach ($controls as $x) {

    if ($x[3] == $room) {
      echo  '<tr><td width=50%>' . $x[2] . '</td>' . PHP_EOL;
      echo '<td><input class="w3-button w3-round-large w3-block w3-red" type="submit" name="' . $x[0] . '_' . $x[1] . '_0' . '" formmethod="post"  formtarget="invisible" value="Off" formaction="active.php" /></td>'. PHP_EOL;
      echo '<td><input class="w3-button w3-round-large w3-block w3-green" type="submit" name="' . $x[0] . '_' . $x[1] . "_1" . '" formmethod="post"  value="On" formtarget="invisible" formaction="active.php" /></td></tr>'. PHP_EOL;
    }
  }
  echo "</table></form></div>". PHP_EOL;    
}
?>    
	</body>
</html>