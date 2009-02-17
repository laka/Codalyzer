package OcSP::Init;

# OpenCodStats
# - Init file for the application
# Yes, start here

use strict;
use warnings;
use Getopt::Std;
use lib '/home/homer/ju/jussimik/OCS-Parser/';
use OcSP::Common;
use OcSP::Config;
use OcSP::Core;
use OcSP::SimpleDB;
use OcSP::Parser;

my $dbh = OcSP::SimpleDB::getDbh();

# Fetch command-line arguments
my %cmd_args;
getopt('i', \%cmd_args);

if(exists($cmd_args{i})) {
    # Starting interactive mode
    print "OpenCodStats Interactive Mode\n";
    while(1) {
		OcSP::Common::interactiveCmd();
    }
} else {
    # Cronjob mode
    OcSP::Common::analyzeLogFile();
}

END {
    my $somany = ((time - $^T) / 60);
    print "Generated in $somany minute(s)\n";
}

1;