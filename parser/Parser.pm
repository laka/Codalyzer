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
	my $version = "cod40";
	my @to_parse = read_file("pam4.log");
	
	LINE: for (@to_parse) {
		my $gid = CA::Common::lastGid();
		
		# Check if line matches regex..					# ..and if so - do this (the sub name speaks for itself)
		/$CA::Regex::Parser{InitGame}{all}/			&& do { CA::Toolbox::addNewGame({ 
																	start => $1,
																	mods => $2,
																	type => $3,
																	version => $4,
																	map => $5});
																	next LINE; 
															};
																	
		/$CA::Regex::Parser{Join}{$version}/			&& do { CA::Toolbox::addNewPlayer({
																	ts => $1,
																	hash => $2,
																	pid => $3,
																	player => $4}); 
																	next LINE;
															};
		
		/$CA::Regex::Parser{Damage}{$version}/		&& do { CA::Toolbox::addDamageHit({
																	ts => $1,
																	w_hash => $2,
																	w_pid => $3,
																	w_team => $4,
																	wounded => $5,
																	h_hash => $6,
																	h_pid => $7,
																	h_team => $8,
																	hitman => $9,
																	weapon => $10,
																	damage => $11,
																	location => $12}); 
																	next LINE; 
															};
		
		/$CA::Regex::Parser{Kills}{$version}/			&& do { CA::Toolbox::addKill({
																	ts => $1,
																	c_hash => $2,
																	c_pid => $3,
																	c_team => $4,
																	corpse => $5,
																	k_hash => $6,
																	k_pid => $7,
																	k_team => $8,
																	killer => $9,
																	weapon => $10,
																	damage => $11,
																	mod => $12,
																	location => $13}); 
																	next LINE; 
															};
		
		/$CA::Regex::Parser{Quotes}{$version}/		&& do { CA::Toolbox::addQuote({
																	ts => $1,
																	hash => $2,
																	pid => $3,
																	player => $4,
																	qoute => $5}); 
																	next LINE; 		
															};
		
		/$CA::Regex::Parser{Action}{$version}/		&& do { CA::Toolbox::addAction({
																	ts => $1,
																	action => $2,
																	player => $3,
																	team => $4}); 
																	next LINE; 
															};
																	
		/$CA::Regex::Parser{Result}{$version}/		&& do { CA::Toolbox::addGameResult({
																	ts => $1,
																	winner => $2,
																	score1 => $3,
																	score2 => $4}); 
																	next LINE;
															};
																	
		/$CA::Regex::Parser{Finish}{$version}/		&& do { CA::Toolbox::addFinished({
																	ts => $1,
																	string => $2}); 
																	next LINE; 
															};
																	
		/$CA::Regex::Parser{Exitlev}{$version}/		&& do { CA::Toolbox::addExitLevel({
																	ts => $1,
																	string => $2}); 
																	next LINE; 
															};
																	
		/$CA::Regex::Parser{Jointeam}{$version}/		&& do { CA::Toolbox::addJoinTeam({
																	ts => $1,
																	hash => $2,
																	team => $3,
																	handle => $4}); 
																	next LINE; 
															};
		
		/$CA::Regex::Parser{Roundstart}{$version}/	&& do { CA::Toolbox::addRoundStart({
																	ts => $1,
																	nr => $2}); 
																	next LINE; 
															};
																	
		/$CA::Regex::Parser{Roundwin}{$version}/		&& do { CA::Toolbox::addRoundWin({
																	ts => $1,
																	winner => $2}); 
																	next LINE; 
															};
		
		
		/$CA::Regex::Parser{Timeout}{$version}/		&& do { CA::Toolbox::addTimeOut({
																	ts => $1,
																	team => $2,
																	who => $3}); 
																	next LINE; 
															};
		
		/$CA::Regex::Parser{Sidechange}{$version}/	&& do { CA::Toolbox::addSideChange({
																	ts => $1,
																	string => $2}); 
																	next LINE; 
															};
																	
		/$CA::Regex::Parser{Winners}{$version}/		&& do { CA::Toolbox::addGameWinners({
																	foo => $1,
																	bar => $2});
																	next LINE; 
															};
		
		/$CA::Regex::Parser{Loosers}{$version}/		&& do { CA::Toolbox::addGameLoosers({
																	foo => $1,
																	bar => $2});
																	next LINE; 
															};
	}
}

1;