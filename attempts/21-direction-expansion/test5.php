<?php

function expand($sequence, $even) {

	$token = $sequence[0];  // Track the current token
	$length = 0; // Count the length of the current token
	$expanded = "";

	for($i = 0; $i < strlen($sequence); $i++) {
		if ($token == $sequence[$i]) $length += 1;
		// Direction has changed		
		if ($token != $sequence[$i] || $length > 3 || $i == strlen($sequence) - 1) {
			switch ($length) {
				case 1:
					$expanded .= "01";				
					break;
				case 2:
					$expanded .= "10";
					break;
				case 3:
					//if (!$even) {
						$expanded .= "11";
					//}
					//else {
					//	$expanded .= "100001";
					//}
					break;
				case $length > 3:
					$expanded .= "1000"; // Simulate a direction change to zero then back
					break;				
			}
			if ($i < strlen($sequence) - 1) {
				// Reset length counter
				$length = 0;
				// Track the new token
				$token = $sequence[$i];
				$i -= 1;
			}
		}
	}
	return $expanded;
}

// Rotate the sequence by 1 by moving last bit to start
function rotate($sequence, $count) {
	// Number of chars to rotate	
	return substr($sequence, strlen($sequence) - $count) . substr($sequence, 0, strlen($sequence) - $count);
}

// reverse the direction of every group of 3 characters (except last group if < 3 length)
function flip($sequence, $length) {
	$flipped = "";
	$finished = false;
	$odd = true;
	$i = 0;
	while ($finished == false) {		
		if ($i + $length > strlen($sequence)) {
			$flipped .= substr($sequence, $i);
			$finished = true;
		}
		else {
			//if (!$odd) {
				$flipped .= strrev(substr($sequence, $i, $length));
			//}
			//else {
//				$flipped .= substr($sequence, $i, $length);
			//}
			$i += $length;
		}
	}
	return $flipped;
}

// determine steps to recreate the sequence using patterns of defined length
function getPatternSteps($sequence, $length) {	
	$finished = false;
	$i = 0;
	$steps = array();	
	while ($finished == false) {
		if ($i + $length >= strlen($sequence)) {
			$pattern = substr($sequence, $i);
			if ($pattern != "")	array_push($steps, array("remainder" => $pattern));
			$finished = true;
		}
		else {
			// Figure out length of current pattern
			$pattern = substr($sequence, $i, $length);
			$count = 1;
			$endCount = false;
			while (!$endCount) {
				$nextPattern = substr($sequence, $i + $count * $length, $length);
				if ($nextPattern == $pattern) {
					$count ++;
				}
				else {
					array_push($steps, array($pattern,$count));
					$i += $count * $length;
					$endCount = true;
				}
			}
		}
	}
	return $steps;
}

$iterations = 1;

$sequence = "000110111001101100111111";

// $sequence = "0001101110011011001111110000100001111111010100010101010110011111111010000001000011001011100010010100101000001000010010100000010001001011101101000011101010000010001100011010001101010110101001010101110001001100110101100101001100110111110101010001111010111111";

$patternLength = 2;

$initialSteps = getPatternSteps($sequence, $patternLength);
$bestComplexity = count($initialSteps);
$bestSteps = null;
$bestStepIteration = 0;

for ($i = 0; $i < $iterations; $i ++) {

	$sequence = expand($sequence, $i % 2 == 0);
	echo $i . " " . strlen($sequence) . "\n";
	// See if the pattern repeats indefinitely
	$pattern = substr($sequence, 0, 2);
	$patternCount = substr_count($sequence, $pattern);
	echo "$patternCount / " . strlen($sequence) / 2 . " (" . $patternCount/(strlen($sequence) / 2) . " | " . ((strlen($sequence) / 2) - $patternCount) . ")\n";
	$steps = getPatternSteps($sequence, $patternLength);
	echo "Complexity: " . count($steps) . "\n";
	if (count($steps) < $bestComplexity) {
		$bestSteps = $steps;
		$bestComplexity = count($steps);
		$bestStepIteration = $i;
	}
	if ($patternCount == strlen($sequence) / 2) {
		file_put_contents("output.file", "$i $pattern " . strlen($sequence));
		die ("Full sequence reached");		
	}
	//$count = floor(strlen($sequence) / ($i + 1));
	$sequence = rotate(flip($sequence, 3), 4);
}

echo "Initial Complexity: " . count($initialSteps) . "\n";

if ($bestSteps != null) {
	echo "Best Complexity: " . $bestComplexity . "\n";
	$data = "$bestStepIteration";
	foreach ($bestSteps as $step) {
		$data .= " " . implode($step, "");
	}
	file_put_contents("output.json", $data);
}
//echo $sequence . "\n";