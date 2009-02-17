package OcSP::Config;

# OpenCodStats
# - Main Config File
# Reading and writing all the config files, so it may be used across packages

use strict;
use warnings;
use lib '/home/homer/ju/jussimik/OCS-Parser/';

# subroutine: readConfig
# -------------------------------------------------------------
# Reads the user-created (via the chosen interface) config-file
# and saves it in the hash "%config".
# Arguments:
#   None

sub readConfig {
    my %config;
    my $config = "config";
    if(!(-r $config)) {
		# Do something here
    }
    open CONFIG, '<', $config or die $!;
    while(<CONFIG>) {
        next if (($_ eq "\n") || /^\#/); # skip comments and blank lines
        chomp;
        my($setting, $value) = split(/=/, $_);
        $config{$setting} = $value;
    }
    return %config;
}

1;