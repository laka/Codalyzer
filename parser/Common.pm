package CA::Common;

# Codalyzer
# - Common Functions
# A gathering of functions used frequently throughout the application

use strict;
use warnings;
use Carp;
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
    my $sth = $dbh->prepare("SELECT $what FROM games WHERE id=?");
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

# subroutine: ts2seconds ($ts)
# -------------------------------------------------------------
# Converts timestamps in the log to seconds

sub ts2seconds {
    my @t = split(':', $_[0]);
    return $t[0]*60 + $t[1];
}

# subroutine: usingMod ($gid)
# -------------------------------------------------------------
# Checks to see if the game is running any mod (like pam4)

sub usingMod {
	my($gid) = @_;
	my $row = $dbh->selectrow_hashref('SELECT mods FROM games WHERE id=?',
		undef, $gid);
		
	if($row->{mods} eq 'none') {
		return;
	} else { return 1; } 
}

# subroutine: assignTeam ($player, $team, $gid)
# -------------------------------------------------------------
# Put the player on the team parsed from damage hits

sub assignTeam {
	my($player, $team, $gid) = @_;
	$dbh->do('UPDATE players SET team=? WHERE handle=? AND gid=?',
		undef, $team, $player, $gid)
		or croak "CA (error): Couldn't assign team: " . DBI->errstr;
}

# subroutine: playerExist ($player, $gid)
# -------------------------------------------------------------
# Checks the existens of a player (so we don't add him twice)

sub playerExist {
	my($player, $gid) = @_;

	my $sth = $dbh->prepare("SELECT id FROM players WHERE handle=? AND gid=?");
    $sth->execute($player, $gid);
		
	if($sth->rows() == 0) {
		return;
	} else { return 1; }
}

# subroutine: round ($gid, $decimal)
# -------------------------------------------------------------
# Round function used in the elo-rating

sub round {
	my $number = shift || 0;
	my $dec = 10 ** (shift || 0);
	return int( $dec * $number + .5 * ($number <=> 0)) / $dec;
}

# subroutine: missingTeam ($player, $gid)
# -------------------------------------------------------------
# Check if the player is missing team membership

sub missingTeam {

}

# subroutine: changeHandle ($handle, $hash, $gid)
# -------------------------------------------------------------
# Change the players nick

sub changeHandle {
	my($handle, $hash, $gid) = @_;
	
	# Old handle
	my $row = $dbh->selectrow_hashref(
		'SELECT handle AS old_handle FROM players WHERE hash=? AND gid=?',
		undef, $hash, $gid);
		
	$dbh->do('UPDATE players SET handle=? WHERE hash=? AND gid=?',
		undef, $handle, $hash, $gid);
		
	$dbh->do('UPDATE kills SET killer=? WHERE killer=? AND gid=?',
		undef, $handle, $row->{old_handle}, $gid);
		
	$dbh->do('UPDATE kills SET corpse=? WHERE corpse=? AND gid=?',
		undef, $handle, $row->{old_handle}, $gid);
		
	$dbh->do('UPDATE hits SET hitman=? WHERE hitman=? AND gid=?',
		undef, $handle, $row->{old_handle}, $gid);
		
	$dbh->do('UPDATE hits SET wounded=? WHERE wounded=? AND gid=?',
		undef, $handle, $row->{old_handle}, $gid);
		
	$dbh->do('UPDATE hits SET hitman=? WHERE hitman=? AND gid=?',
		undef, $handle, $row->{old_handle}, $gid);
	
	# Don't know what to do with profiles yet, so..
	eval {
		$dbh->do('UPDATE profiles SET handle=? WHERE handle=?',
			undef, $handle, $row->{old_handle}, $gid);
	};
}

# subroutine: try2findTeam ($need_team, $has_team, $gid)
# -------------------------------------------------------------
# Setting the oppsite team of the killer/corpse, if they
# have any

sub try2findTeam {
	my($need, $has, $gid) = @_;
}
		
1;