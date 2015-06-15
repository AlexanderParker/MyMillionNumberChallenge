<?php

$infile = '../../assets/lorem.txt';
$outfile = 'result.comp';

// This is where we store the sequence used to restore the value later:
$program = array();

// Program limits:
$start_exponent = 2;

$data = file_get_contents($infile);
$number = gmp_import($data);

// Find a nice tower

while(gmp_cmp($number, "0") > 0) {

	// Find the best tower size:
	$max_length = 0;
	$tower_size = 1;
	while (true) {

		$tower = array_fill(0, $tower_size, $start_exponent);
		$tower_index = count($tower) - 1;

		while (true) {
			$result = gmp_init(2);
			foreach ($tower as $power) {
				$result = gmp_pow($result, $power);		
			}
			// Increase the top power until we find a number larger than the original...
			if (gmp_cmp($result, $number) < 0) {
				$tower[$tower_index] ++;
			}
			// ...Then scale back to the one before it and drop the index...
			else {
				$tower[$tower_index] --;			
				$tower_index --;
			}
			// And we're done:
			if ($tower_index < 0) break;	
		}
		// Recalculate result:
		$result = gmp_init(2);
		foreach ($tower as $power) {
			$result = gmp_pow($result, $power);		
		}
		if (strlen(gmp_export($result)) > $max_length) {
			$tower_size ++;
			$max_length = strlen(gmp_export($result));
		}
		else {
			//optimal tower found
			break;
		}
	}	

	// Apply it:
	$result = gmp_init(2);
	foreach ($tower as $power) {
		$result = gmp_pow($result, $power);	
	}
	//var_dump(gmp_export($result));
	// Count how many times the tower is used:
	$count = 0;
	while (true) {
		if (gmp_cmp($number, $result) > 0) {
			$number = gmp_sub($number, $result);			
			$count ++;
		}
		else {
			$program[] = array(
				'count' => $count,
				'tower' => $tower
			);
			break;
		}
	}	
	
	var_dump($program);;

}

// $result = var_export(array(
// 	'max_count' => $max_count,
// 	'program' => $program,
// 	'remainder' => $number
// 	'remainder' => $number
// ), true);

// file_put_contents($outfile, $result);

// Done.