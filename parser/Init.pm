package CA::Init;

# Codalyzer
# - Init file for the application
# Yes, start here

use strict;
use warnings;
use Getopt::Std;
use lib '/home/homer/ju/jussimik/CA-Parser/';
use CA::Common;
use CA::Config;
use CA::Core;
use CA::SimpleDB;
use CA::Parser;

my $dbh = CA::SimpleDB::getDbh();
my %config = CA::Config::readConfig();

# Fetch command-line arguments
my %cmd_args;
getopt('ifd', \%cmd_args);

if(exists($cmd_args{i})) {
    # Starting interactive mode
    print "Codalyzer Interactive Mode\n";
    while(1) {
		CA::Common::interactiveCmd();
    }
} else {
	# Cronjob mode
	my $logfile = $cmd_args{f} || $config{logfile};
	print "Using logfile: \"$logfile\"\n";
	
	# Clean out the changes table
	$dbh->do('TRUNCATE TABLE latest');
	
	# Fetch latest log
	#CA::Common::getLatestLog(
	#	$logfile, $config{transfer_protocol}, 'cron');
	
	if(exists($cmd_args{d})) {
		if($cmd_args{d} eq 'flush') {
			# Flush out tables
			CA::SimpleDB::flushTable();
		}
	}
	
	# Run the parser
	CA::Parser::parser(
		$logfile, CA::Common::logHist('numlines', $logfile));
	
	# Run the main loop control and core operations
    CA::Core::handler();
	
	# Deletes profiles where games = 0 or kills AND deaths = 0
	CA::Common::cleanUpProfiles();
	
	# Loops through all clans and updates profiles with no clan, 
	# if their handle match the clantag
	# CA::Common::searchClanTag();
	
	# Optimize tables
	CA::SimpleDB::optimizeTable();
}

END {
	my $somany = time - $^T;
	printf("Generated in: %02d:%02d:%02d\n", 
		int($somany / 3600), 
		int(($somany % 3600) / 60), 
		int($somany % 60));
}

1;