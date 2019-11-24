<?php

include "shannon.php";
sleep(1); //wait for server
$threads = 16;
$curlHandles = array();

$infile = '../../assets/AMillionRandomDigits.bin';
$outfile = 'random.out';

$originalData = file_get_contents($infile);
$outputData = $originalData;

$seeds = file_get_contents("seeds");
if (strlen($seeds) > 0) {
  $seeds = json_decode($seeds);
}
else {
  $seeds = array();
}

$seed = 0;
$pass = 0;
$passes = array();
$targetImprovement = 0.001;


$startEntropy = get_shannon_entropy($originalData);
$bestEntropy = $startEntropy;

echo "Start entropy: $startEntropy\n";

echo "Calculating Pass: $pass\n";
while ($startEntropy - $bestEntropy < $targetImprovement) {

  // Do async stuff

  //Requests
  // Process-existing
  $processExistingSeed = false;
  if (sizeof($seeds) > 0) {
    $existingSeed = array_shift($seeds)[0];
    $processExistingSeed = true;
  }
  $multiHandle = curl_multi_init();
  $curlHandles = array();
  for ($i = 0; $i < $threads; $i++) {
    // Find the best seed
    if ($processExistingSeed) $seed = $existingSeed;
    $requestData = array(
      $seed,
      $bestEntropy,
      base64_encode($outputData),
    );

    $curlHandles[$i] = curl_init();
    curl_setopt($curlHandles[$i], CURLOPT_URL, "http://localhost:" . (9990 + $i));
    curl_setopt($curlHandles[$i], CURLOPT_HEADER, 0);
    curl_setopt($curlHandles[$i], CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curlHandles[$i], CURLOPT_POST, 1);
    curl_multi_add_handle($multiHandle, $curlHandles[$i]);
    curl_setopt($curlHandles[$i], CURLOPT_POSTFIELDS,
      "data=" . base64_encode(json_encode($requestData))
    );
    if (!$processExistingSeed) $seed ++;
  }

  do {
      $status = curl_multi_exec($multiHandle, $active);
      if ($active) {
          curl_multi_select($multiHandle);
      }
  } while ($active && $status == CURLM_OK);

  //Responses

  // Check the batch to see if there were any improvements
  // If there are we only accept the first sequential improvement as subsequent results may be false (as they rely on modified $outputData if there is an improvement)
  for ($i = 0; $i < $threads; $i++) {
    $response = json_decode(curl_multi_getcontent($curlHandles[$i]));
    if ($response[2] != "") {
      $seed = $response[0];
      $outputData = base64_decode($response[2]);
      $bestEntropy = $response[1];
      $passes[] = array($seed, $bestEntropy);
      $pass ++;
      echo "\nEntropy reduction: " . ($startEntropy - $bestEntropy) . " (seed $seed))\n";
      $seed ++;
      echo "Calculating Pass: $pass\n";
      file_put_contents($outfile, $outputData);
      file_put_contents("seeds", json_encode($passes));
      break;
    } else {
      echo ".";
    }
  }
  for ($i = 0; $i < $threads; $i++) {
    curl_multi_remove_handle($multiHandle, $curlHandles[$i]);
  }

  curl_multi_close($multiHandle);
}
echo "done";



echo "Finished. Total entropy reduction: " . ($startEntropy - $bestEntropy) . "\n";

