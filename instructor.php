<?php
	require_once 'config.php';
	session_start();
	if(!isset($_SESSION['instructorID']) || empty($_SESSION['instructorID'])) {
		header("location: index.html");
		exit;
	}

	$id = $_SESSION['instructorID'];
    $query = "SELECT fname FROM instructor WHERE instructorID='" . $id . "'";
    $result = mysqli_query($conn, $query);
    if(!($result = mysqli_query($conn, $query))) {
    	die("Query failed: " . mysql_error($conn));
    }
    $row = mysqli_fetch_assoc($result);
	$name = $row['fname'];

	$query = "SELECT name 'Name', semester 'Semester', year 'Year' 
			  FROM course 
			  WHERE taughtBy = '$id' 
			  GROUP BY Name, Semester, Year
			  ORDER BY Year ASC, Semester DESC, Name ASC";
	$result = mysqli_query($conn, $query);
	if (!($result = mysqli_query($conn, $query))) {
		die("Query failed: " . mysqli_error($conn));
	}
	$count = 0;	

	$classes = "";
	while ($row = mysqli_fetch_assoc($result)) {
		$button_stats = "<form action=\"instructor_view.php\" method=\"POST\">" . 
					   "<button class=\"btn btn-primary\" type=\"submit\" value=\"" . $row['Name'] . 
					   '-' . $row['Semester'] . '-' . $row['Year'] . "\" name=\"stats\">View Stats</button></form>";
		$button_eval = "<form action=\"eval_questions.php\" method=\"POST\">" . 
					   "<button class=\"btn btn-primary\" type=\"submit\" value=\"" . $row['Name'] . 
					   '-' . $row['Semester'] . '-' . $row['Year'] . "\" name=\"eval\">Edit Questions</button></form>";
		if ($count == 0) {
    		$classes .= "<table class='table-bordered' style='margin: auto;'><thead><tr>";
    		foreach ($row as $key => $value) {
    			$classes .= "<th style='text-align: center; padding: 10px;'>" . $key . "</th>";
    		}
		}
		$classes .= "<tr>";
		foreach ($row as $key => $value) {
			$classes .= "<td style='padding: 10px;'>" . $value . "</td>";
		}
		$classes .= "<td style='padding: 10px;'>$button_stats</td>";
		$classes .= "</tr>";
    	$count++;
	}
	$classes .= "</tbody></table>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body{ font: 14px sans-serif; text-align: center; padding-bottom: 40px; }
        a{ margin: 40px auto 40px auto;}
    </style>
</head>
<body>
    <div class="page-header">
        <h1>Hi, <b><?= $name ?></b>. Welcome to the Emory Course Evaluation System.</h1>
    </div>
    <div class="center-block">
    	<h3>My classes</h3>
    	<?= $classes ?>
	</div>
    <p><a href="logout.php" class="btn btn-danger">Sign Out of Your Account</a></p>
</body>
</html>