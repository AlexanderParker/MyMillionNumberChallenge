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

