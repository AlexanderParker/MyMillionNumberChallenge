<?php

// Decompress a file

$infile = 'random.out';
$outfile = 'random.restored';

$data = file_get_contents($infile);

// Pages:
$pages = array_shift(unpack('C', substr($data, 0, 1)));

$data = substr($data, 1);

$tokens = array();

for ($i = 0; $i < $pages; $i ++) {
	$tokens [] = array();
	$page_size = array_shift(unpack('C', substr($data, 0, 1)));
	var_dump($page_size);
	$data = substr($data, 1);
	$token_count = array_shift(unpack('C', substr($data, 0, 1)));
	var_dump($token_count);
	$data = substr($data, 1);	
	for ($j = 0; $j < $token_count; $j ++) {
		$token_str = substr($data, 0, 2);
		$data = substr($data, 2);
		$token_data = substr($data, 0, $page_size);
		$data = substr($data, $page_size );
		$tokens[$i][$token_str] = $token_data;
	}
}

var_dump($tokens);

$total = 0;
foreach (array_reverse($tokens) as $page => $page_tokens) {
	foreach (array_reverse($page_tokens) as $search => $replace) {
		$total += substr_count($data, $search);
		$data = str_replace($search, $replace, $data);
		echo ".";
	}
}

file_put_contents($outfile, $data);