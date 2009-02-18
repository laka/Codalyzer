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
        cod10 => '',
        cod15 => '',
        cod20 => '',
        cod30 => '',
        cod40 => qr/(\d+:\d+)\sJ;(.*);(\d+);(.*)/,
        cod50 => '',
    },
    Damage => {
        cod10 => '',
        cod15 => '',
        cod20 => '',
        cod30 => '',
        cod40 => qr/(\d+:\d+)\sD;(.*);(.*);(.*);(.*);(.*);(.*);(.*);(.*);(.*);(.*);.*;(.*)/,
        cod50 => '',
    },
    Kills => {
        cod10 => '',
        cod15 => '',
        cod20 => '',
        cod30 => '',
        cod40 => qr/(\d+:\d+)\sK;(.*);(.*);(.*);(.*);(.*);(.*);(.*);(.*);(.*);(.*);(.*);(.*)/,
        cod50 => '',
    },
	Finish => {
        cod10 => '',
        cod15 => '',
        cod20 => '',
        cod30 => '',
        cod40 => qr/(\d+:\d+)\s(ShutdownGame)/,
        cod50 => '',
    },
    Quotes => {
        cod10 => '',
        cod15 => '',
        cod20 => '',
        cod30 => '',
        cod40 => qr/(\d+:\d+)\ssay;(.*?);(.*?);(.*?);(.*)/,
        cod50 => '',
    },
    Exitlev => {
        cod10 => '',
        cod15 => '',
        cod20 => '',
        cod30 => '',
        cod40 => qr/(\d+:\d+)\s(ExitLevel)/,
        cod50 => '',
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
        cod10 => '',
        cod15 => '',
        cod20 => '',
        cod30 => '',
        cod40 => qr/(\d+:\d+)\sRO;(.*)/,
        cod50 => '',
    },
    Action => {
        cod10 => '',
        cod15 => '',
        cod20 => '',
        cod30 => '',
        cod40 => qr/(\d+:\d+)\sA;(bomb_[planted|defused]);(.*?);(.*)/,
        cod50 => '',
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