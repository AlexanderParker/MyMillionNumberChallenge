<?php

$data = file_get_contents('../../assets/AMillionRandomDigits.bin');

$size = strlen($data);
$chunksize = 1;
$iterations = 0;
$maxdivisor = 1000;
$foundchunks = array();

while ($chunksize <= $size) {
	echo '.';
	$iterations ++;
	$range = $size - $chunksize;
	$offset = 0;
	// Explore the range for perfect square roots:
	do {
		// Get the chunk:
		$chunk = substr($data, $offset, $chunksize);
		// Load string as a number:
		$number = gmp_import($chunk);
		// Find the highest divisor:
		$divisor = 2;
		$maxdivisor = 0;
		$maxresult = null;
		do {
			$remainder = gmp_div_r($number, $divisor);			
			if ($remainder == 0) {
				$result = gmp_export(gmp_div_q($number, $divisor));
				$resultsize = strlen($result);
				$sizediff = $chunksize - $resultsize;
				if ($sizediff < 0) {
					echo "\nIteration {$iterations} - chunk size {$chunksize} bytes, range {$range}, offset {$offset}\n";
					echo "Chunk can be divided by {$divisor}, new length {$resultsize} ({$sizediff} bytes)\n";
					$maxdivisor = $divisor;
					$maxresult = $result;
				}			
			}
			$divisor ++;
		} while ($divisor < $maxdivisor);
		if ($maxdivisor > 0) {
			echo "[" . $chunksize - strlen($result) . "]";
			$foundchunks[] = array(
				'divisor' => $maxdivisor,
				'result' => $result,
				'offset' => $offset,
				'chunksize' => $chunksize,
				'resultsize'=> strlen($result),
			);
		}
		// Step through the range:
		$offset ++;
	} while ($offset <= $range);
	// Reduce our chunksize:
	$chunksize ++;	
}

// Let's output the data for now, so we can play with it (if any):
if (count($foundchunks) > 0) {
	file_put_contents('result.json', json_encode($foundchunks));
	echo "\nChunks found, result.json written\n";
}
else {
	echo "\nNo chunks for you :(\n";
}