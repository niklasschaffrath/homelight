<!DOCTYPE html>
<html>
<head>
	<title></title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="jquery-fullscreen.js"></script>
<style type="text/css">
	.main {
		height: 600px;
		width: 1024px;
	}
	.button {
		height: 60px;
		width: 124px;
		line-height: 60px;
		text-align: center;
		background-color: orange;
		font-family: Verdana;
		font-size: 18px
	}

	.column {
    float: left;
	}

	/* Left and right column */
	.column.side {
    	width: 124px;
	}

	/* Middle column */
	.column.middle {
    	width: 900px;
	}

	iframe {
		width:900px;
		height:600px;
		border: none;

	}

	.overlay {
		width:100%;
		height:600px;
		line-height: 600px;
		font-size: 100px;
		position: fixed;
		background-image:url(reload_icon.png);
	}

	
</style>

<script type="text/javascript">

function do_overlay(){

	var $e = $('<button class="overlay" id="overlay" type="button" >Press to Start</button>');

	$e.click(function() {
		$("#main").fullscreen().request();
		$("#overlay").remove();
	});

	$("body").prepend($e);
} 

$(document).ready(function() {

	$(document).on('fullScreenChange', function() {
   		if (!$("#main").fullscreen().isActive() ) {
   			do_overlay();
   		}
 	});

 	do_overlay();

	let targets= [["Temperatur", 'temp.php'],
			["Home Light", 'http://192.168.178.26'],
			["Weather", 'weather.html'],
			["Tagesschau", 'http://www.tagesschau.de']];


	targets.forEach(function(t) {
		var $e = $("<input>", {'class':'button', 'type':'button', 'value': t[0]});
		$e.click(function() {
			$('#iframe').attr('src', t[1]);
		})
		$("#links").append($e);
	})
});
</script>

</head>
<body>
	<div class="main" id="main">
		<div class="column side" id="links">
			<!-- filled by javascript -->
		</div>
		<div class="column.middle">
			<iframe id="iframe" src="temp.php"></iframe>
		</div>
	</div>
</body>
</html>