<?php
/*
	**************************************************************
	* codalyzer
	* - Handles logins
	**************************************************************
*/

class authentication {
    private $connection;
    
    function __construct($loginurl){
        $this->db = database::getInstance();
    
        if(!$this->isAuthorized()){
            if($_POST['password'] && $_POST['username'] && !$this->isBanned()){
                
                // we escape the password and username...
                $password = mysql_real_escape_string($_POST['password']);
                $username = mysql_real_escape_string($_POST['username']);    

                // check if the user exists
                $sql = "SELECT * FROM users WHERE username='" . $username . "' AND password = MD5('" . $password . "')";
                $result = $this->db->sqlResult($sql);
                if(mysql_num_rows($result) == 1){
                    $this->authorize ();
                    header("Location: ".$loginurl);
                } else {
                    $this->wrongInput ();
                }
            }
            if(!$this->isAuthorized() && !$this->isBanned()){
                $this->printForm ($loginurl);
            }
            if($this->isBanned()){
               // do something with banned people
            }            
        }
    }
    
    public function isAuthorized (){
    	return ($_SESSION['authorized'] == 'yes'); 
    }
    
    private function isBanned (){
    	return ($_SESSION['errors'] >= 10); 
    }   

    private function wrongInput (){
    	$_SESSION['errors']++; 
    }       
    
    private function authorize (){
        $_SESSION['authorized'] = 'yes';
        $_SESSION['errors'] = 0;
    }
    
    public function logOut (){
		if($this->isAuthorized ()){
			unset($_SESSION['authorized']);
		}
       header("Location: index.php");
	}
    
    private function printForm ($action){
        echo '<form method="post" action="' . $action . '">';
        echo '<strong>Username</strong><br /><input type="text" size="30" name="username"><br />';
        echo '<strong>Password</strong><br /><input type="password" size="30" name="password"><br />';
        echo '<input type="submit" name="login" value="Login">';
        echo '</form>';
    }

}
?>