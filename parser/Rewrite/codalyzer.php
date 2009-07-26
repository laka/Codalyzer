<?php
	
# Somebody said chunky?
ini_set("memory_limit","1024M");
$start = microtime(true);
require_once('classes.php');

$logfile = new logfile();
#$logfile->getLatestLog();

$logarray = $logfile->returnArray();
$gamename = $logfile->getGameName();

$toolbox = new toolbox();
$toolbox->truncateTables('all');

$parser  = new parser($gamename);
$parser->parse($logarray, $regexlib, $gamename);

$loop = new loopwrap();
$loop->gamesLoop();
$loop->playersLoop();

$toolbox->cleanUpProfiles();
$toolbox->optimizeTables('all');

# How fast were we?
$stop = microtime(true);
$total = round((($stop-$start)/60), 2);
echo "Codalyzed in ".$total." min\n";

?>