<?php
	require_once 'config.php';
	session_start();

	if(!isset($_SESSION['studentID']) || empty($_SESSION['studentID'])) {
		header("location: login.php");
		exit;
	}

	if(!isset($_POST['eval']) || empty($_POST['courseID'])) {
		header("location: student.php");
	}

	if(isset($_POST['back'])) {
		unset($_POST['back']);
		unset($_POST['eval']);
		header("location: student.php");
	}

	$studentID = $_SESSION['studentID'];
	$courseID = $_POST['courseID'];
	$_SESSION['courseID'] = $courseID;

	$has_completed = "SELECT * FROM completes WHERE cID=$courseID AND sID='$studentID'";
	$query = "SELECT name, semester, year FROM course WHERE courseID='" . $courseID . "'";
	$form_query = "SELECT * FROM evaluation, question WHERE qID = questionID AND cID = $courseID";
	$choice_query = "SELECT * FROM choice";

	$hc = mysqli_query($conn, $has_completed);
	if(!$hc) {
		die("Error: " . mysqli_error($conn));
	}

	if(mysqli_num_rows($hc) > 0) {
		$_SESSION['rejected'] = $courseID;
		header("location: student.php");
	}

	if(!($cn = mysqli_query($conn, $query))) {
		die("Error: " . mysqli_error($conn));
	} else {
		$cn = mysqli_query($conn, $query);
	}

	$getname = mysqli_fetch_assoc($cn);
	$cname = $getname['name'];
	$semester = $getname['semester'];
	$year = $getname['year'];

	if(!($questions = mysqli_query($conn, $form_query))) {
		die("Error: " . mysqli_error($conn));
	} else {
		$questions = mysqli_query($conn, $form_query);
	} 

	if(!($choices = mysqli_query($conn, $choice_query))) {
		die("Error: " . mysqli_error($conn));
	} else {
		$choices = mysqli_query($conn, $choice_query);
	} 

	$crows = array();
	while ($crow = mysqli_fetch_assoc($choices)) {
		$crows[] = $crow;
	}
	unset($crow);

	$eval_form = "<form action=\"submit.php\" method=\"POST\">";
	$count = 0; 
	while ($qrow = mysqli_fetch_assoc($questions)) {
		$eval_form .= "<h3>" . $qrow['text'] . "</h3>";
		if ($qrow['type'] == "m/c") {
			$mcc = 0;
			$eval_form .= "<ul>";
			foreach($crows as $crow) {
				if($crow['qID'] == $qrow['qID']) {
					$eval_form .= "<li><div class=\"radio-inline\">";
					$eval_form .= "<label><input type=\"radio\" name=\"" . $qrow['qID'] . 
								   "\" value=\"" . $crow['choiceText'] . "\" id=\"$mcc\" required>" . 
								   $crow['choiceText'] . "</label>";
					$mcc++;
					$eval_form .= "</div></li>";
				} 
			}
			$eval_form .= "</ul>";
		} else if ($qrow['type'] == "1-10") {
			$ott = "<div><h4 style=\"float: left;\">Not at all</h4><h4 style=\"float: right;\">Very much</h4></div>";
			$eval_form .= "<ul><li>$ott<table class=\"table\"><tbody><tr>";
			for ($i=1; $i <= 10; $i++) { 
				$eval_form .= "<td><label><input type=\"radio\" name=\"" . $qrow['qID']
							   . "\" value=\"$i\" id=\"$i\" required>$i</label></td>";
			}
			$eval_form .= "</tr></tbody></table></li></ul>"; 
		} else if ($qrow['type'] == "a/g") {
			$eval_form .= "<ul><li><table class=\"table\"><tbody><tr>";
			$ag = array('Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree');
			for ($i=0; $i < 5; $i++) { 
				$eval_form .= "<td><label><input type=\"radio\" name=\"" . $qrow['qID'] . "\" value=\"" 
							   . $ag[$i] . "\" id=\"$i\" required>" . $ag[$i] . "</label></td>";
			}
			$eval_form .= "</tr></tbody></table></li></ul>";
		} else {
			$eval_form .= "<textarea name=\"" . $qrow['qID'] . 
						  "\" placeholder=\"Write something here...\" class=\"form-control\" rows=3>" . 
						  "</textarea><br />";
		}
	} 
	unset($qrow);
	$eval_form .= "<input class=\"btn btn-primary btn-block\" type=\"submit\" name=\"complete\" value=\"Evaluate\"></form";
	
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Evaluate <?= $cname ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body { font: 14px sans-serif; width: 80%; margin: auto; }
        form { margin: 40px auto 40px auto;}
        label { font-weight: normal; margin: 10px; font-size: 16px;}
        td { padding-right: 10px; }
        ul { list-style-type: none; }
        .explanation { border: solid 1px lightgray; border-radius: 10px; padding: 20px; }
        #back { float: right; }
    </style>
</head>
<body>
	<div class="page-header">
	<form action="" method="POST">
		<h1>Evaluate your <?= "$semester $year $cname" ?> Class
		<input type="submit" name="back" value="Return to Home"
			   class="btn btn-primary" id="back"/>
	</h1></form>
	</div>
	<br />
	<?= $eval_form ?>
</body>
</html>