package CA::Gamedata;

# Codalyzer
# - Call of Duty Game data
# Standard maps, teams, mods etc

use strict;
use warnings;
use lib '/home/homer/ju/jussimik/CA-Parser/';

# Team planter map
our %Plants = (
	cod10 => {
	},
	cod15 => {
	},
	cod20 => {
	},
	cod40 => {
		convoy => 'axis',
		backlot => 'axis',
		bloc => 'axis',
		bog => 'axis',
		countdown => 'allies',
		crash => 'allies',
		crossfire => 'axis',
		district => 'axis',
		farm => 'allies',
		overgrown => 'axis',
		pipeline => 'allies',
		shipment => 'axis',
		showdown => 'axis',
		strike => 'allies',
		vacant => 'axis',
		cargoship => 'axis',
		broadcast => 'allies',
		creek => 'axis',
		carentan => 'axis',
		killhouse => 'allies',
	},
	cod50 => {
	},
);

# Game type map 
our %Types = (
	cod10 => {},
	cod15 => {},
	cod20 => {},
	cod40 => {'tdm', 'hq', 'dm', 'dom', 'sd', 'sab'},
	cod50 => {},
);
