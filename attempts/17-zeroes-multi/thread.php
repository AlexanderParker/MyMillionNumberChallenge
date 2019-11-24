<?php

include "shannon.php";

$data = json_decode(base64_decode($_POST["data"]));

$seed = $data[0];
$testData = base64_decode($data[1]);
$startEntropy = get_shannon_entropy($testData);

mt_srand($seed, MT_RAND_MT19937);
// Count initial zeroes
$initialZeroCount = substr_count($testData, chr(0));

// Generate xor character mask
for ($charIndex = 0; $charIndex < strlen($testData); $charIndex ++) {
  // mask randomly based on current zero count - chance reduces as number of zeroes increases.
  if (mt_rand(0, strlen($testData)) > $initialZeroCount) {
  	$newChar = mt_rand(0, 255);
    $testData[$charIndex] = $testData[$charIndex] ^ chr($newChar);
  }
}

// Count new zeroes
$newZeroCount = substr_count($testData, chr(0));

if ($newZeroCount > $initialZeroCount && get_shannon_entropy($testData) < $startEntropy) {
	echo json_encode(array(
		$seed,
		$newZeroCount,
		base64_encode($testData)
	));
} else {
	echo json_encode(array(
		$seed,
		$newZeroCount,
		""
	));
	file_put_contents('lastseed', $seed);
}