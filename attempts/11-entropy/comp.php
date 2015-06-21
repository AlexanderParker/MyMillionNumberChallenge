<?php

global $tokensize, $data_length, $token_limit, $min_length, $last_maxlength;

// Program is an array of bit pairs
// Bit 1: 0 to operate on even bytes, 1 to operate on odd bytes.
// Bit 2: 0 to subtract 1, 1 to add 1
// Yes, overflows wrap.

define("EVEN", '0');
define("ODD", "1");
define("SUBTRACT", "0");
define("ADD", "1");

$program = array();

$infile = '../../assets/AMillionRandomDigits.bin';
$outfile = 'random.out';

$original_data = file_get_contents($infile);
$modified_data = $original_data;

function modify($data, $cadence, $direction) {
  $string_array = str_split($data);
  for ($index = $cadence; $index < count($string_array); $index += 2) {
    $result = ord($string_array[$index]) + ($direction * 2 - 1);
    if ($result < 0) $result += 255;
    if ($result > 255) $result -= 255;
    $string_array[$index] = chr($result);
  }
  return implode('', $string_array);
}

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

$count_iterations = 0;

$best_entropy = get_shannon_entropy($original_data);;

while (true) {

  $count_iterations ++;

  $candidates = array(
    modify($modified_data, ODD, ADD),
    modify($modified_data, ODD, SUBTRACT),
    modify($modified_data, EVEN, ADD),
    modify($modified_data, EVEN, SUBTRACT),
  );

  $best_index = 0;  

  $better_entropy_found = FALSE;

  foreach ($candidates as $index => $candidate) {
    $entropy = get_shannon_entropy($candidate);
    if ($entropy < $best_entropy) {
      $best_index = $index;
      $best_entropy = $entropy;
      $better_entropy_found = TRUE;
    }
  }

  if ($better_entropy_found) {
    $modified_data = $candidates[$best_index];
  }
  else {
    echo "Best entropy reached.\n";
    break;
  }

  echo "It. $count_iterations entropy: $best_entropy\n";
}

file_put_contents($outfile, $modified_data);