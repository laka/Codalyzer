<?php

$regexlib = 
	array("cod" => 
		array(
			"newGame" => array(
				# VERSION	LOGPRINT	STATUS: WORKS ON EVERY VERSION
				# ----------------------------------------------------------------------------------------------------------------------------------------------------------------
				# COD10	 	0:00 InitGame: \g_gametype\dm\g_timeoutsAllowed\3\gamename\Call of Duty\mapname\mp_carentan\
				# COD15		0:00 InitGame: \g_gametype\dm\g_timeoutsallowed\0\gamename\CoD:United Offensive\mapname\mp_cassino\
				# COD20		0:00 InitGame: \g_antilag\1\g_gametype\ctf\gamename\Call of Duty 2\mapname\mp_carentan\
				# COD40		0:00 InitGame: \g_compassShowEnemies\0\g_gametype\war\gamename\Call of Duty 4\mapname\mp_killhouse\
				# COD50		0:00 InitGame: \fxfrustumCutoff\1000\g_compassShowEnemies\0\g_gametype\dm\gamename\Call of Duty: World at War\mapname\mp_makin\
				# MOD		0:00 InitGame: \_GV_streaming\enabled - CB v2.5\fs_game\mods/pam4\g_compassShowEnemies\0\g_gametype\sd\gamename\Call of Duty 4\mapname\mp_crash\
				# ----------------------------------------------------------------------------------------------------------------------------------------------------------------
				"all" => "/(\d+:\d+)\sInitGame:\s(.*)g_gametype\\\\(.*?)\\\\.*gamename\\\\(.*?)\\\\mapname\\\\(.*?)\\\\/"),
 
			"joinPlayer" => array(
				# VERSION	LOGPRINT	STATUS: WORKS ON EVERY VERSION
				# ----------------------------------------------------------------------------------------------------------------------------------------------------------------
				# COD10	 	4:04 J;0;0;Laka
				# COD15		57:06 J;0;3;Jinxen
				# COD20		0:00 J;0;0;Unknown Soldier
				# COD40		4047:06 J;329415ed;0;norsof | laka
				# COD50		0:04 J;896080436;0;laka
				# MOD		4:04 J;0;0;Laka
				# ----------------------------------------------------------------------------------------------------------------------------------------------------------------
				"all" => '/(\d+:\d+)\sJ;(\w+;\d+);(.*)/'),

			"damageHit" => array(
				# VERSION	LOGPRINT	STATUS: WORKS ON EVERY VERSION EXCEPT COD20 (UNTESTED)
				# ----------------------------------------------------------------------------------------------------------------------------------------------------------------
				# COD10	 	1:05 D;0;1;allies;Patrio;0;0;allies;Laka;m1garand_mp;35;MOD_RIFLE_BULLET;left_leg_lower'
				# COD15		59:42 D;0;2;axis;Mikael;0;0;allies;Patrio;mg30cal_mp;42;MOD_RIFLE_BULLET;left_leg_upper
				# COD20		????
				# COD40		3:03 D;0ed673079715c343281355c2a1fde843;3;axis;cptLazio;5098e22864e0a25fc26c261690cb4bbb;1;allies;Mikael;m4_reflex_mp;14;MOD_RIFLE_BULLET;torso_upper
				# COD50		1:51 D;896080436;0;axis;laka;290206053;1;allies;Patrio;thompson_mp;40;MOD_RIFLE_BULLET;left_arm_upper
				# MOD		10989:10 D;626e9e18;4;axis;norsof | Patrio;8db75fb1;8;allies;WFP James;m4_silencer_mp;28;MOD_RIFLE_BULLET;torso_lower		
				# ----------------------------------------------------------------------------------------------------------------------------------------------------------------
				"all" => '/(\d+:\d+)\sD;(\w+);\d+;(.*);(.*);(.*);.*;(.*);(.*);(.*);(.*);(.*);(.*)/'),
			
			"kills" => array(
				# VERSION	LOGPRINT	STATUS: WORKS ON EVERY VERSION EXCEPT COD20 (UNTESTED)
				# ----------------------------------------------------------------------------------------------------------------------------------------------------------------
				# COD10	 	1:12 K;0;0;;Laka;0;1;;Patrio;m1garand_mp;105;MOD_HEAD_SHOT;head
				# COD15		8:29 K;0;0;;Patrio;0;2;;Laka;panzerfaust_mp;10000;MOD_EXPLOSIVE;none
				# COD20		????
				# COD40		1:05 K;5098e22864e0a25fc26c261690cb4bbb;2;;Mikael;;0;;Patrio;ak47_reflex_mp;77;MOD_HEAD_SHOT;head
				# COD50		1:05 K;896080436;0;;laka;290206053;1;;Patrio;thompson_mp;40;MOD_RIFLE_BULLET;torso_lower
				# MOD		10997:16 K;48c83a32;7;allies;WFP Dr.K;a1fde843;0;axis;norsof | CptLaz;ak47_reflex_mp;56;MOD_RIFLE_BULLET;right_arm_upper		
				# ----------------------------------------------------------------------------------------------------------------------------------------------------------------
				"all" => '/(\d+:\d+)\sK;(.*);.*;(.*);(.*);(.*);.*;(.*);(.*);(.*);(.*);(.*);(.*)/'),

			"gameStopTime" => array(
				# VERSION	LOGPRINT	STATUS: WORKS ON EVERY VERSION
				# ----------------------------------------------------------------------------------------------------------------------------------------------------------------
				# COD10	 	8839:04 RestartGame:
				# COD15		4:07 ShutdownGame:
				# COD20		1:00 ShutdownGame:
				# COD40		1:07 ShutdownGame:
				# COD50		411:07 ShutdownGame:
				# MOD		10:23 ShutdownGame:
				# ----------------------------------------------------------------------------------------------------------------------------------------------------------------
				"all" => '/(\d+:\d+)\s(RestartGame|ShutdownGame):/'),

			"quotes" => array(
				# VERSION	LOGPRINT	STATUS: WORKS ON EVERY VERSION EXCEPT COD20 (UNTESTED)
				# ----------------------------------------------------------------------------------------------------------------------------------------------------------------
				# COD10	 	40:16 say;0;0;Mikael;stats?
				# COD15		133:07 sayteam;0;3;Mikael;^Uikke braak
				# COD20		????
				# COD40		2:07 say;5098e22864e0a25fc26c261690cb4bbb;1;Mikael;^Ucreate på nytt
				# COD50		4:42 say;896080436;0;laka;la meg plante
				# MOD		14:21 say;1;Mikael;jada
				# ----------------------------------------------------------------------------------------------------------------------------------------------------------------
				"all" => '/(\d+:\d+)\ssay(?:team)?;(.*?);.*?;(.*?);(.*)/'),

			"exitLevel" => array(
				# VERSION	LOGPRINT	STATUS: WORKS ON EVERY VERSION
				# ----------------------------------------------------------------------------------------------------------------------------------------------------------------
				# COD10	 	21:06 ExitLevel: executed
				# COD15		00:02 ExitLevel: executed
				# COD20		12:08 ExitLevel: executed
				# COD40		4091:06 ExitLevel: executed
				# COD50		00:10 ExitLevel: executed
				# MOD		00:30 ExitLevel: executed
				# ----------------------------------------------------------------------------------------------------------------------------------------------------------------
				"all" => '/(\d+:\d+)\sExitLevel/'),

			"joinTeam" => array(
				# VERSION	LOGPRINT	STATUS: ONLY FOR PAM4
				# ----------------------------------------------------------------------------------------------------------------------------------------------------------------
				# MOD(PAM4) 5674:38 JT;218e470a;axis;eSd|grimmjow
				# ----------------------------------------------------------------------------------------------------------------------------------------------------------------
				"all" => '/(\d+:\d+)\sJT;(.*?);(.*?);(.*)/'),

			"roundStart" => array(
				# VERSION	LOGPRINT	STATUS: ONLY FOR PAM4
				# ----------------------------------------------------------------------------------------------------------------------------------------------------------------
				# MOD(PAM4) 5672:43 RS;6
				# ----------------------------------------------------------------------------------------------------------------------------------------------------------------
				"all" => '/\d+:\d+\sRS;(.*)/'),
		
			"roundWin" => array(
				# VERSION	LOGPRINT	STATUS: ONLY FOR COD1 AND PAM4 (AS FAR AS WE KNOW)
				# ----------------------------------------------------------------------------------------------------------------------------------------------------------------
				# COD10		31:22 W;allies
				# MOD(PAM4) 5672:43 RS;6
				# ----------------------------------------------------------------------------------------------------------------------------------------------------------------
				"all" => '/(\d+:\d+)\s(RO|W);(.*)/'),

			"actions" => array(
				# VERSION	LOGPRINT	STATUS: UNCONFIRMED
				"cod10" => '/(\d+:\d+)\sA;\d;(\d);(allies|axis);.*;(bomb_(plant|defuse))/',
				"cod15" => '/(\d+:\d+)\sA;\d;(\d);(allies|axis);.*;(bomb_(plant|defuse))/',
				"cod40" => '/(\d+:\d+)\sA;(bomb_planted|bomb_defused|hq_captured|hq_destroyed|captured_a|captured_b|captured_c);(.*);(.*)/'),
			
			"pickups" => array(
				# VERSION	LOGPRINT	STATUS: UNCONFIRMED
				"cod10" => '/(\d+:\d+)\s(Weapon|Item);0;(\d);.*;(.*)/'),
			
			"teamScore" => array(
				# VERSION   LOGPRINT    STATUS: UNCONFIRMED
				"all" => '/\d+:\d+\sMO;(.*?);(.*?);(.*)/'),					
		)
	);
?>
