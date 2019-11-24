package main

import (
	"fmt"
	"io/ioutil"
	"github.com/seehuhn/mt19937"
	"math"
	"strings"
	"math/rand"
	"sync"
)

// Used to check for file errors
func check(e error) {
    if e != nil {
        panic(e)
    }
}

type sequence struct {
    max uint64
    count uint64
    average float64
    score float64
    total uint64
    seed int64
}

type chunk_data struct {
	data []byte
	score sequence
	seed int64 
}

// Find the number of byte sequences, maximum length, and average length
func count_sequences(data []byte, chr byte) sequence {
	newScore := sequence {
		average: -1, // Average sequence length
	    max: 0, // Maximum sequence length
	    count: 0, // Number of sequences	    	    
	    total: 0, // Total sequence length
	    score: 0, // Derived score
	}
	var sequence_length uint64 = 0
	var sequence_count uint64 = 0
    counting_a_sequence := false
    for i := 0; i < len(data); i++ {
      if (data[i] == chr) {
        if (!counting_a_sequence) {
          counting_a_sequence = true
          sequence_length = 0
        }
        sequence_length ++
      } else {
        if (counting_a_sequence && sequence_length > 1) {
          sequence_count++
	      if (sequence_length > newScore.max) {
	      	newScore.max = sequence_length
	      }
	      if (newScore.average < 0) {
	        newScore.average = float64(sequence_length)
	      } else {
	        newScore.average = (newScore.average + float64(sequence_length)) / 2
	      }
	      newScore.count = sequence_count
	      newScore.total += sequence_length
        }
        counting_a_sequence = false
      }
    }

    // Calculate the score metric
    //newScore.score = math.Pow(float64(newScore.count), float64(newScore.max))
    newScore.score = math.Pow(float64(newScore.total), float64(newScore.average))
    return newScore
}

// Figure out the best character in the input file
func get_best_char(dat []byte) (best_char byte) {
	var best_score float64 = 0
	var wg sync.WaitGroup
	best_char = 0
	wg.Add(256);
	for i := 0; i<256; i++ {
		go func(i int) {
			defer wg.Done()
			res := count_sequences(dat,byte(i))
			if res.score > best_score {
				best_score = res.score
				best_char = byte(i)
			}
		}(i)
	}
	wg.Wait()
	return best_char
}

// Calculate shannon entropy of data
func get_shannon_entropy(data string) (entropy float64) {
    for i := 0; i < 256; i++ {
        px := float64(strings.Count(data, string(byte(i)))) / float64(len(data))
        if px > 0 {
	    entropy += -px * math.Log2(px)
	}
    }
    return entropy
}



func process_chunk(dat []byte, seed int64, char byte, iteration int) (ret chunk_data) {
  var data []byte = make([]byte, len(dat))
  copy(data, dat)
  initialScore := count_sequences(data, char)
  rng := rand.New(mt19937.New())
  rng.Seed(seed) 
  // Generate xor character mask
  for charIndex := 0; charIndex < len(data); charIndex++ {
	      if rng.Intn(101) + 1 > 100 - 100/(iteration+1) {
	        data[charIndex] = data[charIndex] ^ byte(rng.Intn(256))
	      }
	}

  // Score new data
  newScore := count_sequences(data, char)
  ret.score = newScore
  if newScore.score > initialScore.score && newScore.average > initialScore.average && newScore.total > initialScore.total {
//  if newScore.score > initialScore.score && newScore.average > initialScore.average && newScore.total > initialScore.total {
  	ret.data = make([]byte, len(dat))
  	copy(ret.data, data)
	ret.seed = seed
  } else {
	ret.seed = -1
  }
  return ret
}

// Juicy fruit time
func main() {
	dat, err := ioutil.ReadFile("../../assets/AMillionRandomDigits.bin")	
	check(err)	
	iteration := 0
    var seed int64 = 0
    var scores[] sequence
    best_char := get_best_char(dat)
    for true != false {
		newChunk := process_chunk(dat, seed, best_char, iteration)
		if newChunk.seed > -1 {
			newChunk.score.seed = newChunk.seed
			fmt.Printf("\033[2K\rScore: %+v\n", newChunk.score)
			copy(dat, newChunk.data)
			ioutil.WriteFile("output.bin", newChunk.data, 0755)	
			scores = append(scores, newChunk.score)
			ioutil.WriteFile("seeds.txt", []byte(fmt.Sprintf("%+v\nCHR:%x",scores, best_char)), 0755)			
			seed = 0 // reset the seed
			iteration ++
		} else {
			fmt.Printf("\033[2K\rSeed: %d, Score: %+v", seed, newChunk.score)
			seed = seed + 1
		}
	}
	// We done here?
	fmt.Scanln()
	rng := rand.New(mt19937.New())
	rng.Seed(1)
	fmt.Println(int(rng.Intn(255)))
}
