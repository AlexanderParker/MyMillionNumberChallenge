<?php 

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

// Find the number of byte sequences, maximum length, and average length
function count_sequences($data, $chr) {
  $index = 0;
  $sequence_length = 0;
  $sequence_count = 0;
  $counting_a_sequence = false;
  $sequences = array(
    'max' => 0, // Maximum sequence length
    'count' => 0, // Number of sequences
    'average' => null, // Average sequence length
    'score' => 0, // Derived score
    'total' => 0, // Total sequence length
  );
  for ($i = 0; $i < strlen($data); $i++) {
    if ($data[$i] == $chr) {
      if (!$counting_a_sequence) {
        $counting_a_sequence = true;
        $sequence_length = 0;
      }
      $sequence_length ++;
    } else {
      if ($counting_a_sequence && $sequence_length > 1) {
        $sequence_count++;
        if ($sequence_length > $sequences['max']) $sequences['max'] = $sequence_length;
        if ($sequences['average'] == null) {
          $sequences['average'] = $sequence_length;
        } else {
          $sequences['average'] = ($sequences['average'] + $sequence_length) / 2;
        }
        $sequences['count'] = $sequence_count;
        $sequences['total'] += $sequence_length;
      }
      $counting_a_sequence = false;
    }
  }
  // Calculate the score metric
  $sequences['score'] = $sequences['count'] * $sequences['average'] * $sequences['max'];
  return $sequences;
}

