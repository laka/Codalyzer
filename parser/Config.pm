package CA::Config;

# Codalyzer
# - Main Config File
# Reading and writing all the config files, so it may be used across packages

use strict;
use warnings;
use lib '/home/homer/ju/jussimik/CA-Parser/';

# subroutine: readConfig
# -------------------------------------------------------------
# Reads the user-created (via the chosen interface) config-file
# and saves it in the hash "%config".

sub readConfig {
    my %config;
    my $config = "/home/homer/ju/jussimik/CA-Parser/CA/config";
    if(!(-r $config)) {
		# Do something here
    }
    open CONFIG, '<', $config or die $!;
    while(<CONFIG>) {
        next if (($_ eq "\n") || /^\;/); # skip comments and blank lines
        chomp;
        my($setting, $value) = split(/=/, $_);
        $config{$setting} = $value;
    }
    return %config;
}

# Weapon modification map
our %Mods = (
    'GRENADE_SPLASH EXPLOSIVE' => 'grenade_dh',
    'MELEE' => 'knife',
    'EXPLOSIVE' => 'bomb',
);

1;