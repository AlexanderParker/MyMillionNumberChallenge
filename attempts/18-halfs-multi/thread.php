<?php

include "shannon.php";

$data = json_decode(base64_decode($_POST["data"]));

$seed = $data[0];
$testData = base64_decode($data[1]);
$pass = $data[2];
$startEntropy = get_shannon_entropy($testData);

file_put_contents('test',$pass);

mt_srand($seed, MT_RAND_MT19937);
// Count initial zeroes
$initialZeroCount = 0;
for ($i = 0; $i < strlen($testData); $i ++) {
      if (ord($testData[$i]) < 128) {
      $initialZeroCount ++;
    }
}
$newZeroCount = 0;
// Generate xor character mask
for ($charIndex = 0; $charIndex < strlen($testData); $charIndex ++) {
  // mask randomly based on current zero count - chance reduces as number of zeroes increases.
  if (mt_rand(0, 127) > $pass) {
  	$newChar = mt_rand(0, 255);
    $testData[$charIndex] = $testData[$charIndex] ^ chr($newChar);
  }
	if (ord($testData[$charIndex]) < 128) {
	  $newZeroCount ++;
	}
}

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