package CA::Toolbox;

# Codalyzer
# - Parser Toolbox
# Parser Functions used by the parser 

use strict;
use warnings;
use SQL::Abstract;
use List::Util qw(max min);
use Carp;
use lib '/home/homer/ju/jussimik/CA-Parser/';
use CA::SimpleDB;

my $dbh = CA::SimpleDB::getDbh();

# Generate SQL from Perl data structures
my $orm = SQL::Abstract->new();

# subroutine: addNewGame (%hash)
# -------------------------------------------------------------
# LOG IDENTIFIER: InitGame
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
# LOG IDENTIFIER: J
# You can figure this out
sub addNewPlayer {
	my($args) = @_;
	
	if(CA::Common::playerExist($args->{handle}, $args->{gid})) {
		return;
	} 
	else {
		my($sth, @bind) = $orm->insert('players', \%$args);
		$dbh->do($sth, undef, @bind) 
			or croak "CA (error): Couldn't add new player: " . DBI->errstr;

		if(not(CA::Common::hasProfile($args->{handle}))) {
			# Add a profile to the player
			CA::Common::makeProfile($args->{handle});
		}
	}
}

# subroutine: addDamageHit (%hash)
# -------------------------------------------------------------
# LOG IDENTIFIER: D
# Adds a damage hit 
sub addDamageHit {
	my($args) = @_;
	$args->{mods} =~ s/MOD_//;
	
	my($sth, @bind) = $orm->insert('hits', \%$args);
	
	$dbh->do($sth, undef, @bind)
		or croak "CA (error): Couldn't add damage hit: " . DBI->errstr;
	
	# In CoD and Cod:MW teams are not specified with kills, only hits
	# (NOTE: But mods like pam4 and promod supports this)
	if(not(CA::Common::usingMod($args->{gid}))) {
		if(CA::Common::missingTeam($args->{hitman}, $args->{gid})) {
			CA::Common::assignTeam($args->{hitman}, $args->{h_team}, $args->{gid});
		}
		if(CA::Common::missingTeam($args->{wounded}, $args->{gid})) {
			CA::Common::assignTeam($args->{wounded}, $args->{w_team}, $args->{gid});
		}
	}
	
	# Nick changes are not logged, so we have to double check
	if(not(CA::Common::playerExist($args->{hitman}, $args->{gid}))) {
		CA::Common::changeHandle($args->{killer}, $args->{h_hash}, $args->{gid});
	}
	if(not(CA::Common::playerExist($args->{wounded}, $args->{gid}))) {
		CA::Common::changeHandle($args->{corpse}, $args->{w_hash}, $args->{gid});
	}
}

# subroutine: addKill (%hash)
# -------------------------------------------------------------
# LOG IDENTIFIER: K
# Adds a new kill
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
	
	# Don't think we need this after all..
	#if(CA::Common::usingMod($args->{gid})) {
	#	CA::Common::assignTeam($args->{killer}, $args->{k_team}, $args->{gid});
	#	CA::Common::assignTeam($args->{corpse}, $args->{c_team}, $args->{gid});
	#}
	
	#if(CA::Common::firstKill) {
	#	$dbh->do('INSERT INTO survivability (gid, handle, firstkill) VALUES (?,?,?)',
	#		undef, $args->{killer}, $args->{gid}); 
	#}
	
	#if(CA::Common::lastKill) {
	#	$dbh->do('INSERT INTO survivability (gid, handle, lastkill) VALUES (?,?,?)',
	#		undef, $args->{killer}, $args->{gid}); 
	#}
}

# subroutine: addQuote (%hash)
# -------------------------------------------------------------
# LOG IDENTIFIER: say (sayteam)
# Adds messages
sub addQuote {
	my($args) = @_;
	
	my($sth, @bind) = $orm->insert('quotes', \%$args);
	
	$dbh->do($sth, undef, @bind)
		or croak "CA (error): Couldn't add qoute: " . DBI->errstr;
}

# subroutine: addAction (%hash)
# -------------------------------------------------------------
# LOG IDENTIFIER: A
# Adds actions like bomb plants/defuse
sub addAction {
	my($args) = @_;
	
	my($sth, @bind) = $orm->insert('actions', \%$args);
	
	$dbh->do($sth, undef, @bind)
		or croak "CA (error): Couldn't add action: " . DBI->errstr;
}

# subroutine: addGameResult (%hash)
# -------------------------------------------------------------
# LOG IDENTIFIER: MO
# Adds the game result, mostly from mods like pam4
sub addGameResult {
	my($args) = @_;
	my $high = max($args->{score1}, $args->{score2});
	my $low = min($args->{score1}, $args->{score2});
	
	delete $args->{score1}; 
	delete $args->{score2};
	
	if($args->{winner} eq 'axis') {
		$args->{axisscore} = $high;
		$args->{alliesscore} = $low;
	} else {
		$args->{axisscore} = $low;
		$args->{alliesscore} = $high;
	}
	
	delete $args->{winner};
	
	my %where = (id => $args->{id});
	my($sth, @bind) = $orm->update('games', \%$args, \%where);
	
	$dbh->do($sth, undef, @bind)
		or croak "CA (error): Couldn't update game result: " . DBI->errstr;
}

# subroutine: addFinished (%hash)
# -------------------------------------------------------------
# LOG IDENTIFIER: ShutdownGame
# Updates the stop time to the games table
sub addFinished {
	my($args) = @_;

	$dbh->do('UPDATE games SET stop=? WHERE id=?',
		undef, $args->{ts}, $args->{id})
		or croak "CA (error): Couldn't set game stop " . DBI->errstr;
}

# subroutine: addExitLevel (%hash)
# -------------------------------------------------------------
# LOG IDENTIFIER: Exitlevel
# Declares a game finished
sub addExitLevel {
	my($args) = @_;
	$dbh->do('UPDATE games SET finish=? WHERE id=?',
		undef, 1, $args->{gid})
		or croak "CA (error): Couldn't finish game: " . DBI->errstr;
}

# subroutine: addJoinTeam
# -------------------------------------------------------------
# LOG IDENTIFIER: JT
# Updates players according to the chosen team
sub addJoinTeam {
	my($args) = @_;
	
	# We don't wanna update teams after the match is over
	return if CA::Common::gameOver($args->{gid});
	
	# We flip the teams (because they change sides after 10 rounds)
	$args->{team} = (($args->{team} eq 'axis') ? 'allies' : 'axis');
	
	$dbh->do('UPDATE players SET team=? WHERE handle=? AND gid=?',
		undef, $args->{team}, $args->{handle}, $args->{gid})
		or croak "CA (error): Couldn't set player team: " . DBI->errstr;
}

# subroutine: addRoundStart
# -------------------------------------------------------------
# LOG IDENTIFIER: RS
# Keeps track of the current round (in the war/match)
sub addRoundStart {
	my($args) = @_;
	
	# Mark the game as a clanmatch
	CA::Common::going2war($args->{id}, $args->{rcount});
	
	my %where = (id => $args->{id});
	my($sth, @bind) = $orm->update('games', \%$args, \%where);
	
	$dbh->do($sth, undef, @bind)
		or croak "CA (error): Couldn't update game rcount: " . DBI->errstr;
}

1;