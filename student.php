<?php
	require_once 'config.php';
	session_start();
	if(!isset($_SESSION['studentID']) || empty($_SESSION['studentID'])) {
		header("location: index.html");
	}

	if(isset($_SESSION['completed'])) {
		echo "<script>
		     	 alert(\"Course Evaluation for Course Number " . $_SESSION['completed'] ." completed.\");
			 </script>";
		unset($_SESSION['completed']);
	}

	if(isset($_SESSION['rejected'])) {
		echo "<script>
		     	 alert(\"Course Evaluation for Course Number " . $_SESSION['rejected'] . " already completed. Please choose a different course to evaluate.\");
			 </script>";
		unset($_SESSION['rejected']);
	}

	//
	// 	Query for enrolled classes of the student. 											   
	//
	$id = $_SESSION['studentID'];
	$query = "SELECT cID 'Number', name 'Name', section 'Section', semester 'Semester', 
					  year 'Year', CONCAT(fname, ' ', lname) 'Instructor'
			   FROM course, takes, instructor 
			   WHERE cID = courseID AND taughtBy = instructorID AND sID = '$id'";
	$result = mysqli_query($conn, $query);
	unset($query);
	$classes = "";
	$class_eval = "";
	$count = 0;
	while ($row = mysqli_fetch_assoc($result)) {
		$class_eval .= "<option value=\"" . $row['Number'] . "\">" . $row['Name'] . "</option>";
		if ($count == 0) {
    		$classes .= "<table class='table-bordered' style='margin: auto;'><thead><tr>";
    		foreach ($row as $key => $value) {
    			$classes .= "<th style='text-align: center; padding: 10px;'>" . $key . "</th>";
    		}
    		$classes .= "</thead></tr><tbody>";
		}
		$classes .= "<tr>";
		foreach ($row as $key => $value) {
			$classes .= "<td style='padding: 10px;'>" . $value . "</td>";
		}
		$classes .= "</tr>";
    	$count++;
	}
	$classes .= "</tbody></table>";
	unset($row);
	unset($count);
	unset($result);
	//
	// 	End query for enrolled classes of the student. 										   
	//

	//
	// 	Begin query for all instructors.													   
	//
	$query = "SELECT instructorID, CONCAT(fname, ' ', lname) AS 'Name' FROM instructor ORDER BY Name ASC";
	$instructors = "";
	$result = mysqli_query($conn, $query);
	unset($query);
	while($row = mysqli_fetch_assoc($result)) {
		$instructors .= "<option value=\"" . $row['instructorID'] . "\">" . $row['Name'] . "</option>";
	}
	unset($result);
	unset($row);
	//
	// 	End query for all instructors.														   
	//

	//
	// 	Begin query for all courses.														   
	//
	$query = "SELECT DISTINCT name 'Name' FROM course ORDER BY Name ASC";
	$course = "";
	$result = mysqli_query($conn, $query);
	unset($query);
	while($row = mysqli_fetch_assoc($result)) {
		$courses .= "<option value=\"" . $row['Name'] . "\">" . $row['Name'] . "</option>";
	}
	unset($result);
	unset($row);
	//
	// 	End query for all courses.															   
	//

	//
	// 	Begin query for classes to view stats on 											   
	//
	if(isset($_POST['send'])) {
		$cols = 'DISTINCT name "Name", CONCAT(fname, " ", lname) "Instructor", instructorID';
		$query = 'SELECT ' . $cols . ' FROM course, instructor WHERE taughtBy=instructorID';
		unset($cols);

		foreach ($_POST as $key => $value) {
			if($value != null && $key != "send") {
				$query .= " AND $key = '$value'";
			}
		}

		$query .= " ORDER BY Name ASC";
		$result = mysqli_query($conn, $query);
		if (!$result) {
			die("Error: " . mysqli_error($query) . "\tQuery: $query");
		}
		unset($query);

		$count = 0;
		$searched = "";
		while ($row = mysqli_fetch_assoc($result)) {
			$button_form = "<form action=\"student_view.php\" method=\"POST\">" . 
						   "<button class=\"btn btn-primary\" type=\"submit\" value=\"" . $row['Name'] . 
						   '-' . $row['instructorID'] . "\" name=\"stats\">View Stats</button></form>";
			if ($count == 0) {
	    		$searched .= "<table class='table-bordered' style='margin: auto;'><thead><tr>";
	    		foreach ($row as $key => $value) {
	    			if ($key != "instructorID") {
	    				$searched .= "<th style='text-align: center; padding: 10px;'>" . $key . "</th>";
	    			}
	    		}
	    		$searched .= "</tr></thead><tbody>";
			}
			$searched .= "<tr>";
			foreach ($row as $key => $value) {
				if ($key != "instructorID") {
					$searched .= "<td style='padding: 10px;'>" . $value . "</td>";
				}
			}
			$searched .= "<td style='padding: 10px;'>$button_form</td>";
			$searched .= "</tr>";
	    	$count++;
		}
		$searched .= "</tbody></table>";
		unset($row);
		unset($result);
		unset($count);
	}
	//
	// 	End query for classes to view stats on 											   	   	
	//
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
        <h1>Hi, <b>student</b>. Welcome to the Emory Course Evaluation System.</h1>
    </div>
    <div class="center-block">
    	<div id="enrolled classes">
    		<h3>My Classes</h3>
    		<?= $classes ?>
    		<br />
    		<form class="form-inline" action="evaluate.php" method="POST">
    			I want to evaluate my
    			<select class="form-control" name="courseID" required>
    				<option selected disabled hidden>Select</option>
    				<?= $class_eval ?>
    			</select>
    			class.
    			<input class="btn btn-primary" type="submit" name="eval" value="Evaluate">
    		</form>
    	</div>
    </div>
    <br />
    <br />
    <div class="center-block">
    	<div id="search">
    		<h3>View Class Statistics</h3>
    		<form class="form-inline" action="" method="POST">
    			Course Name:
    			<select class="form-control" name="name">
    				<option selected disabled hidden>Select</option>
    				<?= $courses ?>
    			</select>
    			Instructor:
    			<select class="form-control" name="instructorID">
    				<option selected disabled hidden>Select</option>
    				<?= $instructors ?>
    			</select>
      			<input class="btn btn-primary" type="submit" value="Search" name="send">
    		</form>
    	</div>
    	<br />
    	<div id="view stats" style="display: <?php if(isset($_POST['send'])) echo "block"; else echo "none";?>">
    		<h3>Search results:</h3>
    		<?= $searched ?>
    	</div> 
	</div>
    <a href="logout.php" class="btn btn-danger">Sign Out of Your Account</a>
</body>
</html>