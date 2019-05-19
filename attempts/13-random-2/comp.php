<?php

// Build an index of 2-byte pairs representing random seeds.
// The interpreter can then walk through the file from start to finish, reading the 2 byte seeds and replacing them with the result.

$index = array();
$seedBase = 0;
$offset = 0;
$efficiency = 0;
$seedLength = 2; // Length of seed in bytes

$infile = '../../assets/AMillionRandomDigits.bin';
$outfile = 'output.out';

$original_data = file_get_contents($infile);

// Start at position zero, and work up until the end of the file.
while (strlen($original_data) > $offset) {
  // Find the longest random sequence matching the next portion of the file in the current 2 byte seed chunk
  $sequenceMaxLength = 0;
  $sequenceMaxSeed = 0;
  for ($nextSeed = 0; $nextSeed < pow(255, $seedLength); $nextSeed++) {
    mt_srand($nextSeed + $seedBase, MT_RAND_MT19937);
    $matchCount = 0;
    $breakMatching = false;
    while(!$breakMatching) {
      $nextByte = chr(mt_rand(0, 255));
      if ($nextByte === $original_data[$matchCount + $offset]) {
        $matchCount ++;
      } 
      else {
        if ($matchCount > $sequenceMaxLength) {
          $sequenceMaxLength = $matchCount;
          $sequenceMaxSeed = $nextSeed;
        }
        $breakMatching = true;
      }
    }
  }

  $index[] = $sequenceMaxSeed;
  $offset += $seedLength;
  $seedBase += $sequenceMaxSeed;
  $efficiency += $sequenceMaxLength - $seedLength;
  echo ("Chunk " . $offset / $seedLength . "/" . strlen($original_data) / $seedLength . " length = " . $sequenceMaxLength . " (efficiency "  . $efficiency . " )\n");
}

file_put_contents($outfile, $output);
