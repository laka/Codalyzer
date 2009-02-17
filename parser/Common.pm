package OcSP::Common;

# OpenCodStats
# - Common Functions
# A gathering of functions used frequently throughout the application

use strict;
use warnings;
use lib '/home/homer/ju/jussimik/OCS-Parser/';
use OcSP::SimpleDB;

my $dbh = OcSP::SimpleDB::getDbh();

# subroutine: interactiveCmd
# -------------------------------------------------------------
# Dropping the user to an interactive shell where he has
# full access to the entire application (through commands)
# Arguments:
#   1) command (the command to execute)

sub interactiveCmd {
    print "Command: ";
    my $command = <STDIN>;
    chomp($command);
    my %commands = (
        quit => sub { print("Leaving..\n"); exit },
    );

    while(my($key,$value) = each %commands) {
        if($command eq $key) {
            $value->();
        } else {
            print "Unknown command, try again (or help)\n";
        }
    }
}

# subroutine: lastGid
# -------------------------------------------------------------
# Returns the last game id found in the games table
# Arguments:
# 	None

sub lastGid {
	my $row = $dbh->selectrow_hashref("
		SELECT id FROM games ORDER BY id DESC LIMIT 1
	");
	return $row->{id};
}

sub analyzeLogFile {
}

1;