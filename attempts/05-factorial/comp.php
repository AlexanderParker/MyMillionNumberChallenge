<?php

$infile = '../../assets/1001';
$outfile = 'result.comp';

// This is where we store the sequence used to restore the value later:
$program = array();

// Program limits:
$max_factorial = 65535;	  // Impose some limits to our program size
$last_max = $max_factorial;
$max_count = 0; 		  // Count the number of times the maximum factorial is used.
$program_started = false; // Track when we start recording factorials
$min_size = $max_factorial;

// This is the raw data we'll be manipulating (pad the beginning to prevent leading zeroes):
$data = "\xff" . file_get_contents($infile);
$original_size = strlen($data);

// This is the raw data interpreted as an integer:
$start_number = gmp_import($data);
$number = $start_number;

// Find the best ratio
$best_saving = 0;
$best_saving_length = 0;

// Whittle away at the number.
while ($number > $min_size) {
	for ($factorial = $last_max; $factorial > 0; $factorial --) {
		$last_max = $factorial;
		$factorial_value = gmp_fact($factorial);
		$number = gmp_sub($number, $factorial_value);
		if (gmp_cmp($number, "0") < 0) {
			$program_started = true;     // We're now in program space.
			$number = gmp_add($number, $factorial_value);
			continue;                    // Find a lower factorial.
		}
		if (gmp_cmp($number, "$min_size") <= 0) break; // We're done here.		
		if ($program_started) {
			if (!isset($program[$factorial])) $program[$factorial] = 0;
			$program[$factorial] ++;
			$number_length = strlen(gmp_export($number));
			$program_length = count($program) * 4;
			$combined_size = $program_length + $number_length;
			$count_size = strlen(pack('L', $max_count));
			$saving = $original_size - ($combined_size + $count_size);
			if ($saving > $best_saving) {
				$best_saving = $saving;
				$best_saving_length = 0;
			}
		} else {
			$max_count ++;
		}
		break;
	}

	echo "Number Length: $number_length, Program Length: $program_length, Max Count: $max_count, Saving: $saving\n";
}

$result = var_export(array(
	'max_count' => $max_count,
	'program' => $program,
	'remainder' => $number
), true);

file_put_contents($outfile, $result);

// Done.