<?php

define('SUB_FIRST', '1');
define('JUST_DIVIDE', '0');

$infile = 'result.comp';
$outfile = 'restoredData';

// Divisor (must be the same as used to compress the file):
$divisor = 2;

// This is the raw data we'll be manipulating (pad the beginning to prevent leading zeroes):
$data = file_get_contents($infile);

// Read the compressed data:
$delimiter_pos = strpos($data, '|');
$program_size = substr($data, 0, $delimiter_pos);
$program = gzuncompress(substr($data, $delimiter_pos + 1, $program_size));
$program_reversed = str_split(strrev($program));
$number = gmp_import(substr($data, $delimiter_pos + $program_size + 1) ?: 0);

foreach($program_reversed as $instruction) {
	switch ($instruction) {
		case JUST_DIVIDE:
			$number = gmp_mul($number, $divisor);
		break;
		case SUB_FIRST:	
			$number = gmp_mul($number, $divisor);
			$number = gmp_add($number, 1);
		break;
		default;
		break;
	}
}

// Save the file:
$restored_data = gmp_export($number);

file_put_contents($outfile, substr($restored_data, 1));

echo "\nFile saved: {$outfile}\n";
