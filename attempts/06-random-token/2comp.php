<?php

global $tokensize, $data_length, $token_limit, $min_length, $last_maxlength;

$tokensize = 1; // Bytes

//$infile = '../../assets/81055__inplano__forest-birds-and-mosquitoes.wav';
//$outfile = '81055__inplano__forest-birds-and-mosquitoes.wav.out';
// $infile = '../../assets/herron.jpg';
// $outfile = 'herron.out';
$infile = '../../assets/AMillionRandomDigits.bin';
//$infile = '../../assets/lorem.txt';
$outfile = 'random.out';

$data = file_get_contents($infile);

$data_length = strlen($data);

$token_bits = 8 * $tokensize;

$token_limit = pow (2, $token_bits); // Decimal;

$tokens = array();

$min_length = $tokensize + 1;

$match = false;

$max_iterations = 255;

$last_maxlength = floor(strlen($data) / 2);

// Find unused byte sequences

// Find an unused token within the provided data.
function get_unique_token($token_data) {
	global $token_limit;
	for ($token_int = 0; $token_int < $token_limit; $token_int ++) {
		$chars = unpack('C*', pack('S', $token_int));
		$token = '';
		foreach($chars as $charcode) {
			$token .= chr($charcode);
		}
		if (($pos = strpos($token_data, $token)) === false) {
			return array(
				'token_int' => $token_int,
				'token_str' => $token,
			);			
		}
	}
	return false;
}

// Find repeating sequence
function find_repeating_sequence($data) {
	global $tokensize, $min_length, $last_maxlength;
	$data_length = strlen($data);

	echo "Finding repeating sequences:\n";
	$sequences = array();
	$blacklist = array();
	// Create a sliding window from the biggest size down:
	for ($length = $last_maxlength; $length > 2; $length --) {
		echo "Search length: $length\n";
		$max_offset = $data_length - $length;
		$offset = 0;
		$lengthmatches = 0;
		while ($offset < $max_offset) {
			$comparison_token = substr($data, $offset, $length);
			$comparison_data = substr($data, $offset + $length);
			if (($count = substr_count($comparison_data, $comparison_token)) > 1)	{
				// Make sure range not in blacklist:
				for ($blacklist_index = $offset; $blacklist_index < $offset + $length; $blacklist_index ++) {
					if (isset($blacklist[$blacklist_index])) {
						// Set new offset:
						$offset ++;
						continue;
					}
				}
				if (!isset($sequences["$length"])) {
					$sequences["$length"] = array();
				}
				if (!isset($sequences[$length][$comparison_token])) {
					$sequences["$length"][$comparison_token] = 0;
				}
				$sequences["$length"][$comparison_token] += $count;
				$lengthmatches += $count;
				for ($blacklist_index = $offset; $blacklist_index < $offset + $length; $blacklist_index ++) {
					$blacklist[$blacklist_index] = true;
				}
				$offset += $length;
			}
			else {
				$offset ++;
			}
		}
		if ($lengthmatches > 0) {
			$matched_size = $length * $lengthmatches;
			$replace_size = $length + $tokensize * count($sequences["$length"]);
			echo "$lengthmatches matches: original size $matched_size, new size $replace_size : ";
			if ($replace_size < $matched_size) {
				echo "Replacing\n";
				return $sequences;
			}
			echo "Skipped\n";
			
		}
	}
	return $sequences;
}

echo "Sifting sequences\n";

// To build dictionary:
// [iterations(1byte)]{[iteration 1 token count(1byte)][token(2byte)][data(nBytes)][token2...]}{[iteration 2...]]
// Size of entry (bytes) = 2 + datalength
// Only store entry if size of entry < count(matches) * datalength
// Size of dictionary (bytes) = 1 + (total entries size)
// remember to test reject tokens that have become matches before using them

$dictionary = '';

$number_of_sizes = 0;

$output_data = $data;

$bytes_saved = 0;

$iterations = 0;

$keep_going = true;
$last_sequence = array();
while ($keep_going) {	
	$sequences = find_repeating_sequence($output_data);
	// Make sure we don't get caught in a loop forever.
	$different = var_export($last_sequence, true) !== var_export($sequences, true);
	if (!$sequences || !$different) {
		echo "No more sequences found\n";
		break;
	}
	$last_sequence = $sequences;
	foreach ($sequences as $size_page => $matches) {
		$page_matches = 0;
		$page_dictionary = '';
		$page_output_data = $output_data;
		$original_page_size = strlen($page_output_data);
		// Add each match sequence to the page
		foreach ($matches as $match => $count) {
			if ($iterations >= $max_iterations) break 3;
			if ($page_matches >= 255) {
				break; // Hard limit for now.
			}
			$token = get_unique_token($page_output_data);					
			$new_entry = $token['token_str'] . $match;
			$l1 = strlen($page_output_data);
			$temp_output_data = str_replace($match, $token['token_str'], $page_output_data);
			if (strlen($new_entry . $temp_output_data) < strlen($page_output_data)) {
				$page_matches ++;
				$iterations ++;
				$page_output_data = $temp_output_data;
			 	$page_dictionary .= $new_entry;				
			}
		}		
		if ($page_matches > 0) {
			$page_dictionary = pack('C', $size_page) . pack('C', $page_matches) . $page_dictionary;				
			$overhead = strlen($page_dictionary);
			$new_page_size = strlen($page_output_data);
			// Only add the page if it actually is smaller
			if ($new_page_size + $overhead < $original_page_size) {
			 	$number_of_sizes ++;
				$output_data = $page_dictionary . $page_output_data;
			 	echo "Added page length $size_page\n";
			}
			else {
			 	echo "Skipped page length $size_page as not smaller\n";
			 	if ($size_page > $min_length) {
			 		$keep_going = true;
			 	}
			}
		}
		break;
	}
	$iterations ++;
}

// Add the starting size to the dictionary
echo "Preparing compressed data\n";
$compressed_data = pack('C', $iterations) . $dictionary . $output_data;
$new_size = strlen($compressed_data);

// Write the compressed file
echo "Writing compressed data\n";
file_put_contents($outfile, $compressed_data);

$bytes_saved = $data_length - $new_size;

$data_length = strlen($data);

// Some basic stats
echo "Original size  :  $data_length bytes\n";
echo "Compressed Size :  $new_size\n";
echo "Bytes reduced  :  $bytes_saved\n";
$ratio = number_format(floatval($new_size) / floatval(strlen($data)), 4);
echo "Ratio          :  $ratio\n";
echo "Finished.\n\n";