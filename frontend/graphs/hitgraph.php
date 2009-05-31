<?php
/*
	**************************************************************
	* codalyzer
	* - Hit diagram
	**************************************************************
*/

include '../../config.php';
include '../classes/graph.php';
include '../inc/header.php';

//header ('Content-type: image/png');

// validates $_GET-input
if(strlen($_GET['h']) > 0){
    $id = $db->sqlQuote($_GET['h']);
    $sql = "SELECT 1 FROM profiles where id='$id' LIMIT 1";   
	$result = mysql_query($sql);
}

// we continue, if the player existed
if(mysql_num_rows($result) == 1 && (strlen($_GET['t']) > 0)){
    if(!SMALL_HITDIAGRAM){
        $figure 		= '../img/soldatdiagram.png';
        $max_diameter	= '300'; 
        
        // THE COORDINATES OF THE CIRCLES (CENTRUM) (third coordinate is the number of kills)
        $head 				= array (110, 17, 0);
        $neck				= array (110, 32, 0);

        $torso_upper		= array (110, 50, 0);
        $torso_lower		= array (110, 104, 0);

        $left_hand			= array (153, 110, 0);
        $right_hand			= array (100, 96, 0);

        $right_arm_upper 	= array (73, 56, 0);
        $left_arm_upper		= array (139, 56, 0);

        $right_arm_lower 	= array (84, 88, 0);
        $left_arm_lower		= array (150, 92, 0);

        $right_leg_lower	= array (84, 208, 0);
        $left_leg_lower		= array (132, 208, 0);

        $right_leg_upper	= array (90, 160, 0);
        $left_leg_upper		= array (130, 160, 0);

        $right_foot			= array (80, 255, 0);
        $left_foot			= array (135, 255, 0);
        
        $none               = array (0, 170, 0);
    } else {
        $figure 		= '../img/soldatdiagram_small.png';
        $max_diameter	= '250'; 
        
        // THE COORDINATES OF THE CIRCLES (CENTRUM) (third coordinate is the number of kills)
        $head 				= array (93, 15, 0);
        $neck				= array (95, 28, 0);

        $torso_upper		= array (95, 46, 0);
        $torso_lower		= array (99, 87, 0);

        $left_hand			= array (134, 96, 0);
        $right_hand			= array (89, 85, 0);

        $right_arm_upper 	= array (64, 51, 0);
        $left_arm_upper		= array (123, 48, 0);

        $right_arm_lower 	= array (73, 76, 0);
        $left_arm_lower		= array (129, 81, 0);

        $right_leg_lower	= array (75, 187, 0);
        $left_leg_lower		= array (114, 187, 0);

        $right_leg_upper	= array (78, 138, 0);
        $left_leg_upper		= array (110, 138, 0);

        $right_foot			= array (72, 223, 0);
        $left_foot			= array (118, 223, 0);
        
        $none               = array (0, 170, 0);
    }
    
	$im = imagecreatefrompng ($figure);
	$circlecolor = imagecolorallocatealpha($im, 127, 0, 5, 50);
	$bordercolor = imagecolorallocate($im, 33, 0, 1);
    
    $bodyparts = array('head', 'neck', 'torso_upper', 'torso_lower', 'left_hand', 'right_hand', 'right_arm_upper', 'left_arm_upper', 
    'right_arm_lower', 'left_arm_lower', 'right_leg_lower', 'left_leg_lower', 'right_leg_upper', 'left_leg_upper', 'right_foot', 'left_foot', 'none');
    
	$hitarray = explode(',', $_GET['t']);
    
    // we validate the data from the url...
    // the array is in the format bodypart1, kills1, bodypart2, kills2...
	for($i=0; $i<count($hitarray); $i+=2){
        // we found a valid body part...
		if(strlen($hitarray[$i]) > 0 && in_array($hitarray[$i], $bodyparts) && is_numeric($hitarray[$i+1])){
			${$hitarray[$i]}['2']+=$hitarray[$i+1];
			$total += $hitarray[$i+1];			
		}	
	}	
    
	$circles = array ($head, $neck, $torso_upper, $torso_lower, $left_hand, $right_hand, $right_arm_upper, $left_arm_upper, $right_arm_lower, $left_arm_lower, $right_leg_upper, $left_leg_upper, $right_leg_lower, $left_leg_lower, $right_foot, $left_foot, $none);

    // loop through the circles
	foreach ($circles as $element){
		$width = ($element['2']/$total) * $max_diameter + 5;
		
		imagefilledellipse($im, $element['0'], $element['1'], $width, $width, $circlecolor);
		imageellipse ($im, $element['0'], $element['1'], $width, $width, $bordercolor);
	}
	imagepng($im);
}	
?>



