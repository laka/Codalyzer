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
getopt('i', \%cmd_args);

if(exists($cmd_args{i})) {
    # Starting interactive mode
    print "Codalyzer Interactive Mode\n";
    while(1) {
		CA::Common::interactiveCmd();
    }
} else {
	# Cronjob mode
	my $logfile = $ARGV[0] || $config{logfile};
	print "Using logfile: \"$logfile\"\n";
	
	CA::Parser::parser($logfile);
    CA::Core::handler();
}

END {
    my $somany = ((time - $^T) / 60);
    print "Generated in $somany minute(s)\n";
}

1;