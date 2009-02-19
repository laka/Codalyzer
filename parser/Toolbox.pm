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

# subroutine: addNewGame (%hash)
# -------------------------------------------------------------
# Adds a new game

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

# subroutine: addNewPlayer (%hash)
# -------------------------------------------------------------
# You can figure this out

sub addNewPlayer {
	my($args) = @_;
	$args->{handle} =~ s/\s+$//;
	
	if(CA::Common::playerExist($args->{handle}, $args->{gid})) {
		return;
	} 
	else {
		my($sth, @bind) = $orm->insert('players', \%$args);
		$dbh->do($sth, undef, @bind) 
			or croak "CA (error): Couldn't add new player: " . DBI->errstr;
	}
}

sub addDamageHit {
	my($args) = @_;
	$args->{mods} =~ s/MOD_//;
	
	my($sth, @bind) = $orm->insert('hits', \%$args);
	
	$dbh->do($sth, undef, @bind)
		or croak "CA (error): Couldn't add damage hit: " . DBI->errstr;
	
	# In CoD and Cod:MW teams are not specified with kills, only hits
	# (NOTE: But mods like pam4 and promod supports this)
	if(not(CA::Common::usingMod($args->{gid}))) {
		CA::Common::assignTeam($args->{hitman}, $args->{h_team}, $args->{gid});
		CA::Common::assignTeam($args->{wounded}, $args->{w_team}, $args->{gid});
	}
	
	# Nick changes are not logged, so we have to double check
	if(not(CA::Common::playerExist($args->{hitman}, $args->{gid}))) {
		CA::Common::changeHandle($args->{killer}, $args->{h_hash}, $args->{gid});
	}
	if(not(CA::Common::playerExist($args->{wounded}, $args->{gid}))) {
		CA::Common::changeHandle($args->{corpse}, $args->{w_hash}, $args->{gid});
	}
}

sub addKill {
	my($args) = @_;
	$args->{mods} =~ s/MOD_//;
	
	# Convert mods to weapons (MELEE == Knife)
	if(exists($CA::Config::Mods{$args->{mods}})) {
		$args->{weapon} = $CA::Config::Mods{$args->{mods}};
	}
	
	# Silly
	if($args->{weapon} eq 'deserteaglegold_mp') {
		$args->{weapon} = 'deserteagle_mp';
	}
	
	my($sth, @bind) = $orm->insert('kills', \%$args);
	
	$dbh->do($sth, undef, @bind)
		or croak "CA (error): Couldn't add kill: " . DBI->errstr;

	# Nick changes are not logged, so we have to double check
	if(not(CA::Common::playerExist($args->{killer}, $args->{gid}))) {
		CA::Common::changeHandle($args->{killer}, $args->{k_hash}, $args->{gid});
	}
	if(not(CA::Common::playerExist($args->{corpse}, $args->{gid}))) {
		CA::Common::changeHandle($args->{corpse}, $args->{c_hash}, $args->{gid});
	}
	
	# If we still not haven't found a team for the player, we try the set
	# the oppsite team of the killer/corpse (if they have any)
	if(CA::Common::missingTeam($args->{killer}, $args->{gid})) {
		CA::Common::try2findTeam($args->{killer}, $args->{corpse}, $args->{gid});
	}
	if(CA::Common::missingTeam($args->{corpse}, $args->{gid})) {
		CA::Common::try2findTeam($args->{corpse}, $args->{killer}, $args->{gid});
	}
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