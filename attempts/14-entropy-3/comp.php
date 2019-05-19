<?php

$infile = '../../assets/AMillionRandomDigits.bin';
$outfile = 'random.out';

$originalData = file_get_contents($infile);
$outputData = $originalData;

$seed = 0;
$pass = 0;
$passes = array();
$maxPasses = 1000;
$seedsPerPass = 1000;

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

while ($pass < $maxPasses && $bestPassFound == true) {
  $pass ++;
  echo "Pass $pass\n";
  $bestPassEntropy = $bestEntropy;
  $bestPassSeed = $seed;
  $bestData = $outputData;
  $bestPassFound = false;
  // Find the best pass
  for ($i = 0; $i < $seedsPerPass; $i++) {

    echo "$i/$seedsPerPass - ";
    mt_srand($seed, MT_RAND_MT19937);
    $testData = $outputData;
    $xorString = "";
    for ($charIndex = 0; $charIndex < strlen($testData); $charIndex ++) {
      $xorString .= chr(mt_rand(0, 255));
    }
    $testData = $testData ^ $xorString;
    $testEntropy = get_shannon_entropy($testData);
    if ($testEntropy < $bestPassEntropy) {
      $bestPassSeed = $seed;
      $bestPassEntropy = $testEntropy;
      $bestData = $testData;
      echo $bestPassEntropy . "\n";
      $bestPassFound = true;
    }
    $seed ++;
  }
  if ($bestPassFound) {
    $passes[] = $bestPassSeed;
    $seed = $bestPassSeed;
    $outputData = $bestData;
    echo "\nBest seed of pass $pass - $bestPassSeed ($bestPassEntropy)\n";
    $bestEntropy = $bestPassEntropy;
  } else {
    echo "\nNo further improvement\n";
  }
}

echo "Entropy improvement: " . $startEntropy . " / " . $bestEntropy . "\n";

file_put_contents($outfile, $outputData);
file_put_contents("seeds", json_encode($passes));