<?php

$infile = '../../assets/RandomNumbers';

$data = file_get_contents($infile);

$colors = array();

for ($i = 0; $i < 255; $i ++) {
	$colors[] = "rgb({$i}, 0, " . (255 - $i) . ")";
}

$split = str_split($data);

foreach ($split as $char) {
	$val = ord($char);
	echo "<div class='pixel' style='width: 20px; height: 20px; float: left;background-color: " . $colors[$val] . "'>{$val}</div>";
}

