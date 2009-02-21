package CA::SimpleDB;

# Codalyzer
# - Simple Database Functions
# Just a wrapper for some common DBI-functions

use strict;
use warnings;
use DBI;
use Carp;
use lib '/home/homer/ju/jussimik/CA-Parser/';
use CA::Config;

BEGIN {
	my %config = CA::Config::readConfig();
	our $dbh = DBI->connect("dbi:mysql:" . "dbname=" .
		$config{mysql_db} . ";host=" . $config{mysql_host},
        $config{mysql_user}, $config{mysql_pass})
		or croak "CA (error): Couldn't connect to MySQL-database: " . DBI->errstr;
}

our($dbh);

# subroutine: getDbh
# -------------------------------------------------------------
# Returns the given DBI handle if it still can be reached,
# otherwise try to reconnect

sub getDbh {
	my %config = CA::Config::readConfig();
	if (!(defined($dbh) && $dbh->ping)) {
		unless ($dbh = DBI->connect("dbi:mysql:" . "dbname=" .
			$config{mysql_db} . ";host=" . $config{mysql_host},
			$config{mysql_user}, $config{mysql_pass})) {
				$dbh = undef;
				croak "CA (error): Couldn't connect to MySQL-database: " . DBI->errstr;
		}
	}
	return $dbh;
}

# subroutine: insertRow (table, %insert_hash)
# -------------------------------------------------------------
# Inserts the rows into the table specified.
# NOTE: Uses placeholders, but should consider rewriting it 
# using a prepare statement and quotes

sub insertRow {
    my $table = shift;
    my %insert = @_;
    my $placeholders = join(',', map { "?"; } (values(%insert)));

    $dbh->do(
        "INSERT INTO $table (".join(', ', keys %insert).")
        VALUES ($placeholders)", undef, (values(%insert)))
        or croak "CA (error): Couldn't insert row: " . DBI->errstr;
}

# subroutine: updateRow ({table => foo, 
# 	updates => { col => value } where => id=1})
# -------------------------------------------------------------
# Updates rows accordingly to the hash (of hashes) argument.
# NOTE: Looking into an ORM so commented out for now

sub updateRow {
	my ($args) = @_;
	my $table = $args->{table};
	my $updates = $args->{updates};
	my @cols = keys %$updates;
	
	my $query = "UPDATE $table SET ";
	#$query .= (join(', ', map { '$_ = ?' } @cols)
		#($args->{where} ? ' WHERE '. $args->{where} : '');

	my $sth = $dbh->prepare($query);
	$sth->execute(map { $updates->{$_} } @cols);
}

# subroutine: flushTable
# -------------------------------------------------------------
# Flushes (truncates) tables

sub flushTable {
	$dbh->do('TRUNCATE TABLE games');
	$dbh->do('TRUNCATE TABLE players');
	$dbh->do('TRUNCATE TABLE kills');
	$dbh->do('TRUNCATE TABLE hits');
	$dbh->do('TRUNCATE TABLE quotes');
	$dbh->do('TRUNCATE TABLE actions');
	$dbh->do('TRUNCATE TABLE profiles');
}

END {
	our $dbh;
    $dbh->disconnect;
}

1;
