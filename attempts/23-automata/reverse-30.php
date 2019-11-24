<?php

// Compute the inverse of elementary automata rule 30
function reverse($data) {		
	$rules = array(
		"111" => "0",
		"110" => "0",
		"101" => "0",
		"100" => "1",
		"011" => "1",
		"010" => "1",
		"001" => "1",
		"000" => "0"
	);
	$parts = array();
	for ($i = 0; $i < strlen((string)$data); $i ++) {
		$left = $i - 1 > 0 ? ((string)$data)[$i - 1] : "0";
		$current = ((string)$data[$i]);
		$right = $i + 1 < strlen((string)$data) - 1 ? ((string)$data)[$i + 1] : "0";

		$newParts = array();
		$failedParts = array();
		foreach ($rules as $candidate => $value) {
			if ((string)$value == (string)$current) {
				if ($i == 0) {
					array_push($newParts, substr((string)$candidate, 1, 2));
				} 
				else {				
					foreach($parts as $index => $part) {
						if ((isset(((string)$part)[$i]) && ((string)$candidate)[1] == ((string)$part)[$i]) && (isset(((string)$part)[$i-1]) && ((string)$candidate)[0] == ((string)$part)[$i-1])) {
								$newData = (string)$part . ((string)$candidate)[2];
								if ($i + 1 >= strlen($data) - 1) {
									if (((string)$candidate)[2] != "0") $newData = "";
								} 
								if ($newData != "") array_push($newParts, $newData);
						}
					}
				}
			}
		}
		$parts = $newParts;	
	}
	if (count($parts) > 1) die ("Too many results");
	if (count($parts) == 0) die ("No results");
	return substr($parts[0], 0, strlen($data));
}

function forward($data) {
	$rules = array(
		"111" => "0",
		"110" => "0",
		"101" => "0",
		"100" => "1",
		"011" => "1",
		"010" => "1",
		"001" => "1",
		"000" => "0"
	);
}

$data = "11011110110011100010110000110011100000011000010101100100011000010000000101111100111001001110111";

while (true) {
	echo $data . "\n";
	if ($data == str_pad("0", "0", strlen($data))) die;
	//$data = $data . ((string)$data)[0];
	$data = reverse($data);
	//$data = substr($data, 0, strlen($data) - 1);
	//sleep(1);
}