<?php
/*
	**************************************************************
	* codalyzer
	* -  Index page
	**************************************************************
*/

session_start();
$start = (float) microtime();

include 'classes/config.php';
$config = new config('../config.ini');

include_once 'inc/header.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>Call of Duty 4 - Hall of Fame</title>
		<meta name="description" content="CoD4-stats">
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
		<link href="style.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<div id="box">
            <!-- QUOTE START-->
			<div id="sitat">
				<?php include FRONTEND_PATH.'/inc/quote.php'; ?>
			</div>	
            <!-- QUOTE END-->
            
            <!-- HEADER TEXT START-->
			<div id="overskrift">
				<a href="index.php"><h1>www.norsof.org</h1></a>
			</div>		
            <!-- HEADER TEXT END-->
            
            <!-- HEADER START-->
			<div id="header">
				<div id="menu">
					<?php include FRONTEND_PATH.'/inc/navigation.php'; ?>
				</div>
			</div>
            <!-- HEADER END-->
            
            <!-- MAIN CONTENTS START-->
            <div id="left">
                <div class="twentymargin">          
                    <?php include FRONTEND_PATH.'/inc/contents.php'; ?>		
                </div>
            </div>
            <!-- MAIN CONTENTS END-->
            
            <!-- RIGHT BAR START -->
            <div id="right">
                <div class="tenmargin">
                    <?php	
                        include FRONTEND_PATH.'/inc/right.php'; 
                    ?>
                </div>
            </div>        
            <!-- RIGHT BAR END -->
  
            <!-- FOOTER START -->
			<div id="footer">
				<div class="footerserver">
					SERVER.NORSOF.ORG - 94.75.230.52:28960
				</div>
				<div class="footertext">
					<div align="right">
						<?php
						echo "GENERATED IN ".round((float)microtime()-$start,5)." SEC";
						?>
					</div>
				</div>					
			</div>	
            <!-- FOOTER END -->
		</div>
	</body>
</html>
