<?php
include "shannon.php";

sleep(2); //wait for thread servers to start
$threads = 6;
$curlHandles = array();

$infile = '../../assets/AMillionRandomDigits.bin';
$outfile = 'random.out';

//$originalData = substr(file_get_contents($infile),0,100);
$originalData = file_get_contents($infile);

$outputData = $originalData;

echo "Determining the best character: ";
$bestScore = 0;
$bestChr = null;
for ($i = 0; $i < 255; $i++) {
  $result = count_sequences($originalData, chr($i));
  if ($result['score'] > $bestScore) {
    $bestScore = $result['score'];
    $bestChr = $i;
  }
}
echo $bestChr . "\n";

$seeds = file_get_contents("seeds");
file_put_contents("seeds.backup", $seeds);
if (strlen($seeds) > 0) {
  $seeds = json_decode($seeds);
}
else {
  $seeds = array();
}

$seed = 0;
$pass = 0;
$passes = array();
$targetRatio = 0.1;

echo "StartScore: $bestScore\n";
echo "Start entropy: " . get_shannon_entropy($originalData) . "\n";

echo "Calculating Pass: $pass\n";
while (true) { // need to determine a stopping condition

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
  // Find the best seed from all threads
  for ($i = 0; $i < $threads; $i++) {
    
    if ($processExistingSeed) $seed = $existingSeed;
    $requestData = array(
      $seed,
      base64_encode($outputData),
      $bestChr
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
    if (!$processExistingSeed) {
      $seed ++;
      $resetSeed = 0;
    } else {
      $resetSeed = (int)file_get_contents("lastseed");
    }
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
    $response = curl_multi_getcontent($curlHandles[$i]);
    if ($response == "") die ("Thread $i returned empty string");
    $response = json_decode($response);
    if ($response[2] != "") {
      $seed = $response[0];
      $outputData = base64_decode($response[2]);
      $bestScore = $response[1];
      $passes[] = array($seed, $bestScore, get_shannon_entropy($outputData));
      $pass ++;
      echo "\nNew Score: " . $bestScore->score . " | seed $seed | entropy " . get_shannon_entropy($outputData) . "))\n";
      $seed = $resetSeed;
      echo "Calculating Pass: $pass\n";
      file_put_contents($outfile, $outputData);
      file_put_contents("seeds", json_encode($passes));
      break; // Skip all other threads to prevent operations on obsolete data
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



echo "Finished. Best Score: " . $bestScore['score'] . ")\n";

