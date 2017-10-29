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
    max-width: 80%;
    overflow: scroll;
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
    <h1>Temperature Recording Management Console</h1>
    <div class="w3-bar w3-grey">
    <input type="button" id="backtodata" class="w3-button w3-green"  value="Back to Data"/>
    <input type="button" id="createdatabase" class="w3-button w3-green"  value="Create Database"/>
    <input type="button" id="dumpraw" class="w3-button w3-green"  value="Dump Database"/>
    <input type="button" id="data" class="w3-button w3-green"  value="Show data"/>
    </div>

    <div id="log" class="log w3-grey w3-container">
        Ready.
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
    <script src="utils.js"></script>
    <style>
    canvas{
        -moz-user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
    }
    </style>
</head>

<body>
    <div style="width:75%;">
        <canvas id="canvas"></canvas>
    </div>
    <br>
    <br>
    <input type="button" class="w3-button" id="update" value="Update" />
    <input type="button" class="w3-button" id="addDataset" value="Add Dataset" />
    <input type="button" class="w3-button" id="admin" value = "Admin"/>

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

                datasets: [{
                    label: "temperatur",
                    backgroundColor: color(window.chartColors.red).alpha(0.5).rgbString(),
                    borderColor: window.chartColors.red,
                    fill: false,
                    cubicInterpolationMode: 'monotone',
                    data: [],
                }]
            },

            options: {
                title:{
                    text: "Chart.js Time Scale"
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
                },
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

        var colorNames = Object.keys(window.chartColors);

        function update() {
            $.post('temp.php', {'cmd': 'data'}, function(rawdata, status) {
                var colorName = colorNames[0];
                var newColor = window.chartColors[colorName];

                var data2 = JSON.parse(rawdata);

                var data = data2.map(function(x) {
                    return {t: moment(x['t']), y: x['x']};
                });


                var newDataset = {                    
                    backgroundColor: newColor,
                    borderColor: newColor,
                    data: data,
                    fill: false
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


