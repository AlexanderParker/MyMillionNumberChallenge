var rule = 30
var iterations = 10000
var countOperations = 0
var profile = {
	"StandardIterationsEffort": 0,
  "LookupTableEffort": 0,
  "SkipStepIterationsEffort": 0,
	"StandardIterationsTime": 0,
  "LookupTableTime": 0,
  "SkipStepIterationsTime": 0
}
var stepSize = 5
// Find higher order rules for current row + n
// Call the function for (n, rule, generations) where n is the number of generations to look behind, and generations determines the number of generations of automata output to look within

// Generate iterations the old fashioned way for profiling
var standardInput = "1"
var standardOutput = []
profile.StandardIterationsTime = performance.now()
standardOutput.push(standardInput)
for (var standardIterations = 0; standardIterations < iterations; standardIterations ++) {
  standardInput = nextGeneration(standardInput, rule)
  standardOutput.push(standardInput)
}
profile.StandardIterationsTime = performance.now() - profile.StandardIterationsTime
profile.StandardIterationsEffort = countOperations
countOperations = 0;

profile.LookupTableTime = performance.now()
// Generate iterations using higher order steps (n+stepSize)
var higherOrderRules = findHigherOrderRules(stepSize, rule)
console.log(higherOrderRules)
profile.LookupTableTime = performance.now() - profile.LookupTableTime
// Reset operation count (for profiling)
profile.LookupTableEffort = countOperations;

countOperations = 0;
var skipStepsInput = "1"
var skipStepsOutput = []

profile.SkipStepIterationsTime = performance.now()
skipStepsOutput.push(skipStepsInput)

for (var skipStepsIterations = 0; skipStepsIterations < (iterations / stepSize); skipStepsIterations ++) {
   skipStepsInput = generateNthRow(skipStepsInput, stepSize, higherOrderRules)   
   skipStepsOutput[skipStepsIterations] = skipStepsInput
}
profile.SkipStepIterationsTime = performance.now() - profile.SkipStepIterationsTime
profile.SkipStepIterationsEffort = countOperations
countOperations = 0

console.log("Last row standard", standardOutput[standardOutput.length-1])
console.log("Last row step size 5", skipStepsOutput[skipStepsOutput.length-1])
console.log(profile)

// Generate input row + n 

function generateNthRow(input, n, higherOrderRules) {
  nextRow = ""  
  input = input.padStart(n*2 + input.length,"0").padEnd(n * 4 + input.length,"0")   
  for (var i = 0; i < input.length - n*2; i ++) {  
  	var matchedPattern = higherOrderRules[input.substr(i,n*2+1)];    
    nextRow += matchedPattern[matchedPattern.length-1]
    countOperations ++
  }  
  return nextRow
}


// Returns a cellular automata rule "n", as an array of patterns matches with output values
function getRule(n) {  
	var sequence = parseInt(n).toString(2).padStart(8,"0");
  return ["111", "110","101","100","011","010","001","000",].map(function(currentValue, index, arr) {  	
    return [currentValue, sequence[index]]
  })
}

// Calculates the next generation of the given input using the specified rule array
function nextGeneration(input, rule) {
  rule = getRule(rule)
  // create a placeholder for the next step
  var nextStep = "".padStart(input.length + 2, "0")
  input = input.padStart(input.length + 1,"0").padEnd(input.length + 2,"0")
  for (var i = 0; i < nextStep.length; i++) {
  	countOperations++
  	// generate p + q + r
    var match = (i > 0 ? input[i - 1] : 0) + (input[i]) + (i < nextStep.length - 1 ? input[i + 1] : 0)
    var nextValue = 0;
    rule.forEach(function(value) {
    	if (value[0] == match) {
        nextStep = nextStep.substr(0, i) + value[1] + nextStep.substr(i + 1);
      }
    })
  }
  return nextStep
}

// Generate n generations of specified rules
function getGenerations(number, rule) {
	var currentGeneration = "1";
  var generations = []
  for (var i = 0; i < number; i++){
    generations.push(currentGeneration.padStart(number + i, "0").padEnd(number*2-1, "0"))
    currentGeneration = nextGeneration(currentGeneration, rule)
  }  
  return generations
}

// Figure out which other rules would generate the next step for the specified rules

function findHigherOrderRules(n, rule) { 
  var ruleOrders = {}
  // Calculate rule orders descending from n to 1
  for (var currentOrder = n; currentOrder >= 1; currentOrder --) {
 		var patternWidth = currentOrder * 2 + 1 // Width of rule pattern to calculate iteration+n
	  var patternCount = Math.pow(2, patternWidth) // The number of patterns in this rule order
    for (var patternNumber = 0; patternNumber < patternCount; patternNumber++) {
    	var pattern = patternNumber.toString(2).padStart(patternWidth, "0")
      var nextOrder = nextGeneration(pattern, rule).substr(2, patternWidth -2)
      ruleOrders[pattern] = [nextOrder]
    }
  }
  // Fan out the rules
  for (var i = 0; i < n-1; i++) {
    for (var key in ruleOrders) {
    	countOperations++
		 if (typeof ruleOrders[ruleOrders[key][0]] != "undefined" && ruleOrders[ruleOrders[key][0]].hasOwnProperty(i)) ruleOrders[key].push(ruleOrders[ruleOrders[key][0]][i])
    }
  }
  return ruleOrders
}

