package CA::Core;

# Codalyzer
# - Core Operations
# The main functions are placed here (ELO-rating, Awards etc.)

use strict;
use warnings;
use Carp;
use lib '/home/homer/ju/jussimik/CA-Parser/';
use CA::SimpleDB;
use CA::Common;

my $dbh = CA::SimpleDB::getDbh();

# subroutine: handler
# -------------------------------------------------------------
# Starts a main loop control where we run a whole lot of core 
# functions at once (inside the same loop, instead of one loop
# for each function)

sub handler {
	
	CA::Common::killZombies();
		
	# Gid loop ------------------------------------------------
	my $game_ids = $dbh->prepare('SELECT id FROM games ORDER BY id');
	$game_ids->execute();
	
	while(my $ref = $game_ids->fetchrow_hashref) {
		# Delete short games or matches with 5 or less rounds
		CA::Common::cleanUpGames($ref->{id});
		
		# Run our ranking system
		eloRating($ref->{id});
		
		# Grab clan tags
		#findClanTags($ref->{id});
	}
	# END -----------------------------------------------------
	
	# Players loop --------------------------------------------
	my $players = $dbh->prepare('SELECT handle FROM players ORDER BY id');
	$players->execute();
	
	while(my $ref = $players->fetchrow_hashref()) {
		CA::Common::addProfileData($ref->{handle});
	}
	# END -----------------------------------------------------
}

# subroutine: eloRating
# -------------------------------------------------------------
# Our rating system defined. Visit 
# http://en.wikipedia.org/wiki/Elo_rating_system for the glory
# details

sub eloRating {
	my ($gid) = @_;
    my %scores = ();
	
	my $kills = $dbh->prepare("SELECT killer, corpse FROM kills WHERE gid=? ORDER BY id");
	$kills->execute($gid);
	
	while(my $ref = $kills->fetchrow_hashref()) {
		if(!$scores{$ref->{killer}}) {
			$scores{$ref->{killer}} = CA::Common::lastElo($ref->{killer});
		}
		if(!$scores{$ref->{corpse}}) {
			$scores{$ref->{corpse}} = CA::Common::lastElo($ref->{corpse});
		}
		
		my $change = 1 / (1 + 10 ** (($scores{$ref->{killer}} - $scores{$ref->{corpse}})/400));
		
		if($ref->{killer} ne $ref->{corpse}) {
			$scores{$ref->{killer}} += $change;
			$scores{$ref->{corpse}} -= $change;
		}
		elsif($ref->{killer} eq $ref->{corpse}) {
			$scores{$ref->{corpse}} -= $change;
		}
		
		my $update = $dbh->prepare(
            qq/UPDATE players SET elo=? WHERE handle=? AND gid=?/
        );
		
        while(my($key, $value) = each %scores) {
            $update->execute($value, $key, CA::Common::round($gid, 2));
        }
    }
}

sub findClanTags {
	my($gid) = @_;
	my @mates;
	
	my $query = $dbh->prepare('SELECT handle FROM players WHERE gid=? AND team=?');
	$query->execute($gid, 'axis');
	
	while(my $ref = $query->fetchrow_hashref()) {
		push(@mates, $ref->{handle});
	}
	
	my($str1, $str2) = @mates;

    my(@arr1) = split(//, $str1);
    my(@arr2) = split(//, $str2);

    my($common) = "";

    while (@arr1 && @arr2) {
        my($letter1) = shift(@arr1);
        my($letter2) = shift(@arr2);

        if ($letter1 eq $letter2) {
			$common .= $letter1;
        }
        else {
			last;
        }
    }

	$common =~ s/\w+$//;
	
    $dbh->do('UPDATE profiles SET clan=? WHERE team=? AND gid=?',
        undef, $common, 'axis', $gid)
		or croak "CA (error): Couldn't set profile clan " . DBI->errstr;
}

END {
	CA::Common::cleanUpProfiles();
	CA::Common::searchClanTag();
}

1;