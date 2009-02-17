package OcSP::Toolbox;

# OpenCodStats
# - Parser Toolbox
# Parser Functions used by the parser 

use strict;
use warnings;
use lib '/home/homer/ju/jussimik/OCS-Parser/';
use OcSP::SimpleDB;

my $dbh = OcSP::SimpleDB::getDbh();

sub addNewGame {
	my ($args) = @_;
	my $id = OcSP::Common::lastGid();
	
	# Grabbing the mod
	$args->{mod} =~ s/^.*fs_game\\.*\/(.*?)\\g_compass.*$/$1/;
	if($args->{mod} !~ /^[A-Za-z0-9]+$/i) {
		$args->{mod} = 'none';
	}
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