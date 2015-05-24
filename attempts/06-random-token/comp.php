<?php

//$infile = '../../assets/AMillionRandomDigits.bin';
$infile = '../../assets/lorem.txt';
$outfile = 'compressed.bin';

$data = file_get_contents($infile);

$data_length = strlen($data);

$token_bits = 16;

$token_limit = pow (2, $token_bits);

$tokens = array();

$tokensize = 2;

$match = false;

$sequences = array();

// Find unused byte sequences

echo "Finding tokens:\n";

for ($token_int = 0; $token_int < $token_limit; $token_int ++) {
	$chars = unpack('C*', pack('S', $token_int));
	$token = '';
	foreach($chars as $charcode) {
		$token .= chr($charcode);
	}
	if (($pos = strpos($data, $token)) === false) {
		$tokens[] = array(
			'token_int' => $token_int,
			'token_str' => $token,
		);
	}
}

if (count($tokens) > 0) {
	echo "\nPossible dictionary tokens: " . count($tokens) . " (" . $token_bits / 2 * count($tokens) . " bytes)\n";
}
else {
	die("No unused characters found within $token_bits bit token space.");
}

echo "Finding repeating sequences:\n";

// Find repeating sequences
$min_length = $tokensize + 1;
$max_length = $data_length / 2;
$length = $min_length;

// Create a sliding window from the biggest size down:
for ($length = $min_length; $length < $max_length; $length ++) {
	echo "Token length: $length\n";
	$max_offset = $data_length - $length;
	$offset = 0;
	$lengthmatches = 0;
	while ($offset < $max_offset) {
		$comparison_token = substr($data, $offset, $length);
		$comparison_data = substr($data, $offset + $length);
		if (($pos = strpos($comparison_data, $comparison_token)) !== false)	{
			if (!isset($sequences[$length])) {
				$sequences[$length] = array();
			}
			if (!isset($sequences[$length][$comparison_token])) {
				$sequences[$length][$comparison_token] = 1;
			}
			$sequences[$length][$comparison_token] ++;
			$lengthmatches ++;
			$offset += $length;
		}
		else {
			$offset ++;
		}
	}
	if ($lengthmatches == 0) {
		$length -= 1;
		echo "No matches\n";
		break;
	}
}

$longest_token = $length;

if (count($sequences) == 0) {
	die('No repeating sequences found.');
}

echo "Sifting sequences\n";

// To build dictionary:
// [start_size(1byte)] [sizes(1byte)] [size 1 token count(1byte)] [  [token (2byte)][data(nBytes)] [token2...] ] [size 2...[tokens...]]
// Size of entry (bytes) = 2 + datalength
// Only store entry if size of entry < count(matches) * datalength
// Size of dictionary (bytes) = 1 + (total entries size)
// remember to test reject tokens that have become matches before using them

$dictionary = '';

$number_of_sizes = 0;

$token_index = 0;

$output_data = $data;

$bytes_saved = 0;

foreach ($sequences as $size_page => $matches) {
	$page_matches = 0;
	$keep_going = true;
	$page_dictionary = '';
	$page_output_data = $output_data;
	$original_page_size = strlen($page_output_data);
	$page_token_index_start = $token_index;
	foreach ($matches as $match => $count) {
		if ($page_matches >= 256) {
			break; // Hard limit for now.
		}
		$match_length = strlen($match);
		$entry_size = $match_length + $tokensize;
		$original_size = $count * $match_length;
		$page_bytes_reduced = 0;		
		if ($entry_size < $original_size) {
			$token_not_found = false;
			while (!$token_not_found) {
				if ($token_index >= count($tokens)) {
					echo "No more free tokens\n";
					$keep_going = false;
					break 2;
				}
				$token = $tokens[$token_index];
				// Make sure the token is still unique
				$token_not_found = strpos($page_output_data, $token['token_str']) === false;
				$token_index ++;
			}			
			// Add the token to the dictionary
			$page_dictionary .= $token['token_str'];
			// Add the data to the dictionary
			$page_dictionary .= $match;
			echo strlen($token['token_str']) . " " . strlen($match) . " / " . strlen($match) * $original_size . " " . strlen($page_dictionary).  "\n";
			// Calculate saved bytes
			$page_bytes_reduced += $original_size - $entry_size;
			// Trigger some stuff:
			$page_matches ++;
			// Do the replacement
			echo strlen($page_output_data) . " ";
			$page_output_data = str_replace($match, $token['token_str'], $page_output_data);	
			echo strlen($page_output_data) . "\n";			
		}
	}
	if ($page_matches > 0) {
		$page_dictionary = pack('C', $page_matches) . $page_dictionary;
		$overhead = strlen($page_dictionary);
		$new_page_size = strlen($page_output_data);
		// Only add the page if it actually is smaller
		if ($new_page_size + $overhead < $original_page_size) {
			$number_of_sizes ++;
			$dictionary .= $page_dictionary;
			$output_data = $page_output_data;
			$bytes_saved += $page_bytes_reduced ;
			echo "Added page length $size_page\n";
		}
		else {
			echo "Skipped page length $size_page as not smaller\n";
			if ($keep_going == false && $page_token_index_start < count($tokens)) {
				// Reset token pointer for next page:
				$keep_going = true;
				$token_index = $page_token_index_start;
				echo "Resetting token index for next page\n";
			}
		}
	}
	if (!$keep_going) break;
}

// Add the starting size to the dictionary
echo "Preparing compressed data\n";
$compressed_data = pack('C', $longest_token) . $dictionary . $output_data;
$new_size = strlen($compressed_data);

// Write the compressed file
echo "Writing compressed data\n";
file_put_contents('compressed.bin', $compressed_data);

// Some basic stats
echo "Original size  :  $data_length bytes\n";
echo "Comressed Size :  $new_size\n";
echo "Bytes reduced  :  $bytes_saved\n";
$ratio = number_format(floatval($new_size) / floatval($data_length), 4);
echo "Ratio          :  $ratio\n";
echo "Finished.\n\n";