<?php

function expand($sequence) {

	$token = $sequence[0];  // Track the current token
	$length = 0; // Count the length of the current token
	$expanded = "";

	for($i = 0; $i < strlen($sequence); $i++) {
		if ($token == $sequence[$i]) $length += 1;
		// Direction has changed		
		if ($token != $sequence[$i] || $length > 2 || $i == strlen($sequence) - 1) {
//			var_dump($length);

			switch ($length) {
				case 1:
					$expanded .= "01";				
					break;
				case 2:
					$expanded .= "10";
					break;
				case $length > 2:
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
function rotate($sequence) {
	return $sequence[strlen($sequence) - 1] . substr($sequence, 0, strlen($sequence) - 1);
}

$iterations = 10;

$sequence = "000110111001101100111111";

for ($i = 0; $i < $iterations; $i ++) {
	$sequence = expand($sequence);
	echo $i . " " . strlen($sequence) . "\n";
	// See if the pattern repeats indefinitely
	$pattern = substr($sequence, 0, 2);
	echo $pattern . "\n";
	$patternCount = substr_count($sequence, $pattern);
	echo "$patternCount / " . strlen($sequence) / 2 . " (" . $patternCount/(strlen($sequence) / 2) . ")\n";
	//$sequence = rotate($sequence);
}

echo $sequence . "\n";