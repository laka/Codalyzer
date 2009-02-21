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
	
	while(my @gid = $game_ids->fetchrow_array()) {
		# Delete short games or matches with 5 or less rounds
		CA::Common::cleanUpGames($gid[0]);
	}
}

1;