package CA::Common;

# Codalyzer
# - Common Functions
# A gathering of functions used frequently throughout the application

use strict;
use warnings;
use Carp;
use Net::FTP;
use File::Slurp;
use lib '/home/homer/ju/jussimik/CA-Parser/';
use CA::SimpleDB;
use CA::Config;

my $dbh = CA::SimpleDB::getDbh();
my %config = CA::Config::readConfig();

# subroutine: getLogByFtp 
# -------------------------------------------------------------
# Downloads the logfile by ftp, based on current bytes in 
# the last (if any) logfile
sub getLogByFtp {
	# Amount of bytes in the file to NOT be downloaded
	my($logfile, $bytes) = @_;
	
	# Opens the FTP-handle
	my $ftp = Net::FTP->new($config{ftp_host}) 
		or  croak "CA (error): Couldn't fetch logfile by FTP: " . $@;
		
	$ftp->login($config{ftp_user}, $config{ftp_pass});
	
	$config{ftp_path} =~ s/^\///;
	$ftp->cwd($config{ftp_path});
	$ftp->get($logfile, $logfile, $bytes);
	$ftp->quit;
	
	my $filesize = -s $logfile;
	my @array = read_file($logfile);
	my $numlines = scalar(@array);
	
	$dbh->do('INSERT INTO loghist (filename, filesize, numlines) VALUES(?,?,?)',
		undef, $logfile, $filesize, $numlines);
}

# subroutine: interactiveCmd ($command)
# -------------------------------------------------------------
# Dropping the user to an interactive shell where he has
# full access to the entire application (through commands)
sub interactiveCmd {
    print "Command: ";
    my $command = <STDIN>;
    chomp($command);
    my %commands = (
        quit 		=> sub { print("Leaving..\n"); exit; },
		test 		=> sub { exit; },
		download 	=> \&getLatestLog,
    );

    while(my($key,$value) = each %commands) {
        if($command eq $key) {
            $value->();
		}
    }
}

# subroutine: getLatestLog
# -------------------------------------------------------------
# Downloads (or updates) the current logfile using which ever protocol 
# specified in config ($config{transfer_protocol})
sub getLatestLog {
	my($logfile, $method, $via) = @_;
	print("I will now fetch the latest logfile using <$method> (according to the config file)\n");
	
	if($method eq 'ftp') {
		my $current_size = logHist('filesize', $logfile);
		getLogByFtp($logfile, $current_size);
	}
	
	if($via ne 'cron') {
		interactiveCmd();
	}
}

# subroutine: lastGid
# -------------------------------------------------------------
# Returns the last game id found in the games table
sub lastGid {
	my $row = $dbh->selectrow_hashref("
		SELECT id FROM games ORDER BY id DESC LIMIT 1
	");
	return $row->{id};
}

# subroutine: gameData ($what, $gid)
# -------------------------------------------------------------
# Lookup function for the games table
sub gameData {
    my ($what, $gid) = @_;
    my $sth = $dbh->prepare("SELECT $what FROM games WHERE id=?");
    $sth->execute($gid);
    return $sth->fetchrow() 
		or return "CA (warn): Couldn't find data on GID $gid";
}

# subroutine: name2version ($version)
# -------------------------------------------------------------
# Translates the Call of Duty name to a number
# Like CoD:MW is number 4 in the serie
sub name2version {
	my($version) = @_;
	my %map = (
		'call of duty' => 10,
		'cod:united offensive' => 15,
		'call of duty 4' => 40,
		'Call of Duty: World at War' => 50,
	);
	return $map{lc $version} || 'unknown';
}

# subroutine: ts2seconds ($ts)
# -------------------------------------------------------------
# Converts timestamps in the log to seconds
sub ts2seconds {
    my @t = split(':', $_[0]);
    return $t[0]*60 + $t[1];
}

# subroutine: usingMod ($gid)
# -------------------------------------------------------------
# Checks to see if the game is running any mod (like pam4)
sub usingMod {
	my($gid) = @_;
	my $row = $dbh->selectrow_hashref('SELECT mods FROM games WHERE id=?',
		undef, $gid);
	
	if(defined($row->{mods})) {
		if($row->{mods} eq 'none') {
			return;
		} else { return 1; } 
	}
}

# subroutine: assignTeam ($player, $team, $gid)
# -------------------------------------------------------------
# Put the player on the team parsed from damage hits or kills
sub assignTeam {
	my($player, $team, $gid) = @_;
	$dbh->do('UPDATE players SET team=? WHERE handle=? AND gid=?',
		undef, $team, $player, $gid)
		or croak "CA (error): Couldn't assign team: " . DBI->errstr;
}

# subroutine: playerExist ($player, $gid)
# -------------------------------------------------------------
# Checks the existens of a player (so we don't add him twice)
sub playerExist {
	my($player, $gid) = @_;
	
	my $sth = $dbh->prepare("SELECT id FROM players WHERE handle=? AND gid=?");
    $sth->execute($player, $gid);
	
	if($sth->rows() == 0) {
		return;
	} else {
		return 1;
	}
}

# subroutine: round ($gid, $decimal)
# -------------------------------------------------------------
# Round function used in the elo-rating
sub round {
	my $number = shift || 0;
	my $dec = 10 ** (shift || 0);
	return int( $dec * $number + .5 * ($number <=> 0)) / $dec;
}

# subroutine: missingTeam ($player, $gid)
# -------------------------------------------------------------
# Check if the player is missing team membership
sub missingTeam {
	my($player, $gid) = @_;
	
	my $row = $dbh->selectrow_hashref(
		'SELECT team FROM players WHERE handle=? AND gid=?',
		undef, $player, $gid);
	
	if(defined($row->{team})) {
		($row->{team} eq '') ? return 1 : return;
	}
}

# subroutine: changeHandle ($handle, $hash, $gid)
# -------------------------------------------------------------
# Change the players nick
sub changeHandle {
	my($handle, $hash, $gid) = @_;
	
	# Old handle
	my $row = $dbh->selectrow_hashref(
		'SELECT handle AS old_handle FROM players WHERE hash=? AND gid=?',
		undef, $hash, $gid);
		
	$dbh->do('UPDATE players SET handle=? WHERE hash=?',
		undef, $handle, $hash, $gid);
		
	$dbh->do('UPDATE kills SET killer=? WHERE killer=?',
		undef, $handle, $row->{old_handle}, $gid);
		
	$dbh->do('UPDATE kills SET corpse=? WHERE corpse=?',
		undef, $handle, $row->{old_handle}, $gid);
		
	$dbh->do('UPDATE hits SET hitman=? WHERE hitman=?',
		undef, $handle, $row->{old_handle}, $gid);
		
	$dbh->do('UPDATE hits SET wounded=? WHERE wounded=?',
		undef, $handle, $row->{old_handle}, $gid);
	
	#$dbh->do('UPDATE profiles SET handle=? WHERE handle=?',
	#	undef, $handle, $row->{old_handle});
}

# subroutine: try2findTeam ($need_team, $has_team, $gid)
# -------------------------------------------------------------
# Setting the oppsite team of the killer/corpse, if they
# have any
sub try2findTeam {
	my($need, $has, $gid) = @_;
	my $team;
	
	my $row = $dbh->selectrow_hashref(
		'SELECT team AS opposite_team FROM players WHERE handle=? AND gid=? AND team=(team="allies" OR team="axis")',
		undef, $has, $gid);
		
	if($row->{opposite_team}) {
		$team = (($row->{opposite_team} eq 'allies') ? 'axis' : 'allies');		
	}
	
	$dbh->do('UPDATE players SET team=? WHERE handle=? AND gid=?',
		undef, $team, $need, $gid)
		or croak "CA (error): Couldn't update opposite team: " . DBI->errstr;
}

# subroutine: gameOver ($gid)
# -------------------------------------------------------------
# Returns true if a game has passed more then 5 round (because
# then we don't need to assign teams anymore)
sub gameOver {
	my($gid) = @_;
	
	my $row = $dbh->selectrow_hashref('SELECT rcount FROM games WHERE id=?',
		undef, $gid);
	
	if($row->{rcount}) {
		($row->{rcount} > 5) ? return 1 : return;
	}
}

# subroutine: cleanUpGames  ($gid)
# -------------------------------------------------------------
# Find games to delete
sub cleanUpGames {
	my($gid) = @_;
	
	my $row = $dbh->selectrow_hashref(
		'SELECT id, 
			(SELECT COUNT(*) FROM kills WHERE gid=a.id) AS kills, 
			(SELECT COUNT(*) FROM players WHERE gid=a.id) AS players 
		FROM games AS a WHERE id=?', undef, $gid);
		
	if($row->{kills} < 5) {
		deleteGame($gid);
	}
	if($row->{players} <= 1) {
		deleteGame($gid);
	}
}

# subroutine: deleteGame ($gid)
# -------------------------------------------------------------
# ..and then delete them
sub deleteGame {
	my($gid) = @_;
	$dbh->do('DELETE FROM games WHERE id=?', undef, $gid);
	$dbh->do('DELETE FROM actions WHERE gid=?', undef, $gid);
	$dbh->do('DELETE FROM kills WHERE gid=?', undef, $gid);
	$dbh->do('DELETE FROM hits WHERE gid=?', undef, $gid);
	$dbh->do('DELETE FROM quotes WHERE gid=?', undef, $gid);
	$dbh->do('DELETE FROM players WHERE gid=?', undef, $gid);
	$dbh->do('DELETE FROM streaks WHERE gid=?', undef, $gid);
	$dbh->do('DELETE FROM latest WHERE gid=?', undef, $gid);
}

# subroutine: going2war ($gid, $rcount)
# -------------------------------------------------------------
# Mark a game as a war (clanmatch)
sub going2war {
	my($gid) = @_;
	
	$dbh->do('UPDATE games SET war=? WHERE id=?',
		undef, 1, $gid)
		or croak "CA (error): Couldn't set clanmatch " . DBI->errstr;
}

# subroutine: makeProfile ($handle)
# -------------------------------------------------------------
# 
sub makeProfile {
	my($player) = @_;
	
	$dbh->do('INSERT INTO profiles (handle) VALUES(?)',
		undef, $player);
}

# subroutine: addProfileData ($player)
# -------------------------------------------------------------
# Add data to existing profiles
sub addProfileData {
	my($player) = @_;
	sumKills($player);
	sumDeaths($player);
	sumSuicides($player);
	sumGames($player);
	addLastElo($player);
	addClanTag($player);
}

# subroutine: sumKills ($killer)
# -------------------------------------------------------------
# Sum total player kills
sub sumKills {
	my($killer) = @_;
	
	my $kills = $dbh->selectrow_hashref('SELECT COUNT(*) AS count FROM kills WHERE killer=? AND corpse != ?',
		undef, $killer, $killer);

    $dbh->do('UPDATE profiles SET kills=? WHERE handle=?',
        undef, $kills->{count}, $killer)
        or croak "CA (error): Couldn't set profile kills " . DBI->errstr;
}

# subroutine: sumDeaths ($corpse)
# -------------------------------------------------------------
# Sum total player deaths
sub sumDeaths {
	my($corpse) = @_;
	
	my $deaths = $dbh->selectrow_hashref('SELECT COUNT(*) AS count FROM kills WHERE corpse=? AND killer != ?',
        undef, $corpse, $corpse);

    $dbh->do('UPDATE profiles SET deaths=? WHERE handle=?',
        undef, $deaths->{count}, $corpse)
		or croak "CA (error): Couldn't set profile deaths " . DBI->errstr;
}

# subroutine: sumSuicides ($dead)
# -------------------------------------------------------------
# Sum total player suicides
sub sumSuicides {
	my($dead) = @_;
	
	my $suicides = $dbh->selectrow_hashref('SELECT COUNT(*) AS count FROM kills WHERE killer=? AND corpse=?',
        undef, $dead, $dead);

    $dbh->do('UPDATE profiles SET suicides=? WHERE handle=?',
        undef, $suicides->{count}, $dead)
		or croak "CA (error): Couldn't set profile deaths " . DBI->errstr;
}

# subroutine: sumGames ($player)
# -------------------------------------------------------------
# Sum total player games
sub sumGames {
	my($player) = @_;
	
	my $games = $dbh->selectrow_hashref('SELECT COUNT(DISTINCT gid) AS count FROM players WHERE handle=?',
        undef, $player);

    $dbh->do('UPDATE profiles SET games=? WHERE handle=?',
        undef, $games->{count}, $player)
		or croak "CA (error): Couldn't set profile games " . DBI->errstr;
}

# subroutine: addLastElo ($player)
# -------------------------------------------------------------
# Fetch the current elo and put it in profiles
sub addLastElo {
	my($player) = @_;
	my $row = $dbh->selectrow_hashref(
		'SELECT elo FROM players WHERE handle=? AND elo IS NOT NULL ORDER BY id DESC LIMIT 1',
		undef, $player);
	
	$row->{elo} = (($row->{elo}) ? $row->{elo} : 1000);
		
	$dbh->do('UPDATE profiles SET elo=? WHERE handle=?',
        undef, $row->{elo}, $player)
		or croak "CA (error): Couldn't set profile elo " . DBI->errstr;
}

# subroutine: niceString ($string)
# -------------------------------------------------------------
# Clean up given string and remove trailing chars
sub niceString {
	my($string) = @_;

	for($string) {
		s///;
		s///;
		s/\s+$//;
		s/^\s+//;
		s/QUICKMESSAGE_.*//;
	}
	return $string;
}

# subroutine: sleepFor ($duration)
# -------------------------------------------------------------
# Uses the 4th argument in select to sleep
sub sleepFor {
	my($duration) = @_;
	select undef, undef, undef, $duration;
}

# subroutine: hasProfile ($player)
# -------------------------------------------------------------
# Returns true if the given player exists in profiles
sub hasProfile {
	my($player) = @_;
	my $sth = $dbh->prepare('SELECT id FROM profiles WHERE handle=?');
    $sth->execute($player);
	
	if($sth->rows() == 0) {
		return;
	} else { return 1; }
}

# subroutine: lastElo ($player)
# -------------------------------------------------------------
# Grabs the last elo from players
sub lastElo {
	my($handle) = @_;
	
    my $q = $dbh->prepare("SELECT elo FROM players WHERE handle=? AND elo IS NOT NULL ORDER BY id DESC LIMIT 1");
    $q->execute($handle);

    if($q->rows > 0) {
        return $q->fetchrow();
    } else {
        return '1000';
	}
}

# subroutine: killZombies ()
# -------------------------------------------------------------
# 
sub killZombies {
	my $query = $dbh->prepare('SELECT gid,handle FROM players WHERE team="" AND elo IS NULL');
	$query->execute();
	
	while(my $ref = $query->fetchrow_hashref()) {
		my $kills = $dbh->prepare('SELECT COUNT(*) AS count FROM kills WHERE killer=? AND gid=?');
		$kills->execute($ref->{handle}, $ref->{gid});
		
		if($kills->rows() <= 1) {
			deletePlayer($ref->{handle}, $ref->{gid});
		}
	}
}

# subroutine: deletePlayer ($player)
# -------------------------------------------------------------
# Deletes a players record
sub deletePlayer {
	my($player, $gid) = @_;
	$dbh->do('DELETE FROM players WHERE handle=? AND gid=?', undef, $player, $gid);
	$dbh->do('DELETE FROM quotes WHERE handle=? AND gid=?', undef, $player, $gid);
	$dbh->do('DELETE FROM hits WHERE (hitman=? OR wounded=?) AND gid=?', undef, $player, $player, $gid);
	$dbh->do('DELETE FROM actions WHERE handle=? AND gid=?', undef, $player, $gid);
	$dbh->do('DELETE FROM kills WHERE (killer=? OR corpse=?) AND gid=?', undef, $player, $player, $gid);
}

# subroutine: cleanUpProfiles
# -------------------------------------------------------------
# Deletes profiles where games = 0 or kills AND deaths = 0
sub cleanUpProfiles {
	$dbh->do('DELETE FROM profiles WHERE games="0" OR (kills="0" AND deaths="0")');
}

# subroutine: addClanTag ($player)
# -------------------------------------------------------------
# Grabs the clan tag off the player name and puts it in profiles
sub addClanTag {
	my($player) = @_;
	my $clan = 'NO_MATCH';

	if(missingClan($player)) {
		if($player =~ /(\w+).*?(\w+)$/) {
			$clan = $1;
		}
		if($player =~ /^[A-Za-z0-9]+$/) {
			$clan = 'lonewolf';
		}
		$dbh->do('UPDATE profiles SET clan=? WHERE handle=?',
        undef, $clan, $player)
		or croak "CA (error): Couldn't set profile clan " . DBI->errstr;
	}
}

# subroutine: searchClanTag
# -------------------------------------------------------------
# Loops through all clans and updates profiles with no clan,
# if their handle match the clantag
sub searchClanTag {
	my $tags = $dbh->prepare('SELECT DISTINCT(clan) FROM profiles WHERE clan !=? AND clan IS NOT NULL');
	$tags->execute('NO_MATCH');
	
	# It's 06:45 and I just can't get the query to act normal with placeholders.. wtf
	while(my $ref = $tags->fetchrow_hashref()) {
		$dbh->do('UPDATE profiles SET clan="'.$ref->{clan}.'" WHERE handle LIKE "%'.$ref->{clan}.'%"');
			#undef, $ref->{clan}, $ref->{clan})
			#or croak "CA (error): Couldn't set public clan " . DBI->errstr;
	}
}

# subroutine: missingClan
# -------------------------------------------------------------
# Returns true if the player lacks clan
sub missingClan {
	my($player) = @_;
	
	my $sth = $dbh->prepare('SELECT clan FROM profiles WHERE handle=? AND clan IS NOT NULL AND clan !=?');
    $sth->execute($player, 'NO_MATCH');
	
	if($sth->rows() == 0) {
		return 1;
	} else { return; }
}

# subroutine: adjustPlayTime ($gid)
# -------------------------------------------------------------
# Adjusts the play time, start = ts to first kill
# stop = ts to last kill
sub adjustPlayTime {
	my($gid) = @_;
	my $and = '';
	
	if(usingMod($gid)) {
		if(gameData('type', $gid) ne 'dm') {
			$and = ' AND k_team!=c_team ';
		}
	}
	
	my $start_ts = $dbh->selectrow_hashref(
		'SELECT ts FROM kills WHERE gid=? 
		AND id != (SELECT MAX(id) FROM kills WHERE gid=?)
		AND killer!=corpse
		'. $and.'
		ORDER BY ts ASC LIMIT 1',
		undef, $gid, $gid);
		
	my $stop_ts = $dbh->selectrow_hashref(
		'SELECT ts FROM kills WHERE gid=? 
		AND killer!=corpse
		'. $and.'
		ORDER BY ts DESC LIMIT 1',
		undef,  $gid);
		
		#AND id != (SELECT MAX(id) FROM kills WHERE gid=?) 
	
	$dbh->do('UPDATE games SET start=?, stop=? WHERE id=?',
		undef, $start_ts->{ts}, $stop_ts->{ts}, $gid)
		or croak "CA (error): Couldn't set game start/stop " . DBI->errstr;
}

# subroutine: firstKillAndDeath
# -------------------------------------------------------------
sub firstKillAndDeath {
	
}

# subroutine: lastKillAndDeath
# -------------------------------------------------------------
sub lastKillAndDeath {

}

# subroutine: playerRoundStamina
# -------------------------------------------------------------
sub playerRoundStamina {

}

# subroutine: logHist
# -------------------------------------------------------------
sub logHist {
	my($what, $logfile) = @_;
    my $sth = $dbh->prepare("SELECT $what FROM loghist WHERE filename=?");
    $sth->execute($logfile);
    return $sth->fetchrow() 
		or return "CA (warn): Couldn't find data on FILENAME $logfile";
}

# subroutine: isParsed
# -------------------------------------------------------------
sub isParsed {
	my($gid) = @_;
	$dbh->do('UPDATE games SET parsed=? WHERE id=?',
		undef, 1, $gid)
		or croak "CA (error): Couldn't set game parsed " . DBI->errstr;
}

# subroutine: isParsed
# -------------------------------------------------------------
sub isNewGame {
	my($gid) = @_;
	$dbh->do('INSERT INTO latest (gid) VALUES(?)',
		undef, $gid);
}

1;
