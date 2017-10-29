<?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
<?php if ($_POST["cmd"] == "database"): ?>
<?php

$mysqli = new mysqli("localhost", "admin", "admin", "meas");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    exit();
}

$create = <<< EOT
CREATE TABLE temperature ( id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    time DATETIME DEFAULT CURRENT_TIMESTAMP,
    temp     INT NOT NULL )
EOT
;

if (!$mysqli->query("DROP TABLE IF EXISTS temperature") ||
    !$mysqli->query($create)) {
    echo "Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error;
    exit();
}

echo "table successfully created"

?>
<?php else: ?>
<?php if ($_POST["cmd"] == "rawdate"): ?>
<?php
# Query on database 
$mysqli = new mysqli("localhost", "admin", "admin", "meas");
if (!($result = $mysqli->query("select * from temperature"))) {
    echo "Query failed: (" . $mysqli->errno . ") " . $mysqli->error;
    exit();
}
while ($row = $result->fetch_row()) {
    echo $row[1] . " : " . $row[2] . "<br>" . PHP_EOL;
}

?>
<?php else: ?>
<?php if ($_POST["cmd"] == "data"): ?>
<?php
# Query on database 
$mysqli = new mysqli("localhost", "admin", "admin", "meas");
if (!($result = $mysqli->query("select * from temperature WHERE time >= SUBTIME( NOW() , '08:00:00.0')"))) {
    echo "Query failed: (" . $mysqli->errno . ") " . $mysqli->error;
    exit();
}
echo "[";
$first = True;
while ($row = $result->fetch_row()) {
    if (!$first) {
        echo ",";
    }
    $first = False;
    echo '{ "t": "' . $row[1] . '",  "x": ' . floatval($row[2]) / 1000.0 . '}';
}
echo "]";
?>
<?php else: ?>
Unknown command
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>

<?php else: ?>
<!doctype html>
<html>
<head>
<!-- common scripts etc-->
<meta name="viewport" content="user-scalable=no, width=device-width, minimum-scale=1.0 initial-scale=1.0">
<link rel="apple-touch-icon" sizes="114x114" href="HSIcon_temp@114px.png" />
<link rel="apple-touch-icon" sizes="144x144" href="HSIcon_temp@144px.png" />
<link rel="shortcut icon" href="HSIcon_temp@32px.png" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<link rel="stylesheet" href="w3.css">

<!-- Admin Interface -->
<?php if ($_GET["cmd"] == "admin"): ?>
<title>Console Temperature Chart</title>
<style>

canvas{
    -moz-user-select: none;
    -webkit-user-select: none;
    -ms-user-select: none;
}

.log {
    height: 500px;
    overflow: scroll;
}

.header {
    height: 5em;
}

</style>

<script>

function log(msg) {
    $("#log").append("<div>" + msg + "</div");
    $("#log").scrollTop($("#log")[0].scrollHeight);
}

$(document).ready(function(){
    // register events to buttons
    $("#backtodata").click( function () {
        window.open("temp.php", "_self");
    });

    $("#createdatabase").click( function() {
        if (window.confirm("(re)generate database and delete all data?")) {
            log("Regenerating Database");
            $.post('temp.php', {'cmd': 'database'}, function(data, status) {
                log(status + ": " + data);
            });
        }
    });
    $("#dumpraw").click( function() {
        log("Dumping Database");
        $.post('temp.php', {'cmd': 'rawdate'}, function(data, status) {
            log(status + "<br>" + data);
        });
    
    });
    $("#data").click( function() {
        log("Data Call to Database");
        $.post('temp.php', {'cmd': 'data'}, function(data, status) {
            log(status + "<br>" + data);
        });
    
    });
});
</script>


</head>
<body>
    <div class="w3-container" style="max-width:1000px;">    
    <h1 class="w3-green">Temperature Recording Management Console</h1>
    <div class="w3-bar w3-green">
    <input type="button" id="backtodata" class="w3-bar-item w3-button w3-green"  value="Back to Data"/>
    
    <input type="button" id="dumpraw" class="w3-bar-item w3-button"  value="Dump Database"/>
    <input type="button" id="data" class="w3-bar-item w3-button"  value="Show data"/>
    <input type="button" id="createdatabase" class="w3-bar-item w3-button w3-red"  value="Create Database"/>
    </div>

    <div id="log" class="log w3-grey w3-container">
        Ready.
    </div>
    </div>
</body>

<!-- Normal Interface ###############################################   -->
<?php else: ?>
<!doctype html>
<html>

<head>
    <title>Temperature Chart</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.13.0/moment.min.js"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="utils.js"></script>
    <style>
    canvas{
        -moz-user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
    }
    .header {
        height: 5em;
    }
    </style>
</head>

<body>
     <div class="w3-container" style="max-width:1000px;">
      <div class="w3-bar w3-amber">
        <h1 class= "w3-cell w3-bar-item">Temperature History</h1>                
        <a href="javascript:void(0);" id= "admin"  class="w3-bar-item w3-button w3-right material-icons">settings</a>
        <a href="javascript:void(0);" id= "update" class="w3-bar-item w3-button w3-right material-icons">replay</a>
      </div>
    
    <div class="w3-row w3-margin-top">
        <canvas id="canvas"></canvas>
    </div>
    </div>
    <script>

        var timeFormat = 'MM/DD/YYYY HH:mm';
        function newDateString(days) {
            return moment().add(days, 'd').format(timeFormat);
        }

        function newTimestamp(days) {
            return moment().add(days, 'd').unix();
        }

        function newDate(days) {
            return moment().add(days, 'd').toDate();
        }
        var color = Chart.helpers.color;
        var config = {
            type: 'line',
            data: {
                datasets: []
            },
            options: {
                legend: {
                    display: false
                },
            
                scales: {
                    xAxes: [{
                        type: "time",
                        time: {
                            unit: 'hour',                            
                                // // round: 'day'
                                tooltipFormat: 'll HH:mm'
                            },
                            scaleLabel: {
                                display: false,
                            }
                    }, ],
                    yAxes: [{
                        type: "linear",
                        ticks: {
                            min: 15,
                            max: 25
                        },
                        scaleLabel: {
                            display: true,
                            labelString: '°C'
                        }
                    }]
                }
            }
        };

        window.onload = function() {
            var ctx = document.getElementById("canvas").getContext("2d");
            window.myLine = new Chart(ctx, config);
            update();
            setInterval(update, 120000);
        };


        document.getElementById('admin').addEventListener('click', function() {
            window.open("temp.php?cmd=admin","_self");

            window.myLine.update();
        });


        function update() {
            $.post('temp.php', {'cmd': 'data'}, function(rawdata, status) {
                var data2 = JSON.parse(rawdata);

                var data = data2.map(function(x) {
                    return {t: moment(x['t']), y: x['x']};
                });


                var newDataset = {                    
                    data: data,
                    backgroundColor: color(window.chartColors.red).alpha(0.5).rgbString(),
                    borderColor: window.chartColors.red,
                    fill: false,
                    cubicInterpolationMode: 'monotone',
                };

                config.data.datasets[0]= newDataset;
                window.myLine.update();
            });
        };

        document.getElementById('update').addEventListener('click', update);
    </script>
   </body> 
<?php endif; ?>
</html>
<?php endif; ?>


