package CA::Core;

# Codalyzer
# - Core Operations
# The main functions are placed here (ELO-rating, Awards etc.)

use strict;
use warnings;
use lib '/home/homer/ju/jussimik/CA-Parser/';
use CA::SimpleDB;
use CA::Common;

my $dbh = CA::SimpleDB::getDbh();

# subroutine: handle
# -------------------------------------------------------------
# Starts a main loop control where we run a whole lot of core 
# functions at once (inside the same loop, instead of one loop
# for each function)

sub handler {

	# Gid loop ------------------------------------------------
	my $game_ids = $dbh->prepare('SELECT id FROM games ORDER BY id');
	$game_ids->execute();
	
	while(my @gid = $game_ids->fetchrow_array()) {
		# Delete short games or matches with 5 or less rounds
		CA::Common::cleanUpGames($gid[0]);
		eloRating($gid[0]);
	}
	# END -----------------------------------------------------
	
	# Players loop --------------------------------------------
	my $players = $dbh->prepare('SELECT handle FROM players ORDER BY id');
	$players->execute();
	
	while(my @player = $players->fetchrow_array()) {
		CA::Common::addProfileData($player[0]);
	}
	# END -----------------------------------------------------
}

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



1;