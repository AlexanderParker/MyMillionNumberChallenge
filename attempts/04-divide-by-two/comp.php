<?php

define('SUB_FIRST', '1');
define('JUST_DIVIDE', '0');

$infile = '../../assets/lorem.txt';
$outfile = 'result.comp';

// This is where we store the sequence used to restore the value later:
$program = "";

// Max program length:
$max_length = 10000000;

// Divisor:
$divisor = 2;

// This is the raw data we'll be manipulating (pad the beginning to prevent leading zeroes):
$data = "\xff" . file_get_contents($infile);

// This is the raw data interpreted as an integer:
$number = gmp_import($data);
$original_size = strlen($data);
$best_program = 0;
$maximum_compression = 0;

do {
	$remainder = gmp_div_r($number, $divisor);
	if ($remainder == 0) {
		$program .= JUST_DIVIDE;
		$number = gmp_div_q($number, $divisor);
	}
	else {	
		$temp_number = gmp_sub($number, 1);
		if (gmp_cmp($temp_number, 2) >= 0) {
			$program .= SUB_FIRST;
			$number = gmp_div_q($temp_number, $divisor);
		}
		else {
			break;
		}
	}
	$size_diff = $original_size - (strlen(gmp_export($number)) + (strlen($program) / 8));
	if ($size_diff < $maximum_compression) {
		$maximum_compression = $size_diff;
		$best_program = strlen($program);
		echo "Best program: $best_program ($maximum_compression)\n";
	}
} while (strlen($program) < $max_length);

// Save some space:
$program_compressed = "";

// Convert program to binary
$program_padding = 0;
foreach(str_split($program, 8) as $program_piece) {
	// So we know what to ignore when decompressing:
	if (strlen($program_piece) < 8) {
		$program_padding = 8 - strlen($program_piece);
		for ($i = 0; $i < $program_padding; $i ++) {
			$program_piece .= '0';
		}
	}
	$program_compressed .= chr(bindec($program_piece));
}

$compressed_program_size = strlen($program_compressed);


// Convert the number back into a byte sequence:
$result_data = gmp_export($number);

// Structure the data:
$file_contents = $program_padding . $compressed_program_size . '|' . $program_compressed . $result_data;

// Save the file:
file_put_contents($outfile, $file_contents);

// Output some metrics:
$raw_program_size = strlen($program);
$result_data_size = strlen($result_data);
$file_size = strlen($file_contents);
$payload_size = $compressed_program_size + $result_data_size;
$file_container_size = $file_size - $payload_size;
$compression_amount = $file_size - $original_size;
$compression_ratio = floatval($compression_amount) / floatval($original_size);
$metrics = array(
	'Original File Size' => $original_size,
	'Raw Program Size' => $raw_program_size,
	'Compressed Program Size' => $compressed_program_size,
	'Result Data Size' => $result_data_size,
	'Result File Size' => $file_size,
	'Payload Size' => $payload_size,
	'File Container Size' => $file_container_size,
	'Compression Amount' => $compression_amount,
	'Compression Ratio' => $compression_ratio,
	'Program Padding' => $program_padding,
);

var_dump($metrics);

echo "\nFile saved: {$outfile}\n";
