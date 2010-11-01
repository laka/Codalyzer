<h2>Users</h2>
<ul>
    <li><a href="?p=users&amp;m=add">Add user</a></li>
    <li><a href="?p=users&amp;m=delete">Delete user</a></li>
    <li><a href="?p=users&amp;m=reset">Reset password</a></li>
</ul>

<?php 
if($_GET['m'] == 'add'){
    if($_POST['newusername'] && $_POST['newpassword'] && $_POST['newpasswordrepeat']){
        // The form has been submitted..
        
        // remove whitespace
        $_POST['newusername'] = trim($_POST['newusername']);
        if (!get_magic_quotes_gpc()) {
            $_POST['newusername'] = addslashes($_POST['newusername']);
        }    
        
        // query for checking if the username is available
        $sql = "SELECT * FROM users WHERE username ='". $_POST['newusername'] . "'";

        // gives an error if the sql query fails
        if( !$result = mysql_query($sql) ){
            $error[] = "Could not check availability in the database.";
        }   
        
        $row = mysql_fetch_assoc($result);
        
        // checks if the passwords matches
        if($_POST['newpassword'] != $_POST['newpasswordrepeat']){
            $error[] = "The passwords did not match.";
        }
        
        // controls the length of the username
        if(strlen($_POST['newusername']) < 3){
            $error[] = "The username was too short.";
        }        

        // makes sure the password is long enough 
        if(strlen($_POST['newpassword']) < 6){
            $error[] = "The password was too short.";
        } 

        // gives an error if the username is taken
        if($row['username'] == $_POST['newusername']){
            $error[] = "The username is already taken.";
        }                
        
        if(count($error) > 0){
            echo "<strong>Error:</strong>\n<ul>";
            foreach($error as $msg){
                echo "<li>$msg</li>\n";
            }
            echo "</ul>\n";
        } else {
            $password = md5($_POST['newpassword']);        
            $sql = "INSERT INTO users(username, password) VALUES ('" . $_POST['newusername'] . "', '" . $password . "')";
            if(@mysql_query($sql)){
                echo "<strong>Added: </strong>The user was successfully added.";
            } else {
                echo "<strong>Error: </strong>Could not add the user to the database";
            }
        }

    }
?>
<h3>Add user</h3>
<form action="?p=users&amp;m=add" method="post">
    <p><strong>Username</strong><br />
        <input type="text" name="newusername" size="40">
    </p>
    <p><strong>Password</strong><br />
        <input type="password" name="newpassword" size="40">
    </p>    
    <p><strong>Repeat password</strong><br />
        <input type="password" name="newpasswordrepeat" size="40">
    </p>     
    <input type="submit" value="Add user">
</form>

<?php
}   // endif
 elseif ($_GET['m'] == 'delete'){
 
 }


?>

