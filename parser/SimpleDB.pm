package OcSP::SimpleDB;

# OpenCodStats
# - Simple Database Functions
# Just a wrapper for some common DBI-functions

use strict;
use warnings;
use DBI;
use Carp;
use lib '/home/homer/ju/jussimik/OCS-Parser/';
use OcSP::Config;

BEGIN {
	my %config = OcSP::Config::readConfig();
	our $dbh = DBI->connect("dbi:mysql:" . "dbname=" .
		$config{mysql_db} . ";host=" . $config{mysql_host},
        $config{mysql_user}, $config{mysql_pass})
		or croak "OcSP (error): Couldn't connect to MySQL-database: " . DBI->errstr;
}

our($dbh);

sub getDbh {
	my %config = OcSP::Config::readConfig();
	if (!(defined($dbh) && $dbh->ping)) {
		unless ($dbh = DBI->connect("dbi:mysql:" . "dbname=" .
			$config{mysql_db} . ";host=" . $config{mysql_host},
			$config{mysql_user}, $config{mysql_pass})) {
				$dbh = undef;
				croak "OcSP (error): Couldn't connect to MySQL-database: " . DBI->errstr;
		}
	}
	return $dbh;
}

END {
    our $dbh;
    $dbh->disconnect;
}

1;