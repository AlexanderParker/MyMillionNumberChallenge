What is this?
=============

This will catalogue my attempts to grapple with the million number challenge.

At the very least I'm hoping to provide some entertainment values as my feeble
mind battles concepts beyond its puny understanding.

I hope also to provide some insights for people who, like me, go "That doesn't
sound so hard does it?  Why not just <blah>?" by offering practical examples of
what doesn't work.

If any mathematicians happen by who can explain the "why" behind my failings,
I'd be much obliged.

It's cold and windy outside tonight...  I feel the urge to try another
hare-brained compression algorithm...

I'll be back.

Update 20/05/2019
=================

I know this is a fool's errand, but I persist. Tackling the impossible feels like a mantra than a scientific exercise.

In the current iteration, I'm brute forcing an algorithm that incrementally reduces entropy of the source data. It's not even reducing the size of the data! But with each iteration, the randomness decreases.

I'm running this in a virtual machine whenever I have spare processing time. The algorithm is as follows:

 - Set the random seed to 0
 - Generate random bytes matching the filesize of the source data
 - XOR the source data with the random mask
 - If the Shannon entropy is lower, record the seed and replace the working data with the new, lower-entropy version.
 - Repeat until a certain threshold is reached.

I'm sure this is just a fancier way of pushing the complexity of the data into a different corner as always.

We'll see if I eventually shave the entropy by 1/1000th - and if the size of the dictionary of seeds needed to reverse the process is lower.

But it's fun to play with data in different ways.

Another slight observation
==========================

 If this approach is taken to low-entropy data (like lorem text) - the algorithm is worse. So as entropy reduces, randomising the base data set is less useful.

 Basing the chance of randomising a character on the current entropy level might yield results...