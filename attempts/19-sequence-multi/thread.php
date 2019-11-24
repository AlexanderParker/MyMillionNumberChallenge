<?php

include "shannon.php";

$data = json_decode(base64_decode($_POST["data"]));

$seed = $data[0];
$testData = base64_decode($data[1]);
//$startEntropy = get_shannon_entropy($testData);

mt_srand($seed, MT_RAND_MT19937);
// Count initial score
$initialScore = count_sequences($testData, chr($data[2]));

// Generate xor character mask
for ($charIndex = 0; $charIndex < strlen($testData); $charIndex ++) {
  // mask randomly based on current sequences - chance reduces as total sequence length increases.
 // if (mt_rand(0, strlen($testData)) > $initialScore['total'] * $initialScore['total']) {
  if (mt_rand(0, 100) > 90) {
  	$newChar = mt_rand(0, 255);
    $testData[$charIndex] = $testData[$charIndex] ^ chr($newChar);
  } else {
  	// Skip this character
  }
}

// Score new data
$newScore = count_sequences($testData, chr($data[2]));

if ($newScore['max'] >= $initialScore['max'] && $newScore['total'] > $initialScore['total'] + ceil(strlen(decbin($seed))/8) + 1) {// && get_shannon_entropy($testData) < $startEntropy) {
	echo json_encode(array(
		$seed,
		$newScore,
		base64_encode($testData)
	));
} else {
	echo json_encode(array(
		$seed,
		$newScore,
		""
	));
	file_put_contents('lastseed', $seed);
}