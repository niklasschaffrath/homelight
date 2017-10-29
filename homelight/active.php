<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
<?php
$param =str_replace("_", " ", array_keys($_POST)[0]);

$command = "sudo /usr/local/bin/send " . $param;

echo shell_exec($command);
echo "<br>";
echo "Data:<br>";
foreach ($_POST as $key => $value) {
	echo $key . " -> " . $value . "<br>";
}
?>
</body>
</html>