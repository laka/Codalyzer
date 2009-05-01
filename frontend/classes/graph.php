<?php
/*
	**************************************************************
	* codalyzer
	* -  Grapher
	**************************************************************
	
	Draws graphs based on data arrays in this format:
	[] = array("Curve name",  "#color", array(x0,y0,x1,y1...))
	The indexes of the data elements have to start at 0
	Even indexes are x-coordinates, odd indexes are y-coordinates.
*/	

class graph {

    private $data, $height, $width, $img, $xmax, $ymax, $xmin, $ymin, $yunit, $xunit, $xrange, $yrange, $x1, $x2, $y1, $y2;
	
    // SHOULD probably consider replacing some public variables with set-functions
	public $bkg                 = '313738';             // background color
	public $squarebkg           = '212526';             // background color of square
	public $squarebkg2Y         = '313131';             // second background color of square
	public $dualcolorsY         = false;                // enable second background color of square
	public $gridcolor           = '535353';             // grid color
	public $squarecolor         = '535353';             // square color
	public $graphnamescolor     = 'ffffff';             // legend color
	public $numvaluescolor      = 'ffffff';             // value color
	public $gridx               = false;                // enable grid X
	public $gridy               = true;                 // enable grid Y
	public $square              = true;                 // enable square
	public $legend 				= true;                 // enable legend
	public $legendsize 			= 14;                   // height of legend text and dots
	public $addendpoints 		= true;                 // add endpoints (so all graphs are drawn to x-max)
	public $valuesy 			= true;                 // enable units on the y-axis
	public $valuesx 			= true;                 // enable units on the x-axis	
	public $numvaluesy          = 6;                    // number of units on the y-axis
	public $numvaluesx 			= 5;                    // number of units on the x-axis
	public $decimalsvaluesy     = 1;                    // number of decimals on the units on the y-axis
	public $decimalsvaluesx     = 1;                    // number of decimals on the units on the x-axis
	public $simplify            = true;                 // enable simplification of values
	public $smoothpasses        = 2;                    // number of passes in the smoothing algorithm
	public $valuesywidth        = 30;                   // width reserved for units on the y-axis
	public $valuesxheight       = 20;                   // height reserved for units on the x-axis
	public $padding             = 10;                   // total padding
	public $decimalprecision    = true;                 // enable decimals
	
	// creates a new image handler
	public function __construct($data 			= array(), 
                                $width 			= 300, 
                                $height 		= 200
                                ) {
		$this->img 		= imagecreatetruecolor($width, $height);	
		$this->data 	= $data;
		$this->height	= $height;
		$this->width	= $width;	
		
		if(function_exists(imageantialias)){
			imageantialias($this->img, true);
		}			
		$this->rangeFinder();
	}

	// finds the largest x-coordinate
	private function xMax(){
		$this->xmax = $this->data[0][2][0];	
		foreach($this->data as $graf){
			for($i=0; $i<count($graf[2]); $i+=2){
				if($graf[2][$i] > $this->xmax){
					$this->xmax = $graf[2][$i];
				}
			}
		}
	}

	// finds the largest y-coordinate
	private function yMax(){
		$this->ymax = $this->data[0][2][1];	
		foreach($this->data as $graf){
			for($i=1; $i<count($graf[2]); $i+=2){
				if($graf[2][$i] > $this->ymax){
					$this->ymax = $graf[2][$i];
				}
			}
		}
	}	
	
	// finds the smallest x-coordinate
	private function xMin(){
		$this->xmin = $this->data[0][2][0];	
		foreach($this->data as $graf){
			for($i=0; $i<count($graf[2]); $i+=2){
				if($graf[2][$i] < $this->xmin){
					$this->xmin = $graf[2][$i];
				}
			}
		}
	}	

	// finds the smallest y-coordinate
	private function yMin(){
		$this->ymin = $this->data[0][2][1];	
		foreach($this->data as $graf){
			for($i=1; $i<count($graf[2]); $i+=2){
				if($graf[2][$i] < $this->ymin){
					$this->ymin = $graf[2][$i];
				}
			}
		}
	}		
	
	// finds the range and units
	private function rangeFinder(){
		$this->xMax();
		$this->xMin();		
		$this->yMax();
		$this->yMin();		
		$this->xrange = $this->xmax-$this->xmin;
		$this->yrange = $this->ymax-$this->ymin;	
	}

	// adds endpoints to the graphs (the last specified point continues to be used)
	private function addEndPoints(){
		if($this->addendpoints){
			foreach($this->data as $p=>$d){		
				// finds the last y-coordinate
				$endpoint = end($d[2]);
				
				// if the last x-coordinate happends to be the largest x-value globally, we don't do anything...
				if(prev($d[2]) != $this->maxx){
					$this->data[$p][2][] = $this->maxx;
					$this->data[$p][2][] = $endpoint;				
				}				
			}	
		}
	}

	// even out a point
	private function average ($y0, $y1, $y2){
		return round(($y0+$y1+$y2)/3,2);
	}
	
	// simplifies a sequence of numbers (ALPHA!!)
	private function simplify (){
		if($this->simplify){
			for($u = 0; $u < $this->smoothpasses; $u++){
				foreach($this->data as $num=>$graph){
					// loops through all points
					for($i=1; $i<count($graph[2]); $i+=2){
						// x-verdien er uendret
						$arr[] = $graph[2][$i-1];

						if($i >= 2 && (count($graph[2])-$i >= 2)){
							$arr[] = $this->average($graph[2][$i-2], $graph[2][$i], $graph[2][$i+2]);
						} else {
							$arr[] = $graph[2][$i];
						}
					}
					$this->data[$num][2] = $arr;
					unset($arr);
				}
			}
		}	
	}
	
	// converts hex-colors to rgb arrays.
	private function hex2rgb ($color){
		$rgb = array();
		for($i=0; $i<3; $i++){
			$rgb[$i] = hexdec(substr($color, $i*2,2));
		}
		return $rgb;
	}
	
	// allocates a color given in hex
	private function colorAllocate($color) {
		$colorRGB = $this->hex2rgb($color);
		return imagecolorallocate($this->img, $colorRGB[0], $colorRGB[1], $colorRGB[2]);
	}

	// calculates which pixel ranges we can draw inside (reserving space for legend and values)
	private function graphPlanner(){
		// if values are printed on the y-axis, we need to reserve some space to the left of the diagram
		if($this->valuesy){
			$this->x1 = $this->valuesywidth;
		}
		// y2 can't equal the height
		$this->y2 = $this->height-1;
		
		// if values are printed on the x-axis, or the legend is printed, we must reserve some space under the diagram
		if($this->valuesx || $this->legend){
			// if values are printed under the diagram, we must reserve some space
			if($this->valuesx)
				$this->y2 = $this->height-$this->valuesxheight-1;
			// if legend is printed under the diagram, we must reserve some space
			if($this->legend)
				// we reserve legendsize pixels per graph, plus 2 pixels of margin between the lines, plus 5 pixels on the top
				$this->y2 = $this->y2-(count($this->data)*$this->legendsize)-2*(count($this->data))-5;
			if(!$this->valuesy){
				$this->x1 = 0;			
			}
		}		
		$this->y1 = 0;
		$this->x2 = $this->width-1;		

		$this->xunit = ($this->x2-$this->x1)/$this->xrange;
		$this->yunit = ($this->y2-$this->y1-$this->padding)/$this->yrange;			
	}
	
	// creates a frame around the graph
	private function constructSquare(){
		if($this->square){
			$squarecol = $this->colorAllocate($this->squarecolor);
			$squarebkg = $this->colorAllocate($this->squarebkg);

			imagerectangle  ($this->img, $this->x1, $this->y1, $this->x2, $this->y2, $squarecol);
			imagefill($this->img, $this->x1+1, $this->y1+1, $squarebkg);
		}
	}
	
	// creates vertical lines
	private function gridX(){
		if($this->gridx || $this->valuesx){		
			// calculates the number of pixels between the vertical lines
			$stepwidth = $this->xrange/($this->numvaluesx-1);
			
			$ystart = $this->y1;
			$yend 	= $this->y2;
			$xstart = $this->x1;
			$xend 	= $this->x2;				
	
			if($this->valuesx){
				$yend = $this->y2+4;
				if(!$this->gridx){
					$ystart = $this->y2;
				}
				// calculates the values
				for($i=0; $i<=($this->numvaluesx-1); $i++){
					$xvalues[] = round($i*$stepwidth+$this->xmin,$this->decimalsvaluesx);
				}		
				$d = $this->colorAllocate($this->numvaluescolor);
			}
			
			$c = $this->colorAllocate($this->gridcolor);
			
			// prints out the lines/ticks/values
			for($i=0; $i<=($this->numvaluesx-1);$i++){
				// her må vi finne en løsning så alle x-verdiene synes... litt klønete sånn det er nå.
				if($i == ($this->numvaluesx-1)){
					$offset = imagefontwidth(2)*strlen($xvalues[$i]);
				}
				if(($this->square && $i != 0 && $i != ($this->numvaluesx-1)) || !$this->square){
					imageline  ($this->img, $xstart+($this->x2-$this->x1)/($this->numvaluesx-1)*$i, $ystart, $xstart+($this->x2-$this->x1)/($this->numvaluesx-1)*$i, $yend, $c);
				}
				imagestring($this->img, 2, $xstart+($this->x2-$this->x1)/($this->numvaluesx-1)*$i-$offset, $yend, $xvalues[$i], $d);
			}	
		}
	}	

	// creates horizontal lines
	private function gridY(){
		if($this->gridy || $this->valuesy){		
			if($this->valuesy){
				$stepheight = $this->yrange/($this->numvaluesy-1);
				for($i=0; $i<=($this->numvaluesy-1); $i++){
					$yvalues[] = round($i*$stepheight+$this->ymin, $this->decimalsvaluesy);
				}								
				$xstart = $this->valuesywidth-3;
				$xend = $this->valuesywidth;		
			} 
			else {
				$xstart = 0;			
			}
			if($this->gridy){
				$xend = $this->width;	
			}	
			$c = $this->colorAllocate($this->gridcolor);
			$d = $this->colorAllocate($this->numvaluescolor);
			if($this->dualcolorsY){
				$e = $this->colorAllocate($this->squarebkg2Y);	
			}	
			
			for($i=0; $i<=($this->numvaluesy-1);$i++){
				imagestring($this->img, 2, 2, ($this->y2-$this->y1-$this->padding)/($this->numvaluesy-1)*$i+($this->padding/2)-7, $yvalues[($this->numvaluesy-1)-$i], $d);
				imageline  ($this->img,$xstart,($this->y2-$this->y1-$this->padding)/($this->numvaluesy-1)*$i+($this->padding/2),$xend,($this->y2-$this->y1-$this->padding)/($this->numvaluesy-1)*$i+($this->padding/2), $c);
				if($this->dualcolorsY){
					if($i%2)
						imagefill  ($this->img, $xstart+10, ($this->y2-$this->y1-$this->padding)/($this->numvaluesy-1)*$i+($this->padding/2)-1, $e);
				}
			}				
		}	
	}	
	
	// adds legend
	private function addLegend(){
		if($this->legend){
			$t = $this->colorAllocate($this->graphnamescolor);	
			if($this->valuesx){
				$ynames = $this->y2+$this->valuesxheight+5;
			}
			else{
				$ynames = $this->y2+5;
			}
			$i=0;
			foreach($this->data as $graph){
				$graphcol = $this->colorAllocate ($graph[1]);
				$ysize = $ynames + $i*$this->legendsize;
				imagefilledrectangle ($this->img, $this->x1, $ysize, $this->x1+8, $ysize+8, $graphcol);
				imagestring($this->img, 2, $this->x1+12, $ysize-3, $graph[0], $t);	
				$i++;
			}
		}
	}
	
	// draws the graphs
	private function drawGraph(){
		$i=0;
		foreach($this->data as $graph){
			$graphcol = $this->colorAllocate ($graph[1]);	
				
			for($x=0; $x<count($graph[2])/2; $x++){
				if($x > 0){
					$prevx = prev($graph[2]);
					$prevy = next($graph[2]);				
				} else {
					$prevx = current($graph[2]);
					$prevy = next($graph[2]);
				}
				
				$nextx = next($graph[2]);		
				$nexty = next($graph[2]);				
				if(is_numeric($nexty) && is_numeric($nextx)){
					imageline($this->img, ($prevx-$this->xmin)*$this->xunit+$this->x1, 
										  ($this->ymax-$prevy)*$this->yunit+($this->padding/2), 
										  ($nextx-$this->xmin)*$this->xunit+$this->x1, 
										  ($this->ymax-$nexty)*$this->yunit+($this->padding/2), 
										  $graphcol);
				}
			}
		$i++;
		}
	}
	
	// runs functions and creates the diagram
	public function createGraph(){
		$mainbkg = $this->colorAllocate($this->bkg);
		imagefill($this->img, 2, 2, $mainbkg);	
		
		$this->simplify();
		$this->graphPlanner();
		$this->constructSquare();
		$this->gridY();		
		$this->gridX();		
		$this->addEndPoints();
		$this->drawGraph();
		$this->addLegend();
		
		imagepng($this->img);
	}
	
	public function debug(){
		echo "XMAX:" . $this->xmax;
		echo "<br>";
		echo "YMAX:" . $this->ymax;
		echo "<br>";
		echo "XMIN:" . $this->xmin;		
		echo "<br>";
		echo "YMIN:" . $this->ymin;		
	}
}

/*
//Uncomment for testing
header ('Content-type: image/png');
$data[0] = array("Linje 1", "ffffff", array(19,1000.50,20,1002.96,21,1005.71,23,1006.13,24,1007.95,25,1007.40,26,1006.38,27,1008.35,28,1010.80,29,1010.75,30,1013.48,31,1013.48,32,1015.79,33,1014.22,34,1015.41,36,1017.64,37,1015.42,38,1014.21,40,1009.10,41,1008.85,43,1009.51,45,1009.47,46,1009.29,48,1007.62,49,1003.72,50,1002.20,51,1001.22,53,999.55,55,999.87,57,997.63,59,1000.40,62,1003.42,67,1002.70,68,1003.00,69,1001.23,70,1007.30,84,1006.29,85,1004.77,86,1007.21,87,1008.71,88,1009.07,89,1011.19,90,1013.82,93,1019.08,95,1024.85,105,1024.45,107,1023.85,115,1023.85,116,1024.26,119,1023.44,121,1023.77,123,1025.35,125,1029.71,126,1029.21,142,1029.21,144,1028.12,145,1028.12,160,1028.12,161,1026.44,162,1026.77,163,1030.27,164,1031.59,193,1035.36,194,1033.72,196,1033.72,200,1029.97,201,1029.76,207,1030.06,209,1029.52,210,1030.22,215,1029.78,219,1031.11,220,1032.75,222,1033.24,223,1034.57,225,1034.91,226,1033.83,228,1041.10,229,1045.40,231,1048.22,232,1051.16,233,1056.83,234,1056.47,235,1054.57,236,1060.14,237,1059.64,238,1059.64,242,1061.02,243,1064.18,246,1063.21,247,1063.93,248,1063.93,249,1063.93,250,1064.34,251,1064.56,252,1064.09,253,1070.49,254,1070.49,255,1070.62,265,1070.12,266,1070.12,267,1070.12,268,1070.51,269,1071.57,270,1071.57,307,1070.97,308,1069.58,309,1067.48,310,1067.48,311,1067.48,320,1066.83,321,1069.14,322,1072.17,324,1070.67,325,1070.87,326,1071.03,327,1075.63,328,1074.00,329,1072.96,331,1076.15,333,1077.52,334,1077.17,335,1077.33,336,1077.68,338,1082.67,339,1081.41,340,1081.58,341,1079.54,342,1079.44,343,1081.40,345,1079.90,349,1079.90,350,1079.40,353,1079.40,354,1079.40,362,1079.40,363,1081.71,364,1087.16,365,1085.67,366,1085.67,368,1084.91,369,1084.91,370,1085.99,373,1083.41,374,1081.36,375,1080.74,376,1081.48,377,1081.21,385,1081.21,386,1081.37,395,1079.69,396,1079.41,400,1082.13,401,1082.73,402,1082.00,403,1082.65,404,1078.92,405,1078.59,406,1078.97,410,1077.93,411,1075.59,412,1074.80,413,1076.21,414,1077.12,415,1078.50,416,1077.48,442,1077.48,443,1076.43,444,1076.43,445,1077.32,446,1076.51,447,1078.02,448,1077.64,449,1076.35,450,1077.47,451,1076.79,452,1073.80,526,1074.29,527,1070.33,528,1072.19,529,1071.62,601,1071.62,602,1073.30,603,1072.71,604,1071.74,605,1077.88,705,1080.20,711,1080.99,712,1079.90,713,1080.47,715,1084.21,810,1086.90,811,1087.81,814,1088.13,872,1088.13,873,1088.13,983,1088.60,984,1088.15,985,1086.90,986,1085.82,988,1086.59,989,1089.51,990,1091.34,991,1091.34,992,1092.10,993,1091.94,994,1090.67,995,1089.81,996,1088.69,1151,1088.31,1152,1084.79,1153,1085.37,1189,1087.49,1190,1087.49,1191,1087.49,1227,1083.73,1228,1083.31,1229,1080.27,1230,1078.95,1232,1081.55,1233,1079.19,1234,1081.96,1235,1082.10,1236,1079.66,1237,1081.12,1257,1085.21,1262,1087.50,1263,1088.07,1265,1088.01,1273,1088.01,1276,1088.60,1277,1086.95,1278,1090.16,1279,1091.56,1280,1093.95,1281,1093.94,1282,1099.45,1283,1096.51,1337,1095.60,1338,1097.06,1339,1096.50,1341,1096.50,1342,1094.20,1343,1094.20,1345,1093.78,1346,1095.06,1347,1097.47,1348,1097.65,1350,1098.82,1351,1100.15,1352,1099.06,1353,1099.34,1463,1098.62,1464,1097.96,1465,1097.86,1466,1098.72,1467,1100.52,1469,1100.52,1470,1104.64,1471,1109.52,1472,1109.52,1473,1109.52,1502,1109.52,1503,1109.52,1504,1109.52,1505,1109.52,1506,1111.03,1507,1112.49,1508,1111.05,1509,1112.01,1510,1115.19,1511,1114.73,1512,1112.39,1540,1110.26,1541,1109.59,1573,1109.09,1574,1107.49,1575,1109.35,1576,1109.56,1577,1111.52,1578,1109.26,1579,1104.66,1606,1104.66,1916,1104.88,1917,1104.88,1918,1104.95,1920,1104.95,1924,1104.95,1926,1104.95,1928,1104.31,1956,1103.65,1957,1103.29,1958,1103.55,1959,1103.22,1960,1103.61,1961,1104.79,1962,1105.86,1963,1103.89,1985,1101.99,1986,1103.47,1987,1106.62,1988,1106.11,1989,1106.79,2217,1106.89,2218,1106.89,2224,1104.13,2225,1101.32,2226,1101.32,2227,1101.88,2346,1106.81,2347,1107.18,2348,1107.37,2349,1105.04,2379,1103.82,2380,1103.81,2411,1101.05,2412,1100.03,2413,1098.99,2441,1098.99,2442,1097.53,2443,1096.34,2444,1097.28,2445,1093.32,2446,1094.68,2447,1093.44,2448,1091.75,2526,1095.67,2527,1093.49,2528,1090.40,2529,1090.06,2530,1090.66,2532,1088.28,2533,1089.61,2548,1090.43,2549,1088.57,2550,1090.45,2551,1090.45,2678,1088.78,2681,1087.87,2683,1087.69,2684,1089.90,2686,1088.05,2687,1087.54,2688,1083.13,2691,1077.10,2692,1077.10,2728,1073.59,2729,1070.63,2730,1069.06,2731,1068.15,2753,1073.27,2754,1063.44));

$t = new graph($data);
$t->createGraph(); 
*/
?>