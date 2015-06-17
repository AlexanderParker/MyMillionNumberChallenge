<?php

$infile = '../../assets/lorem.txt';
$outfile = 'result.comp';

// This is where we store the sequence used to restore the value later:
$program = array();

$data = file_get_contents($infile);
$number = gmp_import($data);

$nthroot = 2;

function gmp_length(GMP $gmp) {
	return strlen(gmp_export($gmp));
}

var_dump(gmp_length($number));

$quadroot = gmp_root($number, $nthroot);

var_dump(gmp_length($quadroot));

$count = 0;

$remainder = $number;
while (true) {
	$count ++;
	$temp = gmp_sub($remainder, gmp_pow($quadroot, $nthroot));
	$cmp_zero = gmp_cmp($temp, 0);
	if ($cmp_zero >= 0) {
		$remainder = $temp;
	}
	if ($cmp_zero <= 0) {
		break;
	}
}

var_dump($count);

var_dump(gmp_length($remainder));

var_dump(gmp_length($remainder) + gmp_length($quadroot) + gmp_length(gmp_init($count)));

// nup. Always longer.