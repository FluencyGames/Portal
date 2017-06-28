<?php
	require_once(__DIR__ . "/Config.class.php");
	
	class Database {
		private static $_instance = null;
		
		private $_pdo = null,
				$_query,
				$_result,
				$_error = false;
		
		private function __construct() {
			try {
				$this->_pdo = new PDO('mysql:host=' . Config::get('mysql/host') . ';dbname=' . Config::get('mysql/db'), Config::get('mysql/username'), Config::get('mysql/password'));
			} catch(PDOException $e) {
				die($e->getMessage());
			}
		}
		
		public static function getInstance() {
			if (self::$_instance == null)
				self::$_instance = new Database();
			return self::$_instance;
		}
		
		public function beginTransaction() {
			$this->_pdo->beginTransaction();
		}
		
		public function rollback() {
			$this->_pdo->rollback();
		}
		
		public function commit() {
			$this->_pdo->commit();
		}
		
		public function query($query, $params = array()) {
			$this->_error = false;

			if ($this->_query = $this->_pdo->prepare($query)) {
				$i = 0;
				if (count($params) > 0) {
					foreach ($params as $param)
						$this->_query->bindValue(++$i, $param);
				}
				
				if ($this->_query->execute()) {
					$this->_result = $this->_query->fetchAll(PDO::FETCH_ASSOC);
					$this->_count = $this->_query->rowCount();
				}
				else {
					$this->_error = true;
				}
			}
			
			return $this;
		}
		
		public function result() {
			return $this->_result;
		}
		
		public function firstResult() {
			if (count($this->_result) > 0)
				return $this->_result[0];
			return null;
		}
		
		public function count() {
			return $this->_count;
		}
		
		public function error() {
			return $this->_error;
		}
		
		public function errorInfo() {
		    return $this->_query->errorInfo();
		}
	}
?>