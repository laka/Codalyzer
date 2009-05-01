package CA::Parser;

# Codalyzer
# - Parser
# The main parser for the application

use strict;
use warnings;
use File::Slurp;
use lib '/home/homer/ju/jussimik/CA-Parser/';
use CA::SimpleDB;
use CA::Common;
use CA::Toolbox;
use CA::Regex;
use CA::Core;

my $dbh = CA::SimpleDB::getDbh();

# subroutine: parser
# -------------------------------------------------------------
# This is the main parser for the Call of Duty-series. Basic 
# array loop by slurping the logfile. Then it goes through each
# line and checks it up against Regex.pm. It uses functions 
# from Toolbox.pm, so we can keep it nice, clean and easy to
# modify. 
# Arguments:
#	1) logfile (the logfile to parse)
#	2) linestart (where in the logfile should we start parsing)
sub parser {
	my($logfile) = @_;
	my @rawfile = read_file($logfile);
	#my @to_parse = @rawfile[$linestart..$#rawfile];
	
	my $version;
	my $gid;
	
	LINE: for (@rawfile) {
		next LINE if $_ =~ /---/;
		$gid = CA::Common::lastGid();
		
		if(defined($gid)) {
			$version = 'cod' . CA::Common::gameData('version', $gid);
		} else {
			$version  = 'cod40';
		}
		
		# Check if line matches regex				# And if so do this (the sub name speaks for itself)
		/$CA::Regex::Parser{InitGame}{all}/			&& do { CA::Toolbox::addNewGame({ 
																	start => CA::Common::ts2seconds($1),
																	mods => $2,
																	type => $3,
																	version => $4,
																	map => CA::Common::niceString($5)});
																	next LINE; 
															};
																	
		/$CA::Regex::Parser{Join}{all}/				&& do { CA::Toolbox::addNewPlayer({
																	gid => $gid,
																	ts => CA::Common::ts2seconds($1),
																	hash => substr($2, -8),
																	handle => CA::Common::niceString($3)}); 
																	next LINE;
															};
		
		/$CA::Regex::Parser{Damage}{all}/			&& do { CA::Toolbox::addDamageHit({
																	ts => CA::Common::ts2seconds($1),
																	w_hash => substr($2, -8),
																	w_team => $3,
																	wounded => CA::Common::niceString($4),
																	h_hash => substr($5, -8),
																	h_team => $6,
																	hitman => CA::Common::niceString($7),
																	weapon => $8,
																	damage => $9,
																	mods => $10,
																	location => CA::Common::niceString($11),
																	gid => $gid}); 
																	next LINE; 
															};
		
		/$CA::Regex::Parser{Kills}{all}/			&& do { CA::Toolbox::addKill({
																	ts => CA::Common::ts2seconds($1),
																	c_hash => substr($2, -8),
																	c_team => $3,
																	corpse => CA::Common::niceString($4),
																	k_hash => substr($5, -8),
																	k_team => $6,
																	killer => CA::Common::niceString($7),
																	weapon => $8,
																	damage => $9,
																	mods => $10,
																	location => CA::Common::niceString($11),
																	gid => $gid}); 
																	next LINE; 
															};
		
		/$CA::Regex::Parser{Quotes}{all}/			&& do { CA::Toolbox::addQuote({
																	ts => CA::Common::ts2seconds($1),
																	handle => CA::Common::niceString($2),
																	quote => CA::Common::niceString($3),
																	gid => $gid}); 
																	next LINE; 		
															};
		
		/$CA::Regex::Parser{Action}{$version}/		&& do { CA::Toolbox::addAction({
																	ts => CA::Common::ts2seconds($1),
																	action => $2,
																	handle => CA::Common::niceString($3),
																	gid => $gid}); 
																	next LINE; 
															};
																	
		/$CA::Regex::Parser{Result}{$version}/		&& do { CA::Toolbox::addGameResult({
																	winner => $1,
																	score1 => $2,
																	score2 => $3,
																	id => $gid}); 
																	next LINE;
															};
																	
		/$CA::Regex::Parser{Exitlev}{all}/			&& do { CA::Toolbox::addExitLevel({
																	ts => CA::Common::ts2seconds($1),
																	gid => $gid}); 
																	next LINE; 
															};
																	
		/$CA::Regex::Parser{Jointeam}{all}/			&& do { CA::Toolbox::addJoinTeam({
																	ts => CA::Common::ts2seconds($1),
																	hash => $2,
																	team => $3,
																	handle => CA::Common::niceString($4),
																	gid => $gid}); 
																	next LINE; 
															};
		
		/$CA::Regex::Parser{Roundstart}{all}/		&& do { CA::Toolbox::addRoundStart({
																	rcount => $1,
																	id => $gid}); 
																	next LINE; 
															};
															
#		/$CA::Regex::Parser{Finish}{$version}/		&& do { CA::Toolbox::addFinished({
#																	ts => CA::Common::ts2seconds($1),
#																	id => $gid});
#																	next LINE; 
#															};													
																	
#		/$CA::Regex::Parser{Roundwin}{$version}/	&& do { CA::Toolbox::addRoundWin({
#																	ts => CA::Common::ts2seconds($1),
#																	winner => $2}); 
#																	next LINE; 
#															};
		
#		/$CA::Regex::Parser{Timeout}{$version}/		&& do { CA::Toolbox::addTimeOut({
#																	ts => CA::Common::ts2seconds($1),
#																	team => $2,
#																	who => CA::Common::niceString($3)}); 
#																	next LINE; 
#															};
		
#		/$CA::Regex::Parser{Sidechange}{$version}/	&& do { CA::Toolbox::addSideChange({
#																	ts => CA::Common::ts2seconds($1),
#																	string => $2}); 
#																	next LINE; 
#															};
																	
#		/$CA::Regex::Parser{Winners}{$version}/		&& do { CA::Toolbox::addGameWinners({
#																	foo => CA::Common::ts2seconds($1),
#																	bar => $2});
#																	next LINE; 
#															};
		
#		/$CA::Regex::Parser{Loosers}{$version}/		&& do { CA::Toolbox::addGameLoosers({
#																	foo => CA::Common::ts2seconds($1),
#																	bar => $2});
#																	next LINE; 
#															};

	}
}

1;