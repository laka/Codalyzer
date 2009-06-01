<?php
class logfile {
	private $logfile, $numlines, $path, $proto, $size;

	public function __construct() {
		$this->db = database::getInstance();
		
		$logfile = database::getInstance()->singleRow(
			"SELECT value AS name FROM config WHERE ckey=\"logfile\"");
		$log = database::getInstance()->singleRow(
			"SELECT numlines FROM loghist WHERE filename=\"$logfile[name]\"");
		$path = database::getInstance()->singleRow(
			"SELECT value AS name FROM config WHERE ckey=\"logfile_path\"");
		$transfer = database::getInstance()->singleRow(
			"SELECT value AS proto FROM config WHERE ckey=\"transfer_protocol\"");
		$size = database::getInstance()->singleRow(
			"SELECT filesize FROM loghist WHERE filename=\"$this->logfile\" ORDER BY id DESC LIMIT 1");
		
		$this->logfile  = $logfile[name];
		$this->numlines = $log[numlines];
		$this->path 	= $path[name];
		$this->proto    = $transfer[proto];
		$this->size	    = $size[filesize];
		
		if(!file_exists($this->logfile)) {
			system("touch $this->logfile"); 
		}
	}

	public function getLatestLog() {
		if($this->proto == 'ftp') {
			$host = database::getInstance()->singleRow(
				"SELECT value AS name FROM config WHERE ckey=\"ftp_host\"");
			$user = database::getInstance()->singleRow(
				"SELECT value AS name FROM config WHERE ckey=\"ftp_user\"");
			$pass = database::getInstance()->singleRow(
				"SELECT value AS word FROM config WHERE ckey=\"ftp_pass\"");	
			
			$open  = ftp_connect($host[name]);
			$login = ftp_login($open, $user[name], $pass[word]);
			ftp_chdir($open, $this->path);
			
			if(ftp_get($open, $this->logfile, $this->logfile, FTP_BINARY, $this->size)) {
				$new_file_size = filesize($this->logfile);
				$new_num_lines = count(file($this->logfile));
				
				database::getInstance()->sqlResult(
					"INSERT INTO loghist (filename, filesize, numlines) 
					VALUES(\"$this->logfile\", \"$new_file_size\", \"$new_num_lines\")");
			}
		}
	}
	
	public function returnArray() {
		return file($this->logfile);
#return array_slice($logarray, $this->numlines);
	}

	public function getGameName() {
		return 'cod';
	}
}

?>
