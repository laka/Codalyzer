<?php
/*
	**************************************************************
	* codalyzer
	* - Configuration file handling
	**************************************************************
*/

class config {
    private $config, $file;

    // constructor, makes sure the config file exists, converts keys to constants
    function __construct ($file){
        $this->config = parse_ini_file($file) 
            or die("Error: Could not find the config file.");
        $this->constants();
        $this->file = $file;
    }
    
    private function constants(){
        // SHOULD do some validation to make sure all necessary variables are defined. 
        foreach($this->config as $key => $value){
            if(!is_array($value)){
                // we want the constants to be upper case
                define (strtoupper($key), $value);
            }
        }
    }
    
    // function to modify ini file, does not support arrays
    public function iniSet ($key, $value){
        if(!is_array($value) && isset($this->config[$key])){
            $configfile = file($this->file);
            foreach($configfile as $line=>$keyvalue){
                $pair = explode ('=', $keyvalue);
                if($pair[0] == $key){
                    $configfile[$line] = "$key=$value\n";
                }
            }
            $this->iniWrite ($configfile);
        }
    }
    
    private function iniWrite ($filearray){
        $temp_file = tempnam(sys_get_temp_dir(), 'config');
        $handle = fopen($temp_file, "w");
        foreach($filearray as $line){
            fwrite($handle, $line) 
                or die('<strong>Error:</strong> Could not write to config file.');
        }
        copy($temp_file, $this->file);
        fclose($handle);
    }
}
?>