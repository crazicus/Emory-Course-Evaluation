<?php
	$conn = mysqli_connect("<DATABASE SERVER>", "<USERNAME>", "<PASSWORD>", "<DATABASE>");

	if(!$conn) {
		die("Connection Failed: " . mysqli_connect_error());
	}
?>
