var Gearman = require("node-gearman"),

	// Use your Gearman Server's remote IP (or localhost if it's running local)
    gearman = new Gearman("10.32.91.27", 4730);

// Register a new Job
var job = gearman.submitJob("reverse", "Reverse this string pls thx");

job.setTimeout(2000);

// Handle: Timeouts
job.on("timeout", function(){
    console.log("Timeout!");
    gearman.close();
})

// Handle: Errors
job.on("error", function(err){
    console.log("ERROR: ", err.message || err);
    gearman.close();
});

// Handle: Incoming data
job.on("data", function(reversed){
    console.log(reversed.toString());
});

// Handle: End of job
job.on("end", function(){
    console.log("Ready!");
    gearman.close();
});