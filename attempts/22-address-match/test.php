<?php

$infile = '../../assets/AMillionRandomDigits.bin';
//$infile = '../../assets/husky.gif';

//See is a list of primer number below 
$primes = json_decode(file_get_contents("primes.json", true));
// for( $j = 2; $j <= strlen($originalData); $j++ ) {
// 	$isPrime = true;
// 	for( $k = 2; $k < $j; $k++ ) {
// 		if( $j % $k == 0 ) {
// 			$isPrime = false;
// 			break;
// 		}
// 	}
// 	if ($isPrime) {
// 		array_push($primes, $j);
// 	}
// 	echo "\r" . $j/strlen($originalData);
// }

// file_put_contents("primes.json", json_encode($primes));


$originalData = file_get_contents($infile);

$dataArray = str_split($originalData);

//$char = $originalData[0];

function getIndexed($char) {
	global $originalData, $primes, $dataArray;

	$matchAddresses = array();

	for ($i = 0; $i < strlen($originalData); $i++) {
		if ($originalData[$i] == $char) {
			array_push($matchAddresses, $i);
		}
	}

	$bestSeed = 0;
	$seed = 487255882;
	$best_count = 0;

	$totalAddresses = count($matchAddresses);


	$matches = array();
	$bestMatches = array();
	$lastIndex = 0;
	$maxBits = 0;
	$bestMaxBits = 0;
	$sequenceOffset = 0; //where in the prime sequence do we start
	$bestSequenceOffset = 0; //where in the prime sequence do we start
	$bestLength = 0;
	$newSequence = true;
	$deleteIndexes = array();
	$bestDeleteIndexes = array();
	foreach($primes as $index => $prime) {
		if ($newSequence) {
			$sequenceOffset = $index;
			$lastIndex = $index;
			$newSequence = false;
			$maxBits = 0;
			$deleteIndexes = array();
			$matches = array();	
		}
		$matchIndex = array_search($prime, $matchAddresses, true);

		if ($matchIndex !== false) {
			$gap = ($index - $sequenceOffset) - ($lastIndex - $sequenceOffset);
			$lastIndex = $index;
			$bits = ceil(log($gap, 2));
			if ($bits > 7) {
				$newSequence = true;
				continue; // Maximum bit size we can define is reached, try and find a better sequence
			}
			if ($bits > $maxBits) $maxBits = $bits;
			array_push($matches, $gap);
			array_push($deleteIndexes, $prime);
			if (count($matches) == 255) break; // Don't add more than we can reference
			if (count($matches) > $bestLength) {
				$bestLength = count($matches);
				$bestSequenceOffset = $sequenceOffset;
				$bestMatches = $matches;
				$bestMaxBits = $maxBits;
				$bestDeleteIndexes = $deleteIndexes;
			}
		}
	}

	$dictionary['maxbits'] = str_pad(decbin($bestMaxBits), 3, "0", STR_PAD_LEFT);
	$dictionary['offset'] = str_pad(decbin($bestSequenceOffset), 16, "0", STR_PAD_LEFT);
	$dictionary['character'] = str_pad(decbin(ord($char)), 8, "0", STR_PAD_LEFT);
	$dictionary['count'] = str_pad(decbin(count($bestMatches)), 8, "0", STR_PAD_LEFT);
	$dictionary['gaps'] = array();
	foreach($bestMatches as $match) {
		array_push($dictionary['gaps'], str_pad(decbin($match), $bestMaxBits, "0", STR_PAD_LEFT));
	}

	$bitstring = $dictionary['maxbits'] . $dictionary['offset'] . $dictionary['character'] . $dictionary['count'] . implode($dictionary['gaps'],'');

	return array(
		"size_original" => count($matches) * 8,
		"size_new" => (3 + 16 + 8 + 8 + $maxBits * count($matches)),
		"offset" => $bestSequenceOffset,
		"binary" => $bitstring,
		"delete" => $bestDeleteIndexes,
		"gaps" => $dictionary['gaps']
	);

}

$char = chr(0);

$binaryString = "";

$totalReduction = -16; // The count of character reductions and padding size takes 16 bytes
$count = 0;

for ($i = 0; $i < 255; $i++) {
	echo "Computing gaps for char " . $i . "\n";
	$result = getIndexed(chr($i));
	if ($result["size_new"] < $result["size_original"]) {		
		$binaryString .= $result["binary"];
		$count ++;
		$totalReduction += $result["size_original"] - $result["size_new"];
		foreach ($result['delete'] as $index) {
			$dataArray[$index] = null;
		}
	}
}

// Add padding length, so total binary string packs neatly into bytes
$padding_length = 8 - (strlen($binaryString) % 8);
$binaryString = str_pad(decbin($padding_length), 8, "0", STR_PAD_LEFT) . str_pad($binaryString, strlen($binaryString) + $padding_length, "0", STR_PAD_LEFT);
$totalReduction -= $padding_length; // Remove padding length from reduction amount

// Convert to actual binary bytes
$binary = "";
for($i = 0; $i < strlen($binaryString); $i += 8) {
	$byte = substr($binaryString, $i, 8);
	$binary .= chr(bindec($byte));
}

foreach($dataArray as $dataElement) {
	if ($dataElement != null) {
		$binary .= $dataElement;
	}
}

var_dump(strlen($binaryString) / 8);
var_dump($totalReduction);
file_put_contents("dictionary.bin", $binary);
file_put_contents("original.bin", $originalData);