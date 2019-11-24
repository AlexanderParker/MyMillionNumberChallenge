<?php

$infile = '../../assets/AMillionRandomDigits.bin';
$outfile = 'random.out';

//$originalData = substr(file_get_contents($infile),0,100);
$originalData = file_get_contents($infile);

$firstByte = $originalData[0];
$byteAddresses = array();

for ($i = 0; $i < strlen($originalData); $i++) {
	if ($originalData[$i] == $firstByte) {
		array_push($byteAddresses, $i);
	}
}

$bestSeed = 0;
$seed = 0;
$best_count = 0;

// Find maximum spacing between addresses

$addressSpacing = 0;
foreach ($byteAddresses as $index => $address) {
	if ($index == 0) {
		$addressSpacing = $address;
	}
	else {
		$thisSpacing = $address - $byteAddresses[$index-1];
		if ($thisSpacing > $addressSpacing) $addressSpacing = $thisSpacing;
	}
}

while(true) {
	mt_srand($seed, MT_RAND_MT19937);
	$get_next_address = true;
	$count_matches = 0;
	$lastAddress = 0;
	while ($get_next_address) {
		$nextAddress = mt_rand(0, $addressSpacing * 2) + $lastAddress;
		if (in_array($nextAddress, $byteAddresses)) {
			$count_matches ++;
			$lastAddress = $nextAddress;
		}
		else {
			$get_next_address = false;			
		}
	}
	if ($count_matches > $best_count) {
		$best_count = $count_matches;
		$bestSeed = $seed;
		echo "found $best_count addresses for seed $seed\n";
	}
	$seed ++;
}