<?php

$infile = '../../assets/AMillionRandomDigits.bin';
$outfile = 'random.out';

$originalData = file_get_contents($infile);
$outputData = $originalData;

$seed = 0;
$pass = 0;
$passes = array();
$targetImprovement = 0.001;

function get_shannon_entropy($data) {
  $map = array();
  $string_array = str_split($data);
  foreach ($string_array as $char) {
      if (!array_key_exists($char, $map)) {
        $map[$char] = 1;
      }
      else {
        $map[$char] += 1;
      }
  }
  $result = 0.0;
  $len = strlen($data);
  foreach ($map as $item) {
      $frequency = $item / $len;      
      $result -= $frequency * (log($frequency, 256));
  }

  return $result;
}

$startEntropy = get_shannon_entropy($originalData);
$bestEntropy = $startEntropy;

echo "Start entropy: $startEntropy\n";

$bestPassFound = true;
echo "Calculating Pass: $pass\n";
while ($startEntropy - $bestEntropy < $targetImprovement) {
  $bestPassFound = false;
  // Find the best seed
  mt_srand($seed, MT_RAND_MT19937);
  $seed ++;
  $testData = $outputData;
  $xorString = "";
  // Generate xor character mask
  for ($charIndex = 0; $charIndex < strlen($testData); $charIndex ++) {
    $nextChar = mt_rand(0, 255);
    // mask randomly based on current entropy - chance to mask reduces as entropy reduces
 
    if ($nextChar / 255 > $bestEntropy )   {
      $testData[$charIndex] = $testData[$charIndex] ^ chr($nextChar);
    }
  }
  $testEntropy = get_shannon_entropy($testData);
  if ($testEntropy < $bestEntropy) {
    $passes[] = array($seed, $bestEntropy);
    $outputData = $testData;
    $bestEntropy = $testEntropy;
    echo "\nEntropy reduction: " . ($startEntropy - $bestEntropy) . " (seed $seed))\n";
    $pass ++;
    $seed = 0;
    echo "Calculating Pass: $pass\n";
  } else {
    echo ".";
  }
}

echo "Finished. Total entropy reduction: " . ($startEntropy - $bestEntropy) . "\n";

file_put_contents($outfile, $outputData);
file_put_contents("seeds", json_encode($passes));