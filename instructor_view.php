<?php 
	require_once 'config.php';
	session_start();
	if(!isset($_SESSION['instructorID'])) {
		header("location: login.php");
	}

	if(isset($_POST['back'])) {
		unset($_POST['back']);
		unset($_POST['stats']);
		header("location: instructor.php");
	}

	function median($arr) {
	    $count = count($arr); 
	    $mid = floor(($count-1)/2); 
	    if($count % 2) { 
	        $median = $arr[$mid];
	    } else { 
	        $l = $arr[$mid];
	        $h = $arr[$mid+1];
	        $median = (($l+$h)/2);
	    }
	    return $median;
	}

	$vals = explode('-', $_POST['stats']);
	$cname = $vals[0];
	$semester = $vals[1];
	$year = $vals[2];
	$instr = $_SESSION['instructorID'];

	unset($vals);

	$query = "CREATE VIEW view_courses AS 
			  SELECT courseID 
			  FROM course 
			  WHERE name='$cname' 
			  AND semester = '$semester'
			  AND year = '$year'
			  AND taughtBy='$instr';";
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
	$count_open = 0;
	while($row = mysqli_fetch_assoc($questions)) {
		$ID = $row['qID'];
		if ($row['type'] == '1-10') {
			if($count_ott == 0) { 
				$to_print .= "<br /><h4 class=\"explanation\"><small>The following questions were given a score on a scale of 1 to 10. ";
				$to_print .= "Here are their numerical breakdowns:</small></h4><br />";
			}
			$to_print .= "<h3>" . $row['text'] . "</h3><ul>";
			$query = "SELECT qResponse, COUNT(sID) 'Count'
					  FROM all_responses
					  WHERE qID = $ID 
					  GROUP BY qID, qResponse;";
			$result = mysqli_query($conn, $query);
			$responses = array(); 
			$options = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0,
							 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0);
			while($qrow = mysqli_fetch_assoc($result)) {
				$options[$qrow['qResponse']] = $qrow['Count'];
				for($i=0; $i < $qrow['Count']; $i++) {
					$responses[] = $qrow['qResponse'];
				}
			}
			$to_print .= "<li><table><tbody>";
			foreach ($options as $key => $value) {
				$to_print .= "<tr><td><h4>$key:</h4></td><td><h4>$value response(s)</h4></td></tr>";
			}
			$to_print .= "</tbody></table></li>";
			$to_print .= "<br /><li><h4><strong>The median response is: " . median($responses) . "</strong></h4></li></ul>";
			$count_ott++;
		} else if ($row['type'] == 'a/g') {
			if($count_ag == 0) {
				$to_print .= "<br /><h4 class=\"explanation\"><small>The following questions were given a rating of favorability "
						  . "ranging from 'Strongly Disagree' to 'Strongly Agree'. Here are the breakdowns of "
						  . "each response to every question:</small></h4><br />";
			}
			$to_print .= "<h3>" . $row['text'] . "</h3><ul>";
			$to_print .= "<li><table><tbody>";
			$query = "SELECT qResponse, COUNT(sID) 'Count'
					  FROM all_responses
					  WHERE qID = $ID
					  GROUP BY qResponse";
			$result = mysqli_query($conn, $query);
			$responses = array('Strongly Disagree' => 0, 
							   'Disagree' => 0, 
							   'Neutral' => 0, 
							   'Agree' => 0, 
							   'Strongly Agree' => 0);
			while($qrow = mysqli_fetch_assoc($result)) {
				$responses[$qrow['qResponse']] = $qrow['Count'];
			}
			foreach ($responses as $key => $value) {
				$to_print .= "<tr><td><h4>$key:</h4></td><td><h4>$value response(s)</h4></td></tr>";
			}
			$to_print .= "</tbody></table></ul>";
			$count_ag++;
		} else if ($row['type'] == 'm/c') {
			if ($count_mc == 0) {
				$to_print .= "<br /><div><h4 class=\"explanation\"><small>The following questions are multiple choice and broken up into their responses. "
						  . "Here are the counts of each choice for each question:</small></h4></div><br />";
			}
			$to_print .= "<h3>" . $row['text'] . "</h3><ul>";
			$to_print .= "<li><table><tbody>";
			$query = "SELECT choiceText FROM choice WHERE qID = $ID;";
			$result = mysqli_query($conn, $query);
			$responses = array();
			while($qrow = mysqli_fetch_assoc($result)) {
				$responses[$qrow['choiceText']] = 0;
			}
			$query = "SELECT qResponse, COUNT(sID) 'Count'
			  		  FROM all_responses
					  WHERE qID = $ID
					  GROUP BY qID, qResponse;";
			$result = mysqli_query($conn, $query);
			while($qrow = mysqli_fetch_assoc($result)) {
				$responses[$qrow['qResponse']] = $qrow['Count'];
			}
			foreach ($responses as $key => $value) {
				$to_print .= "<tr><td><h4>$key:</h4></td><td><h4>$value response(s)</h4></td></tr>";
			}
			$to_print .= "</tbody></table></ul>";
			$count_mc++;
		} else {
			if($count_open == 0) { 
				$to_print .= "<br /><h4 class=\"explanation\"><small>The following questions are open-ended. ";
				$to_print .= "Here are the responses for each question:</small></h4><br />";
			}
			$to_print .= "<h3>" . $row['text'] . "</h3>";
			$query = "SELECT qResponse
					  FROM all_responses
					  WHERE qID = $ID;";
			$result = mysqli_query($conn, $query);
			while ($qrow = mysqli_fetch_assoc($result)) {
				$to_print .= "<blockquote class=\"blockquote\"><p>" . $qrow['qResponse'] . "</p></blockquote>";
			}
			$count_open++;
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
        body { font: 14px sans-serif; width: 80%; margin: auto; }
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
		<h1>View statistics for your <?= "$semester $year $cname" ?> Class</h1>
	</div>
	<?= $to_print ?>
</body>
</html>