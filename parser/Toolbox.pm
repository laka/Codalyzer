package CA::Toolbox;

# Codalyzer
# - Parser Toolbox
# Parser Functions used by the parser 

use strict;
use warnings;
use SQL::Abstract;
use Carp;
use lib '/home/homer/ju/jussimik/CA-Parser/';
use CA::SimpleDB;

my $dbh = CA::SimpleDB::getDbh();
my $orm = SQL::Abstract->new();

sub addNewGame {
	my ($args) = @_;
	# Global game id 
	my $gid = CA::Common::lastGid();
	
	# Grabbing the mod
	$args->{mods} =~ s/^.*fs_game\\.*\/(.*?)\\g_compass.*$/$1/;
	if($args->{mods} !~ /^[A-Za-z0-9]+$/i) {
		$args->{mods} = 'none';
	}
	
	# Translate the cod name to a number
	$args->{version} = CA::Common::name2version($args->{version});
	
	# Check to see if we have an ongoing game
	if(defined($gid)) {
		# Check for modes where InitGame restarts
		if(CA::Common::gameData('type', $gid) =~ /sd|sab/) {
			if((CA::Common::gameData('map', $gid) eq $args->{map}) &&
				(CA::Common::gameData('type', $gid) eq $args->{type}) &&
				(CA::Common::gameData('finish', $gid) == 0)) {
				# We assume the same game is ongoing
					$dbh->do('UPDATE games SET stop=? WHERE id=?',
						undef, '0', $gid)
						or croak "CA (error): Couldn't update game: " . DBI->errstr;
					return;
			}
		}
	}
	
	# If it's a new game, insert the data accordingly 
	my($sth, @bind) = $orm->insert('games', \%$args);
	
	#print $sth;
	#print join("-", @bind);
	#die();
	$dbh->do($sth, undef, @bind)
		or croak "CA (error): Couldn't add new game: " . DBI->errstr;
}

sub addNewPlayer {
	
}

sub addDamageHit {

}

sub addKill {

}

sub addQuote {

}

sub addAction {

}

sub addGameResult {

}

sub addFinished {

}

sub addExitLevel {

}

sub addJoinTeam {

}

sub addRoundStart {

}

sub addRoundWin {

}

sub addGameWinners {

}

sub addGameLoosers {

}

sub addTimeOut {

}

sub addSideChange {

}

1;