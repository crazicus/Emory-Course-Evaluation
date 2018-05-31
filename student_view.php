<?php 
	require_once 'config.php';
	session_start();
	if(!isset($_SESSION['studentID'])) {
		header("location: login.php");
	}

	if(isset($_POST['back'])) {
		unset($_POST['back']);
		unset($_POST['stats']);
		header("location: student.php");
	}

	$vals = explode("-", $_POST['stats']);
	$cname = $vals[0];
	$instr = $vals[1];

	unset($vals);

	$query = "SELECT CONCAT(fname, ' ', lname) 'name' FROM instructor WHERE instructorID='$instr'";
	$result = mysqli_query($conn, $query);
	$row = mysqli_fetch_assoc($result);
	$instr_name = $row['name'];

	$query = "CREATE VIEW view_courses AS SELECT courseID FROM course WHERE name='$cname' AND taughtBy='$instr'";
	mysqli_query($conn, $query);

	$query = "CREATE VIEW all_responses AS 
			  SELECT qID, sID, type, text, qResponse
			  FROM completes, view_courses, question
			  WHERE courseID = cID
			  AND qID = questionID";
	mysqli_query($conn, $query);

	$query = "SELECT qID, type, text FROM all_responses GROUP BY qID ORDER BY type, qID ASC";
	$questions = mysqli_query($conn, $query);

	$to_print = "";
	$count_ott = 0;
	$count_ag = 0;
	$count_mc = 0;
	while($row = mysqli_fetch_assoc($questions)) {
		$ID = $row['qID'];
		if ($row['type'] == '1-10') {
			if($count_ott == 0) { 
				$to_print .= "<br /><h4 class=\"explanation\"><small>The following questions were given a score on a scale of 1 to 10. ";
				$to_print .= "Here are their average scores:</small></h4><br />";
			}
			$query = "SELECT AVG(qResponse) 'Average'
					  FROM all_responses
					  WHERE qID = $ID 
					  GROUP BY qID;";
			$result = mysqli_query($conn, $query);
			$qrow = mysqli_fetch_assoc($result);
			$to_print .= "<h3>" . $row['text'] . "</h3><ul><li><h4><strong>Average response: " . 
						  round($qrow['Average'], 2) . "</strong></h4></li></ul>"; 
			$count_ott++;
		} else if ($row['type'] == 'a/g') {
			if($count_ag == 0) {
				$to_print .= "<br /><h4 class=\"explanation\"><small>The following questions were given a rating of favorability "
						  . "ranging from 'Strongly Disagree' to 'Strongly Agree'. Here are the percentages of "
						  . "students that responded 'Agree' or 'Strongly Agree' to the statements:</small></h4><br />";
			}
			$query = "SELECT text, (COUNT(sID) / (SELECT COUNT(sID) 
												  FROM all_responses 
												  WHERE qID = $ID 
												  GROUP BY qID) * 100) 'Percentage'
					  FROM all_responses
					  WHERE qID = $ID
					  AND qResponse LIKE '%agree'
					  GROUP BY qID";
			$result = mysqli_query($conn, $query);
			$qrow = mysqli_fetch_assoc($result);
			$percent = round($qrow['Percentage'], 2);
			$to_print .= "<h3>" . $row['text'] . "</h3><ul><li><h4><strong>$percent%</strong> of students " .
						 "<em>agree</em> with this statement.</h4></li></ul>";
			$count_ag++;
		} else if ($row['type'] == 'm/c') {
			if ($count_mc == 0) {
				$to_print .= "<br /><div><h4 class=\"explanation\"><small>The following questions are multiple choice and broken up into their responses. "
						  . "Here are the percentages of each choice for each question:</small></h4></div><br />";
			}
			$query = "SELECT choiceText FROM choice WHERE qID = $ID";
			$result = mysqli_query($conn, $query);
			$responses = array();
			while($qrow = mysqli_fetch_assoc($result)) {
				$responses[$qrow['choiceText']] = 0;
			}
			$to_print .= "<h3>" . $row['text'] . "</h3><ul><li><table><tbody>";
			$query = "SELECT q1.qID, qResponse, ((count / sum ) * 100) 'Percentage' FROM
			  		 (SELECT qID, text, qResponse, COUNT(sID) 'count'
			  		  FROM all_responses
					  WHERE qID = $ID
					  GROUP BY qID, qResponse) AS q1,
					 (SELECT qID, COUNT(sID) 'sum'
					  FROM all_responses
					  WHERE qID = $ID
					  GROUP BY qID) AS q2
					  WHERE q1.qID = q2.qID";
			$result = mysqli_query($conn, $query);
			while($qrow = mysqli_fetch_assoc($result)) {
				$responses[$qrow['qResponse']] = round($qrow['Percentage'], 2);
			}
			foreach ($responses as $key => $value) {
				$to_print .= "<tr><td><h4><strong>$key:</strong></h4></td><td><h4>$value%</h4></td></tr>";
			}
			$to_print .= "</tbody></table></li></ul>";
			$count_mc++;
		} else {
			continue;
		}
	}

	if($to_print == "") {
		$to_print = "<h3 class=\"small\">This class has not yet been evaluated. Please check again later.</h3>";
	}
	
	$query = "DROP VIEW all_responses";
	mysqli_query($conn, $query);
	$query = "DROP VIEW view_courses";
	mysqli_query($conn, $query);

	unset($query);
	unset($result);
	unset($row);
	unset($qrow);
	unset($ID);
	unset($percent);
	unset($count_ag);
	unset($count_mc);
	unset($count_ott);

	mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>View Stats</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body{ font: 14px sans-serif; width: 80%; margin: auto; }
        td { padding-right: 10px; }
        ul { list-style-type: none; }
        .explanation { border: solid 1px lightgray; border-radius: 10px; padding: 20px; }
    </style>
</head>
<body>
	<form action="" method="POST" style="float: right;">
		<input type="submit" name="back" value="Return to Home"
			   class="btn btn-primary">
	</form>
	<div class="page-header">
		<h1>View statistics for <?= $instr_name ?>'s <?= $cname ?> Class</h1>
	</div>
	<?= $to_print ?>
</body>
</html>