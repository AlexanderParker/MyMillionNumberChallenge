<?php

include "shannon.php";

$data = json_decode(base64_decode($_POST["data"]));

$seed = $data[0];

$bestEntropy = (float)$data[1];
$testData = base64_decode($data[2]);

mt_srand($seed, MT_RAND_MT19937);

// Generate xor character mask
for ($charIndex = 0; $charIndex < strlen($testData); $charIndex ++) {
  $nextChar = mt_rand(0, 255);
  // mask randomly based on current entropy - chance to mask reduces as entropy reduces
  if ($nextChar / 255 < $bestEntropy )   {
    $testData[$charIndex] = $testData[$charIndex] ^ chr($nextChar);
  }
}
$testEntropy = get_shannon_entropy($testData);
if ($testEntropy < $bestEntropy) {
	echo json_encode(array(
		$seed,
		$testEntropy,
		base64_encode($testData)
	));
} else {
	echo json_encode(array(
		$seed,
		$testEntropy,
		""
	));
	file_put_contents('lastseed', $seed);
}