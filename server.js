var Gearman = require("node-gearman"),

	// Use your Gearman Server's remote IP (or localhost if it's running local)
    gearman = new Gearman("10.32.91.27", 4730); 

// Register a new Worker for given fuction
gearman.registerWorker("reverse", function(payload, worker){
	
	// Check if there's data given
    if(!payload){
        worker.error();
        return;
    }
	
	// Do the requested magic
    var reversed = payload.toString("utf-8").split("").reverse().join("");

    // Return the response
    setTimeout(function(){
        worker.end(reversed);
    },1000);
    
});