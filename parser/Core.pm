package CA::Core;

# Codalyzer
# - Core Operations
# The main functions are placed here (ELO-rating, Awards etc.)

use strict;
use warnings;
use lib '/home/homer/ju/jussimik/CA-Parser/';
use CA::SimpleDB;

my $dbh = CA::SimpleDB::getDbh();

1;