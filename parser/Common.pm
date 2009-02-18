package CA::Common;

# Codalyzer
# - Common Functions
# A gathering of functions used frequently throughout the application

use strict;
use warnings;
use lib '/home/homer/ju/jussimik/CA-Parser/';
use CA::SimpleDB;

my $dbh = CA::SimpleDB::getDbh();

# subroutine: interactiveCmd ($command)
# -------------------------------------------------------------
# Dropping the user to an interactive shell where he has
# full access to the entire application (through commands)

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

sub lastGid {
	my $row = $dbh->selectrow_hashref("
		SELECT id FROM games ORDER BY id DESC LIMIT 1
	");
	return $row->{id};
}

# subroutine: gameData ($what, $gid)
# -------------------------------------------------------------
# Lookup function for the games table

sub gameData {
    my ($what, $gid) = @_;
    my $sth = $dbh->prepare(
        qq/SELECT $what FROM games WHERE id=?/
    );
    $sth->execute($gid);
    return $sth->fetchrow() 
		or return "CA (warn): Couldn't find data on GID $gid";
}

# subroutine: name2version ($version)
# -------------------------------------------------------------
# Translates the Call of Duty name to a number
# Like CoD:MW is number 4 in the serie

sub name2version {
	my($version) = @_;
	my %map = (
		'call of duty' => 1,
		'cod:united offensive' => 1.5,
		'call of duty 4' => 4,
		'Call of Duty: World at War' => 5,
	);
	return $map{lc $version} || 'unknown';
}

1;