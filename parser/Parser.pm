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

# ONLY FOR TESTING
END {
	parser();
}

sub parser {
	#my($logfile, $linestart) = @_;
	#my @rawfile = read_file($logfile);
	#my @to_parse = @rawfile[$linestart..$#rawfile];
	
	# ONLY FOR TESTING
	CA::Common::flushTable();
	my $version = "cod40";
	my @to_parse = read_file("pam4.log");
	
	LINE: for (@to_parse) {
		my $gid = CA::Common::lastGid();
		
		# Check if line matches regex..					# ..and if so - do this (the sub name speaks for itself)
		/$CA::Regex::Parser{InitGame}{all}/			&& do { CA::Toolbox::addNewGame({ 
																	start => CA::Common::ts2seconds($1),
																	mods => $2,
																	type => $3,
																	version => $4,
																	map => $5});
																	next LINE; 
															};
																	
		/$CA::Regex::Parser{Join}{$version}/			&& do { CA::Toolbox::addNewPlayer({
																	gid => $gid,
																	ts => CA::Common::ts2seconds($1),
																	hash => substr($2, -8),
																	handle => $3}); 
																	next LINE;
															};
		
		/$CA::Regex::Parser{Damage}{$version}/		&& do { CA::Toolbox::addDamageHit({
																	ts => CA::Common::ts2seconds($1),
																	w_hash => substr($2, -8),
																	w_team => $3,
																	wounded => $4,
																	h_hash => substr($5, -8),
																	h_team => $6,
																	hitman => $7,
																	weapon => $8,
																	damage => $9,
																	mods => $10,
																	location => $11,
																	gid => $gid}); 
																	next LINE; 
															};
		
		/$CA::Regex::Parser{Kills}{$version}/			&& do { CA::Toolbox::addKill({
																	ts => CA::Common::ts2seconds($1),
																	c_hash => substr($2, -8),
																	c_team => $3,
																	corpse => $4,
																	k_hash => substr($5, -8),
																	k_team => $6,
																	killer => $7,
																	weapon => $8,
																	damage => $9,
																	mods => $10,
																	location => $11,
																	gid => $gid}); 
																	next LINE; 
															};
		
		/$CA::Regex::Parser{Quotes}{$version}/		&& do { CA::Toolbox::addQuote({
																	ts => CA::Common::ts2seconds($1),
																	hash => $2,
																	pid => $3,
																	player => $4,
																	qoute => $5}); 
																	next LINE; 		
															};
		
		/$CA::Regex::Parser{Action}{$version}/		&& do { CA::Toolbox::addAction({
																	ts => CA::Common::ts2seconds($1),
																	action => $2,
																	player => $3,
																	team => $4}); 
																	next LINE; 
															};
																	
		/$CA::Regex::Parser{Result}{$version}/		&& do { CA::Toolbox::addGameResult({
																	ts => CA::Common::ts2seconds($1),
																	winner => $2,
																	score1 => $3,
																	score2 => $4}); 
																	next LINE;
															};
																	
		/$CA::Regex::Parser{Finish}{$version}/		&& do { CA::Toolbox::addFinished({
																	ts => CA::Common::ts2seconds($1),
																	string => $2}); 
																	next LINE; 
															};
																	
		/$CA::Regex::Parser{Exitlev}{$version}/		&& do { CA::Toolbox::addExitLevel({
																	ts => CA::Common::ts2seconds($1),
																	string => $2}); 
																	next LINE; 
															};
																	
		/$CA::Regex::Parser{Jointeam}{$version}/		&& do { CA::Toolbox::addJoinTeam({
																	ts => CA::Common::ts2seconds($1),
																	hash => $2,
																	team => $3,
																	handle => $4}); 
																	next LINE; 
															};
		
		/$CA::Regex::Parser{Roundstart}{$version}/	&& do { CA::Toolbox::addRoundStart({
																	ts => CA::Common::ts2seconds($1),
																	nr => $2}); 
																	next LINE; 
															};
																	
		/$CA::Regex::Parser{Roundwin}{$version}/		&& do { CA::Toolbox::addRoundWin({
																	ts => CA::Common::ts2seconds($1),
																	winner => $2}); 
																	next LINE; 
															};
		
		
		/$CA::Regex::Parser{Timeout}{$version}/		&& do { CA::Toolbox::addTimeOut({
																	ts => CA::Common::ts2seconds($1),
																	team => $2,
																	who => $3}); 
																	next LINE; 
															};
		
		/$CA::Regex::Parser{Sidechange}{$version}/	&& do { CA::Toolbox::addSideChange({
																	ts => CA::Common::ts2seconds($1),
																	string => $2}); 
																	next LINE; 
															};
																	
		/$CA::Regex::Parser{Winners}{$version}/		&& do { CA::Toolbox::addGameWinners({
																	foo => CA::Common::ts2seconds($1),
																	bar => $2});
																	next LINE; 
															};
		
		/$CA::Regex::Parser{Loosers}{$version}/		&& do { CA::Toolbox::addGameLoosers({
																	foo => CA::Common::ts2seconds($1),
																	bar => $2});
																	next LINE; 
															};
	}
}

1;