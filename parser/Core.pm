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
	my $game_ids = $dbh->prepare('SELECT id FROM games ORDER BY id');
	$game_ids->execute();
	
	#-- Gid loop ------------------------------->
	while(my @gid = $game_ids->fetchrow_array()) {
		# Delete short games or matches with 5 or less rounds
		CA::Common::cleanUpGames($gid[0]);
	}
	
	my $players = $dbh->prepare('SELECT handle FROM players ORDER BY id');
	$players->execute();
	
	#--Players loop------------------------------->
	while(my @player = $players->fetchrow_array()) {
		CA::Common::addProfileData($player[0]);
	}
}

1;