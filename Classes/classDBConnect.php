<?php

/*
 * DB Configuration information is stored here.
 * ideally you should read these vaules from an
 * external properties file.
 */

class DBConnect extends mysqli {
	
//  --  private $databaseHost = "localhost";
	private $databaseHost;
	private $databasePort;
	private $databaseName;
	private $databaseUName;
	private $databasePWord;
	
//	function  __construct($user, $password, $host='home.sawatzki.eu', $port='3306', $name='Deputat') {
//	function  __construct($user, $password, $host='p:sr216n16.ls.haw-hamburg.de', $port='3306', $name='Deputat') {
	function __construct($user, $password, $database) {
		
//		error_log("DBConnect - Memory usage before construction: " . number_format(memory_get_usage()) . "\n");
		
		switch ($database) {
			case 'Test':
				$this->databaseHost = 'p:home.sawatzki.eu';
				$this->databasePort = '3306';
				$this->databaseName = 'Deputat';
				break;
			case 'Prod':
				$this->databaseHost = 'p:sr216n16.ls.haw-hamburg.de';
				$this->databasePort = '3306';
				$this->databaseName = 'Deputat';
			default:
				break;
		}
		$this->databaseUName = $user;
		$this->databasePWord = $password;

//		error_log("Login with: $database $this->databaseHost, $this->databaseUName, $this->databasePWord, $this->databaseName, $this->databasePort ");
		
		parent::__construct($this->databaseHost, $this->databaseUName, $this->databasePWord, $this->databaseName, $this->databasePort );
		if ($this->is_connected() == FALSE) {
			error_log("$host  $user  $name  $port \n");
			$this->show_error();
			die("connection to $this->databaseHost failed");
		} else {
			error_log("Connection to $this->databaseHost established.");
		}
		
		if (!parent::set_charset("utf8")) {
			$charset = parent::character_set_name();
			error_log("Can't set CharSet to 'utf8'; working with $charset");
		}

	}

	function  __destruct() {
		#$this->close();
//		error_log("DBConnect - Memory usage after destruction: " . number_format(memory_get_usage()) . "\n");
	}

	function get_databaseHost() {
		return $this->databaseHost;
	}

	function get_databaseName() {
		return $this->databaseName;
	}

	function get_databasePort() {
		return $this->databasePort;
	}

	function get_databaseUName() {
		return $this->databaseUName;
	}

	function get_databasePWord() {
		return $this->databasePWord;
	}

	function is_connected() {
		return $this->connect_errno > 0? FALSE: TRUE;
	}

	function show_error() {
		error_log("{$this->connect_errno} \n{$this->connect_error}\n");
	}
	
//	function query(string $query, int $resultmode = MYSQLI_STORE_RESULT) : mysqli_result {
//		$result = parent::query($query, $resultmode);
//		if ($result) {
//			return $result;
//		}
//		return NULL;
//	}
}

