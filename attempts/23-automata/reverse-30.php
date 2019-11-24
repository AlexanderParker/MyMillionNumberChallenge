<?php

// Put your rule 30 row here. You can also just jam random bits. Yeah this is a character array, just pretend the 1's and 0's are binary ok? This isn't optimised.

$data = "11011110110011100010110000110011100000011000010101100100011000010000000101111100111001001110111";

// Compute the inverse of elementary automata rule 30

function reverse($data) {		
	$rules = array(
		"111" => "0",
		"110" => "0",
		"101" => "0",
		"100" => "1",
		"011" => "1",
		"010" => "1",
		"001" => "1",
		"000" => "0"
	);

	// This array starts with each of the rules, and is whittled down until only one possible sequence matches.
	// We build a number of potential ancestor strings from the rules, which become possible matches to the actual previous row.
	$possibleMatches = array();

	// We step through each character in our row, to evaluate which ancestor rules it matches with
	for ($i = 0; $i < strlen((string)$data); $i ++) {

		// We are going to compare the possible matches so far with the current rules which match.
		$newMatches = array();

		// We just want to check every rule to see if it fits into any of the possible match strings so far.		
		foreach ($rules as $rule => $value) {

			// Does the data row's input character equal the rule's output character?			
			if ((string)$value == ((string)$data[$i])) {

				// Only on the first step, push each rule into the array or possible - gotta start somewhere, and for the first character, every ancestor rule could apply.
				if ($i == 0) {
					array_push($newMatches, substr((string)$rule, 1, 2));
				} 

				else {
					foreach($possibleMatches as $part) {
						if (
								(isset(((string)$part)[$i]) && ((string)$rule)[1] == ((string)$part)[$i]) 	// Does the rule's middle character match the current end of the possible match?
								&& ((string)$rule)[0] == ((string)$part)[$i-1]								// And does the rule's first character match with the second last character of the possible match?
																											// (essentially - does the rule line up with any of the current possible strings?)
						) {
								// Generate the next possible string by adding the right-most character of the rule-part to one of the possible matches
								$newData = (string)$part . ((string)$rule)[2];

								// Just a quick hack to force the rightmost overflow bit to be a zero
								if ($i + 1 >= strlen($data) - 1) {
									if (((string)$rule)[2] != "0") $newData = "";
								} 

								// Add our new string to the array of possible matches
								if ($newData != "") array_push($newMatches, $newData);
						}
					}
				}
			}
		}
		// (we could probably unset non matching strings above instead of reassigning here)
		$possibleMatches = $newMatches;	
	}
	return substr($possibleMatches[0], 0, strlen($data));
}

while (true) {
	echo $data . "\n";
	if ($data == str_pad("0", "0", strlen($data))) die;
	$data = reverse($data);
}