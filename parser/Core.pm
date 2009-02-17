package OcSP::Core;

# OpenCodStats
# - Core Operations
# The main functions are placed here (ELO-rating, Awards etc.)

use strict;
use warnings;
use lib '/home/homer/ju/jussimik/OCS-Parser/';
use OcSP::SimpleDB;

my $dbh = OcSP::SimpleDB::getDbh();

1;