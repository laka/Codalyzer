package CA::Regex;

# Codalyzer
# - Regexes
# Gathering of all regexes used by CA::Parser

use strict;
use warnings;
use lib '/home/homer/ju/jussimik/CA-Parser/';

our %Parser = (
	InitGame => {
		all => qr/(\d+:\d+)\sInitGame:\s(.*)g_gametype\\(.*?)\\.*gamename\\(.*?)\\mapname\\(.*?)\\protocol/,
	},
	Join => {
        cod10 => qr/(\d+:\d+)\sJ;\d+;\d+(.*?)/,
        cod15 => qr/(\d+:\d+)\sJ;\d+;\d+(.*?)/,
        cod20 => '',
        cod30 => '',
        cod40 => qr/(\d+:\d+)\sJ;(.*);\d+;(.*)/,
        cod50 => qr/(\d+:\d+)\sJ;(.*);\d+;(.*)/,
    },
	Status => {
		cod10 => qr/(\d+:\d+)\s(RestartGame|ShutdownGame):/,
		cod15 => qr/(\d+:\d+)\s(RestartGame|ShutdownGame):/,
	},
    Damage => {
        cod10 => qr/(\d+:\d+)\sD;\d;(\d);(.*);.*;\d;(\d);(.*);.*;(.*)_mp;(\d+);.*;(.*)$/,
        cod15 => '',
        cod20 => '',
        cod30 => '',
        cod40 => qr/(\d+:\d+)\sD;(.*);.*;(.*);(.*);(.*);.*;(.*);(.*);(.*);(.*);(.*);(.*)/,
        cod50 => qr/(\d+:\d+)\sD;(.*);.*;(.*);(.*);(.*);.*;(.*);(.*);(.*);(.*);(.*);(.*)/,
    },
    Kills => {
        cod10 => qr/(\d+:\d+)\sK;\d;(\d);;(.*);\d;(\d);;(.*);(.*)_mp;(.*);.*;(.*)/,
        cod15 => '',
        cod20 => '',
        cod30 => '',
        cod40 => qr/(\d+:\d+)\sK;(.*);.*;(.*);(.*);(.*);.*;(.*);(.*);(.*);(.*);(.*);(.*)/,
        cod50 => qr/(\d+:\d+)\sK;(.*);.*;(.*);(.*);(.*);.*;(.*);(.*);(.*);(.*);(.*);(.*)/,
    },
	TBkills => {
		cod10 => qr/(\d+:\d+)\sK;\d;(\d);(allies|axis);.*;\d;(\d);(allies|axis);.*;(.*)_mp;(\d+);.*;(.*)$/,
        cod15 => '',
	},
	Finish => {
        cod10 => '',
        cod15 => '',
        cod20 => '',
        cod30 => '',
        cod40 => qr/(\d+:\d+)\s(ShutdownGame)/,
        cod50 => qr/(\d+:\d+)\s(ShutdownGame)/,
    },
    Quotes => {
        cod10 => qr/(\d+:\d+)\s(say|sayteam);0;(\d);.*;(.*)/,
        cod15 => '',
        cod20 => '',
        cod30 => '',
        cod40 => qr/(\d+:\d+)\ssay;.*?;.*?;(.*?);(.*)/,
        cod50 => qr/(\d+:\d+)\ssay;.*?;.*?;(.*?);(.*)/,
    },
    Exitlev => {
        cod10 => '',
        cod15 => '',
        cod20 => '',
        cod30 => '',
        cod40 => qr/(\d+:\d+)\sExitLevel/,
        cod50 => qr/(\d+:\d+)\sExitLevel/,
    },
    Jointeam => {
        cod10 => '',
        cod15 => '',
        cod20 => '',
        cod30 => '',
        cod40 => qr/(\d+:\d+)\sJT;(.*?);(.*?);(.*)/,
        cod50 => '',
    },
	Roundstart => {
        cod10 => '',
        cod15 => '',
        cod20 => '',
        cod30 => '',
        cod40 => qr/(\d+:\d+)\sRS;(.*)/,
        cod50 => '',
    },
    Roundwin => {
        cod10 => qr/(\d+:\d+)\sW;(allies|axis)(.*)/,
        cod15 => '',
        cod20 => '',
        cod30 => '',
        cod40 => qr/(\d+:\d+)\sRO;(.*)/,
        cod50 => '',
    },
    Action => {
        cod10 => qr/(\d+:\d+)\sA;\d;(\d);(allies|axis);.*;(bomb_(plant|defuse))/,
        cod15 => '',
        cod20 => '',
        cod30 => '',
        cod40 => qr/(\d+:\d+)\sA;(bomb_[planted|defused]);(.*?);(.*)/,
        cod50 => '',
    },
	Pickups => {
		cod10 => qr/(\d+:\d+)\s(Weapon|Item);0;(\d);.*;(.*)/,
		cod15 => '',
	},
	Timeout => {
        cod10 => '',
        cod15 => '',
        cod20 => '',
        cod30 => '',
        cod40 => qr/(\d+:\d+)\sTO;(.*?);(.*)/,
        cod50 => '',
    },
    Sidechange => {
        cod10 => '',
        cod15 => '',
        cod20 => '',
        cod30 => '',
        cod40 => qr/(\d+:\d+)\s(SS)/,
        cod50 => '',
    },
    Result => {
        cod10 => '',
        cod15 => '',
        cod20 => '',
        cod30 => '',
        cod40 => qr/(\d+:\d+)\sMO;(.*?);(.*?);(.*)/,
        cod50 => '',
    },
    Winners => {
        cod10 => '',
        cod15 => '',
        cod20 => '',
        cod30 => '',
        cod40 => qr/(\d+:\d+)\sW;(.*?);(.*)/,
        cod50 => '',
    },
    Loosers => {
        cod10 => '',
        cod15 => '',
        cod20 => '',
        cod30 => '',
        cod40 => qr/(\d+:\d+)\sL;(.*?);(.*)/,
        cod50 => '',
    },
);

1;