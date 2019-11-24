<?php

function expand($sequence, $even) {

	$token = $sequence[0];  // Track the current token
	$length = 0; // Count the length of the current token
	$expanded = "";

	for($i = 0; $i < strlen($sequence); $i++) {
		if ($token == $sequence[$i]) $length += 1;
		// Direction has changed		
		if ($token != $sequence[$i] || $length > 3 || $i == strlen($sequence) - 1) {
//			var_dump($length);

			switch ($length) {
				case 1:
					$expanded .= "01";				
					break;
				case 2:
					$expanded .= "10";
					break;
				case 3:
					//if ($even) {
						$expanded .= "11";
					//}
					//else {
					//	$expanded .= "100001";
					//}
					break;
				case $length > 3:
					$expanded .= "1100"; // Simulate a direction change to zero then back
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
function rotate($sequence) {
	// Number of chars to rotate
	$count = floor(strlen($sequence) / 3);
	var_dump($count);
	return substr($sequence, strlen($sequence) - $count) . substr($sequence, 0, strlen($sequence) - $count);
}

// reverse the direction of every group of 3 characters (except last group if < 3 length)
function flip($sequence) {
	$flipped = "";
	$finished = false;
	$i = 0;
	while ($finished == false) {
		if ($i + 3 > strlen($sequence)) {
			$flipped .= substr($sequence, $i);
			$finished = true;
		}
		else {
			$flipped .= strrev(substr($sequence, $i, 3));
			$i += 3;
		}
	}
	return $flipped;
}

$iterations = 50;

$sequence = "000110111001101100111111";

for ($i = 0; $i < $iterations; $i ++) {

	$sequence = expand($sequence, $i % 2 == 0);
	echo $i . " " . strlen($sequence) . "\n";
	// See if the pattern repeats indefinitely
	$pattern = substr($sequence, 0, 2);
	echo $pattern . "\n";
	$patternCount = substr_count($sequence, $pattern);
	echo "$patternCount / " . strlen($sequence) / 2 . " (" . $patternCount/(strlen($sequence) / 2) . " | " . ((strlen($sequence) / 2) - $patternCount) . ")\n";
	if ($patternCount == strlen($sequence) / 2) {
		file_put_contents("output.file", "$i $pattern " . strlen($sequence));
		die ("Full sequence reached");		
	}
	$sequence = rotate(($sequence));
}

//echo $sequence . "\n";