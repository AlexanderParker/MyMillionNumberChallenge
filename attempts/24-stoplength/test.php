<?php

define("DEBUG", false);

$testRange = 12;

// Analyse a binary string
function analyse($input) {
	$value = bindec($input);
	$bitLength = strlen($input);
	$firstBit = $input[strlen($input) - 1];
	$step = 0;
	$stopLength = 0;
	$finalAdd = 0;
	$countAdd = 0;
	$previousAdd = 0;
	$addPrevious = false;
	while ($value > 1) {
		$stopLength ++;
		if ($value % 2 != 0) {
			$value += 1;
			$previousAdd = $finalAdd;
			$finalAdd = $stopLength;
			$countAdd ++;		
		}
		$value = $value / 2;
	}
	return array(
		"input" => $input,
		"value" => $value,
		"bitlength" => $bitLength,
		"firstbit" => $firstBit,
		"stoplength" => $stopLength,
		"finaladd" => $finalAdd,
		"previousadd" => $previousAdd,
		"countadd" => $countAdd
	);
}

function reverse($input) {

	$initialAnalysis = analyse($input);

	if (DEBUG) echo "Input analysis:\n\n";

	if (DEBUG) var_dump($initialAnalysis);

	// Reversal

	$minVal = str_pad("1", $initialAnalysis["bitlength"], "0", STR_PAD_RIGHT);
	$maxVal = str_pad("1", $initialAnalysis["bitlength"], "1", STR_PAD_RIGHT);


	if (DEBUG) echo "Generating potential matches:\n\n";
	$candidates = array();

	for ($i = bindec($minVal); $i <= bindec($maxVal); $i++) {
		if (DEBUG) echo ($i) . " ";
		if (DEBUG) echo decbin($i) . "\n";
		$analysis = analyse(decbin($i));
		array_push($candidates, $analysis);
	}

	if (DEBUG) var_dump($candidates);

	// Exclude non-matching first bit

	if (DEBUG) echo "Excluding non-matching first bit:\n\n";

	foreach ($candidates as $index => $candidate) {
		if ($candidate['firstbit'] != $initialAnalysis['firstbit']) unset($candidates[$index]);
	}
	if (DEBUG) var_dump($candidates);


	// Exclude non-matching stop length

	if (DEBUG) echo "Excluding non-matching stop length:\n\n";

	foreach ($candidates as $index => $candidate) {
        	if ($candidate['stoplength'] != $initialAnalysis['stoplength']) unset($candidates[$index]);
	}
	if (DEBUG) var_dump($candidates);


	// Exclude non-matching final add step

	if (DEBUG) echo "Excluding non-matching final add step:\n\n";

	foreach ($candidates as $index => $candidate) {
        	if ($candidate['finaladd'] != $initialAnalysis['finaladd']) unset($candidates[$index]);
	}
	if (DEBUG) var_dump($candidates);


	// Exclude non-matching previous add step

        if (DEBUG) echo "Excluding non-matching previous add step:\n\n";

        foreach ($candidates as $index => $candidate) {
                if ($candidate['previousadd'] != $initialAnalysis['previousadd'] && $candidate['previousadd'] != 0) unset($candidates[$index]);
        }
        if (DEBUG) var_dump($candidates);

	

	// Exclude non-matching add count

	if (DEBUG) echo "Excluding non-matching add count:\n\n";

	foreach ($candidates as $index => $candidate) {
        	if ($candidate['countadd'] != $initialAnalysis['countadd']) unset($candidates[$index]);
	}
	if (DEBUG) var_dump($candidates);


	// Final output

	if (DEBUG) echo "Original value: $input\n";
	if (DEBUG) echo "Candidates: ";

	if (DEBUG) foreach ($candidates as $candidate) {
		echo $candidate['input'] . " ";
	}

	if (DEBUG) echo "\n";

	return count($candidates);
}

$maxbits = 0;
$maxvalue = 0;
$bestCandidate = array();
$countMatches = 0;

$rangeMin = str_pad("1", $testRange, "0", STR_PAD_RIGHT);
$rangeMax = str_pad("1", $testRange, "1", STR_PAD_RIGHT);

for ($i = bindec($rangeMin); $i <= bindec($rangeMax); $i ++) {
	$input = decbin($i);
	$count = reverse($input);
	if ($count == 1) {
		$maxbits = strlen($input);
		$maxvalue = $i;
		$bestCandidate = analyse($input);
		$countMatches ++;
		echo "$i: $input\n";
	}
}

$coverage = $countMatches / (bindec($rangeMax) - bindec($rangeMin) + 1);

echo "Max bits $maxbits value: $maxvalue count $countMatches coverage $coverage\n\n";
var_dump($bestCandidate);

