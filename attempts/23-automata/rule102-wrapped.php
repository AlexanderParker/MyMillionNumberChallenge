<?php

// Does the environment wrap as on a cylinder?
define("CYLINDRICAL_ENVIRONMENT", TRUE);
$infile = '../../assets/AMillionRandomDigits.bin';
$milliondata = file_get_contents($infile);

// Byte length of address maps

// Naive addressing
echo strlen(decbin(floor(strlen($milliondata) / 8))) . "\n";

$minSize = 10000;
for ($bookSize = 1; $bookSize <= 65536; $bookSize ++) {
for ($pageSize = 1; $pageSize <= 1024; $pageSize ++) {
for ($blockSize = 1; $blockSize <= 256; $blockSize ++) {
			$chunkSize = 8;
			//for ($chunkSize = 4; $chunkSize <= 8; $chunkSize ++) {
				$bookCount  = floor(strlen($milliondata) / $bookSize);
				$pageCount  = $bookSize / $pageSize;
				$blockCount = $pageSize / $blockSize;
				$chunkCount = $blockSize / $chunkSize;
				$dicSize = strlen(decbin($bookCount) . decbin($pageCount) . decbin($blockCount) . decbin($chunkCount));
				if (
					$dicSize < $minSize && 
					$bookSize % $pageSize == 0 &&
					$pageSize % $blockSize == 0 &&
					$blockSize % $chunkSize == 0
				) {
					echo "Size: $bookSize $pageSize $blockSize $chunkSize\n";
					echo "Count: $bookCount books, $pageCount pages, $blockCount blocks per page, $chunkCount chunks per block\n";
					echo decbin($bookCount) . " " . decbin($pageCount) . " " . decbin($blockCount) . " " . decbin($chunkCount) . "\n";
					echo strlen(decbin($bookCount) . decbin($pageCount) . decbin($blockCount) . decbin($chunkCount)). "\n";
					$minSize = $dicSize;
				}
				echo "\r$bookSize $pageSize $blockSize $chunkSize $dicSize          ";
			//}		
		}
	}
}

die;
$pageSize = 20000;
$blockSize = 200;
$chunkSize = 10;

echo "$pageCount pages, $blockCount blocks per page, $chunkCount chunks per block\n";
echo decbin($pageCount) . " " . decbin($blockCount) . " " . decbin($chunkCount) . "\n";
echo strlen(decbin($pageCount) . decbin($blockCount) . decbin($chunkCount)). "\n";

function evaluate($rule, $left, $centre, $right) {
	switch ($rule) {
		case 30: {
			return
				$left . $centre . $right == "100" ||
				$left . $centre . $right == "011" ||
				$left . $centre . $right == "010" ||
				$left . $centre . $right == "001";
			break;
		}	
	}
}

$pageCount  = floor(strlen($milliondata) / $pageSize);
$blockCount = $pageSize / $blockSize;
$chunkCount = $blockSize / $chunkSize;



$dictionary = array();

for ($page = 0; $page < $pageCount; $page ++) {
	echo "Page $page\n";
	for ($block = 0; $block < $blockCount; $block++) {
		echo "Block $block\n";
		for ($chunk = 0; $chunk < $chunkCount; $chunk++) {
			echo "Chunk $chunk\n";
			$datas = array();	
			for ($i = 0; $i < $chunkSize; $i++) {
				$address = $page * $pageSize + $block * $blockSize + $chunk * $chunkSize + $i;
				array_push($datas, str_pad(decbin(ord($milliondata[$address])), 8, "0", STR_PAD_LEFT));
			}
			$average = 0;
			$min = 10000000;
			$max = 0;
			foreach($datas as $test) {
				$data = str_pad("1", 16, "0", STR_PAD_RIGHT);
				$nextGeneration = str_pad("", 16, "0");
				//echo $data . "\n";
				$uniques = array();
				$cycles = 0;
				while (true) {
					$cycles ++;
					for ($i = 0; $i < strlen($data); $i ++) {
						if (CYLINDRICAL_ENVIRONMENT) {
							$left = ($i - 1 < 0) ? $data[strlen($data) - 1] : $data[$i - 1];
							$centre = $data[$i];
							$right = ($i + 1 > strlen($data) - 1) ? $data[0] : $data[$i + 1];
						}
						else {
							$left = ($i - 1 < 0) ? "0": $data[$i - 1];
							$centre = $data[$i];
							$right = ($i + 1 > strlen($data) - 1) ? "0" : $data[$i + 1];
						}
						if (evaluate(30, $left, $centre, $right)) {
							$nextGeneration[$i] = 1;
						} else {
							$nextGeneration[$i] = 0;
						}
					}
					$data = $nextGeneration;
					if (in_array($data, $uniques)) {
						echo "Algorithm repeating, unable to continue\n";
						die;
					}
					if ($cycles < $min) $min = $cycles;
					if ($cycles > $max) $max = $cycles;
					if (substr($data,0,8) == $test) {
						if ($average == 0) {
							$average = $cycles;
						}
						else {
							$average = ($cycles + $average) / 2;
						}
						break;
					}
					array_push($uniques, $data);
				}
			}
			$canCompress = $max < 128;
			echo ("$page:$block:$chunk - $min $max $average " . ($canCompress ? "**" : "") . "\n");
			if ($canCompress) {
				//array_push()
			}

		}
	}
}