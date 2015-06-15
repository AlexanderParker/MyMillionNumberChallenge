<?php

$infile = '../../assets/lorem.txt';
$outfile = 'result.comp';

// This is where we store the sequence used to restore the value later:
$program = array();

// Program limits:
$max_power = 65535;	  // Impose some limits to our program size
$last_max = $max_power;
$max_count = 0; 		  // Count the number of times the maximum exponent is used.
$program_started = false; // Track when we start recording exponents
$min_size = $max_power;

// This is the raw data we'll be manipulating (pad the beginning to prevent leading zeroes):
$data = "\xff" . file_get_contents($infile);
$original_size = strlen($data);

// This is the raw data interpreted as an integer:
$start_number = gmp_import($data);
$number = $start_number;

// Whittle away at the number.
while (gmp_cmp($number, $min_size) >= 0) {
	for ($exponent = $last_max; $exponent > 0; $exponent --) {
		$last_max = $exponent;
		$exponent_value = gmp_pow("2", $exponent);
		$number = gmp_sub($number, $exponent_value);
		if (gmp_cmp($number, "0") < 0) {
			$program_started = true;     // We're now in program space.
			$number = gmp_add($number, $exponent_value);
			continue;                    // Find a lower exponent.
		}
		if (gmp_cmp($number, "$min_size") <= 0) break; // We're done here.		
		if ($program_started) {
			if (!isset($program[$exponent])) $program[$exponent] = 0;
			$program[$exponent] ++;
			$number_length = strlen(gmp_export($number));
			$program_length = count($program) * 4;
			$combined_size = $program_length + $number_length;
			$count_size = strlen(pack('L', $max_count));
			$saving = $original_size - ($combined_size + $count_size);
		} else {
			$max_count ++;
		}
		break;
	}
	// I don't care.
	$number_length = strlen(gmp_export($number));
	$program_length = count($program) * 4;
	$combined_size = $program_length + $number_length;
	$count_size = strlen(pack('L', $max_count));
	$saving = $original_size - ($combined_size + $count_size);

	echo "Number Length: $number_length, Program Length: $program_length, Max Count: $max_count, Saving: $saving\n";
}

$result = var_export(array(
	'max_count' => $max_count,
	'program' => $program,
	'remainder' => $number
), true);

file_put_contents($outfile, $result);

// Done.