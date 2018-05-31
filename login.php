<?php
	require_once 'config.php';

	$id = $id_error = "";

	if(empty(trim($_POST['id']))) {
		$id_error = "Please enter an ID";
	} else {
		$id = trim($_POST['id']);
	}

	if(empty($id_error)) {
		$sql = "SELECT " . ($_POST['occupation'] . "ID") . " FROM " . $_POST['occupation'] 
				  . " WHERE " . ($_POST['occupation'] . "ID") . " = '" . $_POST['id'] . "'";
		if($query = mysqli_prepare($conn, $sql)) {
			$param_id = $id;
			$occupation = $_POST['occupation'];

			if(mysqli_stmt_execute($query)) {
				mysqli_stmt_store_result($query);
				if(mysqli_stmt_num_rows($query) == 1) {
					$occupation = $_POST['occupation'];
					if ($occupation == "instructor") {
						session_start();
						$_SESSION['instructorID'] = $id;
						header("location: instructor.php");
					} else {
						session_start();
						$_SESSION['studentID'] = $id;
						header("location: student.php");
					}
				} else {
					$id_error = "No such user ID found.";
					echo "<script>
						  	  alert(\"Please enter a valid User ID.\");
						  </script>";
				}
			} else {
				echo "Oops! We got our wires crossed.";
			}
		}
		mysqli_stmt_close($query);
	}
	mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Log In</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body{ font: 14px sans-serif; text-align: center; padding-bottom: 40px; }
        a{ margin: 40px auto 40px auto; }
    </style>
</head>
<body>
	<div class="page-header">
		<h1>Log In</h1>
	</div>
	<div class="center-block">
		<form class="form-inline" action="" method="POST">
		Login ID:
		<input class="form-control" type="text" name="id" maxlength="16" required>
		I am a:
		<select class="form-control" name="occupation" required>
			<option selected disabled hidden>Select</option>
			<option value="student">Student</option>
			<option value="instructor">Instructor</option>
		</select>
		<input class="btn btn-primary" type="submit" value="Log In">
	</div>
</body>
</html>