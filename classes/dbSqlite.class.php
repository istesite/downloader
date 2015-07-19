<?php
class dbSqlite {
	protected static $conn;

	protected static $lastError;
	protected static $lastQuery;

	protected static $beginTime;
	protected static $timing;

	public function __construct($dbName) {
		self::$beginTime = microtime(true);
		if(!is_dir($dbName)){
			mkdir($dbName,0777);
		}
		self::$conn = new SQLite3($dbName."/".$dbName.".sqlite");
		self::$lastError = self::$conn->lastErrorCode() . " - " . self::$conn->lastErrorMsg();
	}

	public function query($sql) {
		$startTime = microtime(TRUE);
		//$sql = self::clean($sql);
		self::$lastQuery = $sql;
		//self::$lastError =& mysql_error(self::$conn);

		switch (self::getQueryType($sql)) {
			case "sel":
				$result = self::$conn->query($sql);
				break;

			case "ins":
				$result = self::$conn->exec($sql);
				break;

			case "upd":
				$result = self::$conn->exec($sql);
				break;

			case "del":
				$result = self::$conn->exec($sql);
				break;

			default:
				$result = self::$conn->query($sql);
				break;
		}

		self::$timing['query'] = round((microtime(TRUE) - $startTime), 5);

		return $result;
	}

	public function exec($sql) {
		return self::query($sql);
	}

	public function getQueryType($sql) {
		return strtolower(substr($sql, 0, 3));
	}

	public function fetchArray($sql, $rowIndex = Null) {
		$startTime = microtime(TRUE);
		$resultsx = array();

		if (!is_null($rowIndex) and $rowIndex >= 0 and self::getQueryType($sql) == 'sel') {
			$sql = 'SELECT qRL_fdasl534.* FROM (' . $sql . ') AS qRL_fdasl534 LIMIT ' . $rowIndex . ', 1';
			$resultsx = self::fetchArray($sql);
			$resultsx = $resultsx[0];
		}
		else {
			$sqlQuery = self::query($sql);
			while ($rows = $sqlQuery->fetchArray(SQLITE3_ASSOC)) {
				$resultsx[] = $rows;
			}
			unset($sqlQuery);
		}

		self::$timing['fetchArray'] = round((microtime(TRUE) - $startTime), 5);

		return $resultsx;
	}

	public function fetchObject($sql, $rowIndex = Null) {
		$startTime = microtime(true);
		$random = substr(md5(time()),0,6);
		$resultsx = array();

		if (!is_null($rowIndex) and $rowIndex >= 0 and strtolower(substr($sql, 0, 3)) == 'sel') {
			$sql = 'SELECT qRL_' . $random . '.* FROM (' . $sql . ') AS qRL_' . $random . ' LIMIT ' . $rowIndex . ', 1';
			$resultsx = self::fetchObject($sql);
			$resultsx = $resultsx[0];
		}
		else {
			$sqlQuery = self::query($sql);
			while ($rows = self::fetch_object($sqlQuery)) {
				$resultsx[] = $rows;
			}
		}

		self::$timing['fetchObject'] = round((microtime(true) - $startTime),3);

		return $resultsx;
	}

	public function fetch_array($queryResult, $type = SQLITE3_BOTH) {
		return $queryResult->fetchArray($type);
	}

	public function fetch_array_num($queryResult) {
		return $queryResult->fetchArray(SQLITE3_NUM);
	}

	public function fetch_object($queryResult) {
		return $queryResult->fetchObject();
	}

	public function fetch_assoc($queryResult) {
		return $queryResult->fetchArray(SQLITE3_ASSOC);
	}

	public function numRows($sql) {
		return self::numRowsCount($sql);
	}

	public function numRowsCount($sql) {
		$startTime = microtime(true);

		$sql = preg_replace('/^select .*? from (.*?)/ix', 'SELECT count(*) AS dfsa1231fde5 FROM $1', $sql);
		$query = self::fetchArray($sql, 0);
		$numRows = $query['dfsa1231fde5'];

		self::$timing['numRows'] = round((microtime(true) - $startTime), 5);

		return $numRows;
	}

	public function affected_rows() {
		return self::$conn->changes();
	}

	public function insertId() {
		return self::insert_id();
	}

	public function insert_id() {
		return self::$conn->lastInsertRowID();
	}

	public function clean($sql) {
		return self::$conn->escapeString($sql);
	}

	public function nextRow($tableName, $fieldName = 'row', $step = 10) {
		$sql = "SELECT MAX(" . $fieldName . ") AS maxRow FROM " . $tableName;

		if ($fetchResult = self::fetchArray($sql, 0)) {
			$newRow = $fetchResult["maxRow"];
			if ($newRow % $step == 0) {
				return $newRow + $step;
			}
			else {
				$modRow = $step - ($newRow % $step);

				return $newRow + $step + $modRow;
			}
		}
		else {
			return 0;
		}
	}

	protected function close() {
		return self::$conn->close();
	}

	public function getLastQuery() {
		return self::$lastQuery;
	}

	public function getTiming() {
		self::$timing['total'] = round((microtime(TRUE) - self::$beginTime), 5);

		return self::$timing;
	}

	public function __destruct() {
		self::close();
	}
}