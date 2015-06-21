<?php

global $tokensize, $data_length, $token_limit, $min_length, $last_maxlength;

// Program is an array of 7bit int and 1bit flags.
// Int: 0 - 127 cadence / period
// Bit 2: 0 to subtract 1, 1 to add 1
// Yes, overflows wrap.

define("SUBTRACT", "0");
define("ADD", "1");

$program = array();

$infile = '../../assets/AMillionRandomDigits.bin';
$outfile = 'random.out';

$original_data = file_get_contents($infile);
$modified_data = $original_data;

function modify($data, $cadence, $direction) {
  $string_array = str_split($data);
  for ($index = 0; $index < count($string_array); $index += $cadence) {
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

$start_entropy = get_shannon_entropy($original_data);
$best_entropy = $start_entropy;

echo "Start entropy: $start_entropy\n";

$last_cadence = 0;
$last_direction = null;

while (true) {

  $count_iterations ++;

  $candidates = array();

  for ($cadence = 2; $cadence < 256; $cadence ++) {
    $candidates[] = array('cadence' => $cadence, 'direction' => ADD, 'data' => modify($modified_data, $cadence, ADD));
    $candidates[] = array('cadence' => $cadence, 'direction' => SUBTRACT, 'data' => modify($modified_data, $cadence, SUBTRACT));
  }

  $best_index = 0;  

  $better_entropy_found = FALSE;

  foreach ($candidates as $index => $candidate) {
    $entropy = get_shannon_entropy($candidate['data']);
    if ($entropy < $best_entropy) {
      $best_index = $index;
      $best_entropy = $entropy;
      $better_entropy_found = TRUE;
      $last_cadence = $candidate['cadence'];
      $last_direction = $candidate['direction'];
    }
  }

  if ($better_entropy_found) {
    $modified_data = $candidates[$best_index]['data'];
    $program[] = array('direction' => $candidates[$best_index]['cadence'], 'cadence' => $candidates[$best_index]['cadence']);
  }
  else {
    echo "Best entropy reached.\n";
    break;
  }

  echo "It. $count_iterations entropy: $best_entropy, cadence: $last_cadence, direction: $last_direction\n";
}

echo "Entropy improvement: " . $start_entropy - $best_entropy . "\n";

file_put_contents($outfile, $modified_data);
file_put_contents($outfile . '.program.json', json_encode($program));