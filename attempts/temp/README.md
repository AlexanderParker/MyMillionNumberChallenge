Million Number Challenge Attempt 3 - RUNNING
============================================

This time I thought I'd get the better of those pesky digits using basic division.

Surely inside all those numbers there would be just one that was perfectly divisible, and where the storage size of the quotient and divisor were smaller than the whole.

The way I tried to figure it, 100 / 2 is 4 numbers.  To reverse it, 2 * 50 is only 3 numbers.  If I could find a combination of numbers that resulted in a large enough saving to account for the storage of the information, then I would be onto something for sure.

Requires PHP 5.6 and gmp.

The algorithm
=============

* Set a "chunk size", say, 2.
* Set an offset at file position 0.
* Read the chunk of bytes at the offset as an integer.
* Start with a divisor of 2.
* Increment the divisor up to a fixed limit.
* Record the highest divisor the integer could be divided by with no remainder.
* Increment the offset by 1 and repeat with the next chunk, and so on until the entire file is read.
* Increase the chunk size until it encompasses the whole file.
* Process the recorded divisable integers, as many will overlap find a way to arrange the chunks that give the greatest benefit.
* ...?
* Profit.

The problem
===========

* It's going to take forever to run.