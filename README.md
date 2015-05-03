Million Number Challenge Attempt 1 - FAILED
===========================================

This is my first obviously naive crack at the million number challenge.

I thought surely, if you just randomly scramble the entire set of bytes
over and over again, eventually you'd get sequences that gzip would be
able to compress, as the data set moved further and further away from
"true random".

Oh, what a fool I've been.  Hundreds of iterations later and it won't
budge.  I get the same result every time.  Seems randomly randomizing
random data returns a random set of data.  Huh, go figure.

I'm sure if we had until the heat death of the universe it may compress
a few bytes here and there, but then again probably not.

The random number generator was the Mersenne Twister built into PHP, and
the built-in gz functions were used.

Oh well, onto the next idea.

Usage
=====

On the command line, execute:

    > php comp.php

It will read the source file, and if successful in compressing will output
the result.

As I wasn't able to have a successful run-through, I didn't bother creating
a file structure to store metadata on start seed and number of iterations,
which would be necessary to create a stand alone data file that could be
decrypted.  Sorry!

Sample Output
=============

```
Iteration 401 - Not Compressed - size diff +83 bytes.Start seed: 1994597758, Seed: 35168996
Iteration 402 - Not Compressed - size diff +83 bytes.Start seed: 1994597758, Seed: 474016659
Iteration 403 - Not Compressed - size diff +83 bytes.Start seed: 1994597758, Seed: 89571026
Iteration 404 - Not Compressed - size diff +83 bytes.Start seed: 1994597758, Seed: 523562622
Iteration 405 - Not Compressed - size diff +83 bytes.Start seed: 1994597758, Seed: 331427469
Iteration 406 - Not Compressed - size diff +83 bytes.Start seed: 1994597758, Seed: 952642125
Iteration 407 - Not Compressed - size diff +83 bytes.Start seed: 1994597758, Seed: 651401837
Iteration 408 - Not Compressed - size diff +83 bytes.Start seed: 1994597758, Seed: 517470817
Iteration 409 - Not Compressed - size diff +83 bytes.Start seed: 1994597758, Seed: 709537143
Iteration 410 - Not Compressed - size diff +83 bytes.Start seed: 1994597758, Seed: 1546244127
Iteration 411 - Not Compressed - size diff +83 bytes.Start seed: 1994597758, Seed: 1568195299
Iteration 412 - Not Compressed - size diff +83 bytes.Start seed: 1994597758, Seed: 1727434158
Iteration 413 - Not Compressed - size diff +83 bytes.Start seed: 1994597758, Seed: 963450300
Iteration 414 - Not Compressed - size diff +83 bytes.Start seed: 1994597758, Seed: 466335632
Iteration 415 - Not Compressed - size diff +83 bytes.Start seed: 1994597758, Seed: 1904649961
Iteration 416 - Not Compressed - size diff +83 bytes.Start seed: 1994597758, Seed: 1137240174
Iteration 417 - Not Compressed - size diff +83 bytes.Start seed: 1994597758, Seed: 509923223
Iteration 418 - Not Compressed - size diff +83 bytes.Start seed: 1994597758, Seed: 1986160599
Iteration 419 - Not Compressed - size diff +83 bytes.Start seed: 1994597758, Seed: 1182225645
Iteration 420 - Not Compressed - size diff +83 bytes.Start seed: 1994597758, Seed: 792523381
Iteration 421 - Not Compressed - size diff +83 bytes.Start seed: 1994597758, Seed: 626330605
^C
```
