Million Number Challenge Attempt 4 - IN PROGRESS
================================================

Every number is one digit away from being divisible by 2.

So either a number is immediately divisible, or you subtract 1 (or any number) then divide by two (or any number).

There are two operations, "SUB_FIRST" (1) or "JUST_DIVIDE" (0).  These can be stored as binary digits in a sequence to create a simple program.

Eventually, one would assume that by dividing by two enough times we'd quite quickly reduce the size of the number, while being able to reverse the sequence quite easily to restore the original value.

Results
=======

It seemed to work well on the lorem ipsum sample, I could easily compress and decompress quite well.

Things turned sour on the random digits though.  The sample AMillionRandomDigits.bin would not compress at all.  With a program size of 1000, the size increased by 0.0002 (85 bytes).  With a program size of 10000, a factor of 0.0009 (408 extra bytes).  It just kept going up with each extra division.

* Program Size : Ratio
* 1 : 0.00002649
* 10 : 0.0000409
* 100 : 0.000081
* 1000 : 0.00020
* 10000 : 0.0009
* 100000 : 0.008
* 1000000 : 0.08
* 10000000 : 0.2

It's pretty clear from this that attempting to encode random data in this way is probably futile.  I'll play around with some more efficient packing of the program before giving up on this one.

And then its... on to the next... great adventure?