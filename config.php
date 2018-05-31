<?php
	$conn = mysqli_connect("localhost", "cs377", "cs377_s18", "eval");

	if(!$conn) {
		die("Connection Failed: " . mysqli_connect_error());
	}
?>