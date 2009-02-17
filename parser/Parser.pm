package OcSP::Parser;

# OpenCodStats
# - Parser
# The main parser for the application

use strict;
use warnings;
use File::Slurp;
use lib '/home/homer/ju/jussimik/OCS-Parser/';
use OcSP::SimpleDB;
use OcSP::Common;
use OcSP::Toolbox;
use OcSP::Regex;

my $dbh = OcSP::SimpleDB::getDbh();

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
		my $gid = OcSP::Common::lastGid();
		
		# Check if line matches regex..					# ..and if so - do this (the sub name speaks for itself)
		/$OcSP::Regex::Parser{InitGame}{all}/			&& do { OcSP::Toolbox::addNewGame({ 
																	ts => $1,
																	mod => $2,
																	gametype => $3,
																	codversion => $4,
																	mapname => $5});
																	next LINE; 
															};
																	
		/$OcSP::Regex::Parser{Join}{$version}/			&& do { OcSP::Toolbox::addNewPlayer({
																	ts => $1,
																	hash => $2,
																	pid => $3,
																	player => $4}); 
																	next LINE;
															};
		
		/$OcSP::Regex::Parser{Damage}{$version}/		&& do { OcSP::Toolbox::addDamageHit({
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
		
		/$OcSP::Regex::Parser{Kills}{$version}/			&& do { OcSP::Toolbox::addKill({
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
		
		/$OcSP::Regex::Parser{Quotes}{$version}/		&& do { OcSP::Toolbox::addQuote({
																	ts => $1,
																	hash => $2,
																	pid => $3,
																	player => $4,
																	qoute => $5}); 
																	next LINE; 		
															};
		
		/$OcSP::Regex::Parser{Action}{$version}/		&& do { OcSP::Toolbox::addAction({
																	ts => $1,
																	action => $2,
																	player => $3,
																	team => $4}); 
																	next LINE; 
															};
																	
		/$OcSP::Regex::Parser{Result}{$version}/		&& do { OcSP::Toolbox::addGameResult({
																	ts => $1,
																	winner => $2,
																	score1 => $3,
																	score2 => $4}); 
																	next LINE;
															};
																	
		/$OcSP::Regex::Parser{Finish}{$version}/		&& do { OcSP::Toolbox::addFinished({
																	ts => $1,
																	string => $2}); 
																	next LINE; 
															};
																	
		/$OcSP::Regex::Parser{Exitlev}{$version}/		&& do { OcSP::Toolbox::addExitLevel({
																	ts => $1,
																	string => $2}); 
																	next LINE; 
															};
																	
		/$OcSP::Regex::Parser{Jointeam}{$version}/		&& do { OcSP::Toolbox::addJoinTeam({
																	ts => $1,
																	hash => $2,
																	team => $3,
																	handle => $4}); 
																	next LINE; 
															};
		
		/$OcSP::Regex::Parser{Roundstart}{$version}/	&& do { OcSP::Toolbox::addRoundStart({
																	ts => $1,
																	nr => $2}); 
																	next LINE; 
															};
																	
		/$OcSP::Regex::Parser{Roundwin}{$version}/		&& do { OcSP::Toolbox::addRoundWin({
																	ts => $1,
																	winner => $2}); 
																	next LINE; 
															};
		
		
		/$OcSP::Regex::Parser{Timeout}{$version}/		&& do { OcSP::Toolbox::addTimeOut({
																	ts => $1,
																	team => $2,
																	who => $3}); 
																	next LINE; 
															};
		
		/$OcSP::Regex::Parser{Sidechange}{$version}/	&& do { OcSP::Toolbox::addSideChange({
																	ts => $1,
																	string => $2}); 
																	next LINE; 
															};
																	
		/$OcSP::Regex::Parser{Winners}{$version}/		&& do { OcSP::Toolbox::addGameWinners({
																	foo => $1,
																	bar => $2});
																	next LINE; 
															};
		
		/$OcSP::Regex::Parser{Loosers}{$version}/		&& do { OcSP::Toolbox::addGameLoosers({
																	foo => $1,
																	bar => $2});
																	next LINE; 
															};
	}
}

1;