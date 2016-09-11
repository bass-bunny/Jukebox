<?php

/**
*	Mysql database class
*/
class Database {
	private $_connection;
	private static $_instance; //The single instance

	private $_host = "localhost";
	private $_username = "root";
	private $_password = "password1000";
	private $_database = "zwytytws_albums";

	public static $_table_albums = "albums";
	public static $_table_radio_stations = "radio_stations";

	/*
	*	Get an instance of the Database
	*	@return Instance
	*/
	public static function getInstance() {
		if(!self::$_instance) { // If no instance then make one
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	// Constructor
	public function __construct() {
		try {
			$this->_connection = new PDO("mysql:host=$this->_host;dbname=$this->_database;charset=utf8mb4", $this->_username, $this->_password);
		} catch (PDOException $e) {
			echo 'Connection failed: ' . $e->getMessage();
		}
	}

	// Magic method clone is empty to prevent duplication of connection
	private function __clone() { 
	}

	// Get connection
	public function getConnection() {
		return $this->_connection;
	}

    /**
     * Execute a raw query on the database.
     *
     * @param string $query
     * @return bool
     */
	public function rawQuery($query = "") {
		if($query == "") {
			return false;
		}

		try {
			$stmt = $this->getConnection()->prepare($query);
			return $stmt->execute();
		} catch (PDOException $e) {
			return false;
		}
	}

    /**
     * Run an insert query.
     *
     * @param $table
     * @param $array
     * @return bool
     */
	public function insert($table, $array) {
		$array_fields = array_keys($array);

		$fields = '(' . implode(',', $array_fields) . ')';
		$val_holders = '(:' . implode(', :', $array_fields) . ')';

		$sql = "INSERT INTO $table";
		$sql .= $fields . ' VALUES ' . $val_holders;

		$stmt = $this->_connection;
		$stmt = $stmt->prepare($sql);
		
		foreach ($array as $key => $value)
		    $stmt->bindValue(":$key", $value);

		return $stmt->execute();
	}

    /**
     * Select $columns from $table with additional $query.
     *
     * @param string $columns The columns to select. Default is *.
     * @param string $table The table where to perform the select from. Default is albums.
     * @param string $query The additional query: WHERE ...
     *
     * @return array|null
     */
	public function select($columns = "*", $table = "albums", $query = "WHERE 1") {
		if(is_array($columns)) {	
			$columns = implode(', ', $columns);
		}

		$sql = "SELECT $columns FROM `$table`";
		$sql .= $query;

		$stmt = $this->_connection->prepare($sql);
		
		// DEBUG
		// $stmt->debugDumpParams();

		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_CLASS);
		$row_count = $stmt->rowCount();

		if($row_count == 0) {
			return null;
		}

		return $rows;
	}

    /**
     * Update a record.
     *
     * @param string $table
     * @param $array
     * @param string $where
     * @return bool
     */
	public function update($table = "", $array, $where = "") {
		if($table == "" || $array == null) {
			return false;
		}

		$sql = "UPDATE $table SET ";

		foreach ($array as $key => $value) {
			$sql .= $key . "=" . "'$value'" . ",";
		}

		$sql = rtrim($sql, ",");

		$sql .= " WHERE " . $where;

		$stmt = $this->_connection;
		$stmt = $stmt->prepare($sql);
		
		foreach ($array as $key => $value)
		    $stmt->bindValue(":$key", $value);

		// // DEBUG
		// $stmt->debugDumpParams();

		return $stmt->execute();
	}


    /**
     * Remove record(s).
     *
     * @param string $table
     * @param string $where
     * @return bool
     */
	public function delete($table, $where) {
		if($table == null || $where == null) {
			return false;
		}

		$sql = "DELETE FROM $table WHERE ";
		$sql .= $where;

		try {
			$stmt = $this->getConnection()->prepare($sql);
			return $stmt->execute();
		} catch (PDOException $e) {
			return false;
		}
	}

    /**
     * Drop one or more tables.
     *
     * @param string $tables
     * @return bool
     */
	public function drop($tables = "") {

		switch($tables) {
			case "albums":
				$sql = "TRUNCATE TABLE " . self::$_table_albums;
				break;
			case "radio_stations":
				$sql = "TRUNCATE TABLE " . self::$_table_radio_stations;
				break;
			default:
				$sql = "TRUNCATE TABLE " . self::$_table_albums . "; TRUNCATE TABLE " . self::$_table_radio_stations;
		}
		
		try {
			$stmt = $this->getConnection()->prepare($sql);
			return $stmt->execute();
		} catch (PDOException $e) {
			return false;
		}
	}

}