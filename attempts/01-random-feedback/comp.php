<?php

// Read the raw bytes.
//$data = unpack('C*', file_get_contents('../../assets/lorem.txt'));
$data = unpack('C*', file_get_contents('../../assets/AMillionRandomDigits.bin'));
//$data = unpack('C*', file_get_contents('../../assets/RandomNumbers'));

global $compressed;
global $iteration;
global $is_compressed;
global $last_compressed;
global $data_size;
global $start_seed;

$compressed = false;
$iteration = 0;
$data_size = count($data);
$start_seed = mt_rand();

function randomize($data, $seed) {
	global $compressed;
	global $iteration;
	global $last_compressed;
	global $data_size;
	global $start_seed;
	$packed_data = processIteration($data, $seed);
	$last_compressed = gzcompress($packed_data, 9, ZLIB_ENCODING_RAW);
	$compressed_size = strlen($last_compressed);
	$is_compressed = $compressed_size < $data_size;
	// Keep track of progress:
	$iteration ++;
	echo "Iteration {$iteration} - " . (
		$is_compressed 
		? 'Compressed - size diff -' . ($data_size - $compressed_size) . " bytes!" 
		: 'Not Compressed - size diff +' . ($compressed_size - $data_size). " bytes."
	);
	echo "Start seed: {$start_seed}, Seed: {$seed}\n";
	return unpack('C*', $packed_data);
}

function processIteration($data, $seed) {
	// Initialise the random number generator:
	mt_srand($seed);

	// Somewhere to store the new data:
	$new_data = array();

	// Randomise the data:
	foreach ($data as $byte) {
		// Determine the new value:
		$byte += mt_rand(0,255);
		// Wrap the value:
		$byte -= 255 * floor($byte / 255);
  		$new_data[] = $byte;
	}
	// Compress using gzip algorithm:
	return call_user_func_array("pack", array_merge(array("C*"), $new_data));
}

while ($is_compressed == false) {
  $seed = mt_rand();
  $data = randomize($data, $seed);
}

// Win! (well, if whatever metadata we need is also smaller than the original file)
file_put_contents('compressed.bin', $last_compressed);

$output[] = "Original Size: {$data_size} bytes.";

$output[] = "Compressed Size: " . strlen($last_compressed) . " bytes.";

$output[] = "Starting Seed {$start_seed}";

$output[] = "Iterations: {$iteration}";

$output[] = "Finished. You win.";

foreach ($output as $count => $text) {
  echo "{$count}: {$text}\n";
}
