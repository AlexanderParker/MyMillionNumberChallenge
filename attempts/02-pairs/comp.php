<?php

// Read the raw bytes.
//$data = unpack('C*', file_get_contents('../../assets/lorem.txt'));
$data = unpack('C*', file_get_contents('../../assets/AMillionRandomDigits.bin'));
//$data = unpack('C*', file_get_contents('../../assets/RandomNumbers'));

// Fits in 4 bits...
$maxorder = 16;

$pairs = array();
$pagefile = array();

$segment = 0;
$page = 0;

// Find pairs:
foreach ($data as $index => $value) {
	if ($index > 0 && $index % 65536 == 0) {
		$segment ++;
		$page = 0;
	}
	if (($index - $segment * 65536) % 255 == 0) {
		$page ++;
	}
	$pageindex = ($index - $segment * 65536 - $page * 255);

	for ($order = 1; $order <= $maxorder; $order++) {
		$offset = $index + $order;
		// Prevent reading past eof:
		if ($offset > count($data)) continue;		
		if ($value == $data[$offset] && !isset($pairs[$value][$offset])) {
			// Build index (prevent overlap)
			if (!isset($pairs[$value])) {
				$pairs[$value] = array();
			}
			if (!isset($pairs[$value][$index])) {
				$pairs[$value][$index] = array();
			}
			$pairs[$value][$index][] = array(
				'order' => $order,
				'segment' => $segment,
				'page' => $page,
				'pageindex' => $pageindex
			);
			// Populate pagefile
			if (!isset($pagefile[$segment])) {
				$pagefile[$segment] = array();
			}
			if (!isset($pagefile[$segment][$page])) {
				$pagefile[$segment][$page] = array();
			}
			if (!isset($pagefile[$segment][$page][$pageindex])) {
				$pagefile[$segment][$page][$pageindex] = array();
			}
			if (!isset($pagefile[$segment][$page][$pageindex][$value])) {
				$pagefile[$segment][$page][$pageindex][$value] = array();
			}
			// Set order
			$pagefile[$segment][$page][$pageindex][$value][] = $order;
		}
	}
}

// Strip pairs that don't have a net benefit:
// Header: [segment<2byte>][pagecount<byte>][page<byte>][indexcount<byte>][index<byte>][value<byte>][ordercount<byte>][2xorders<byte>]
//  		2 				1
//											1			1
//																			1			1			1
//																														0.5
//	
var_dump($pagefile);
die;
foreach ($pagefile as $segment => $pages) {
	foreach ($pages as $page => $indices) {
		foreach ($indices as $index => $value) {
			var_dump($value);
			if (count($value) % 2 != 0) {
		 		unset($pagefile[$segment][$page][$index]);
			}
			if (empty($pagefile[$segment][$page][$index])) {
				unset($pagefile[$segment][$page][$index]);
			}
		}
		if (empty($pagefile[$segment][$page])) {
			unset($pagefile[$segment][$page]);
		}
	}
	if (empty($pagefile[$segment])) {
		unset($pagefile[$segment]);
	}
}

var_dump($pagefile);
