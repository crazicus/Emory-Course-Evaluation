<?php 
	require_once 'config.php';
	session_start();

	$studentID = $_SESSION['studentID'];
	$courseID = $_SESSION['courseID'];

	$input_base = "INSERT INTO completes VALUES($courseID, ";

	foreach ($_POST as $key => $value) {
		$input = $input_base;
		if ($key == 'complete') {
			break 1;
		} else if ($value != "") {
			$input .= "$key, '$studentID', '$value')";
		} else {
			continue;
		}

		if(!($result = mysqli_query($conn, $input))) {
			die("Error: " . mysqli_error($conn) . $input);
		} 
	}

	$_SESSION['studentID'] = $studentID;
	$_SESSION['completed'] = $courseID;
	header("location: student.php");
?>