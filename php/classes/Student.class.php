<?php
	require_once(__DIR__ . '/Config.class.php');
	require_once(__DIR__ . '/Database.class.php');
	require_once(__DIR__ . '/User.class.php');
	require_once(__DIR__ . '/../fg/license.php');
	
	class Student {
		private $_data = array();
		private $_result = array();
		private $_valid = false;
		private static $_encrypt = true;
		private $_username = "";
		
		public function __construct($id, $product, $columns = '*') {
			$query = "SELECT {$columns} FROM " . T_GAMEDATA . " WHERE (Username = ? AND Product = ?)";
			$data = array($id, $product);
			
			$result = null;
			$this->_result = Database::getInstance()->query($query, $data)->result();
			if (count($this->_result) > 0) $result = $this->_result[0];
			
			if ($result != null) {
				$this->_data = $result;
				$this->_valid = true;
				$this->_username = User::decode($result['Username']);
			}
		}

		public function setCurrentStudent() {
			$this->setCookies($this->_data['Username'], $this->_data['Product']);	
		}
				
		public static function getCurrentStudent() {
			if ((isset($_COOKIE['fluency_games_student'])) && (isset($_COOKIE['fluency_games_product'])))
				return new Student($_COOKIE['fluency_games_student'], $_COOKIE['fluency_games_product']);
			return new Student(-1, 0, '*');
		}
		
		public static function setCookies($id, $product) {
			$expire = time() + 60 * 60; // 1 hr
			setcookie('fluency_games_student', $id, $expire, '/');
			setcookie('fluency_games_product', $product, $expire, '/');
			return true;
		}
		
		public static function getCookie($cookie) {
			return (isset($_COOKIE['fluency_games_' . $cookie])) ? $_COOKIE['fluency_games_' . $cookie] : '';
		}
		
		public static function logout() {
			setcookie('fluency_games_student', '', time() - 3600, '/');
			setcookie('fluency_games_product', '', time() - 3600, '/');
		}
		
		public static function isLoaded() {
			return ((isset($_COOKIE['fluency_games_student'])) && (isset($_COOKIE['fluency_games_product'])));
		}
		
		public function isValid() {
			return $this->_valid;
		}
		
		public static function decodeData($dataToDecode) {
			$dataEncoded = null;
			//$data = json_encode("");
			
			$dataEncoded = $dataToDecode;
			$data = trim( base64_decode($dataEncoded) );
            // handle legacy data for now
            if(empty($data)&&!empty($dataEncoded))
                $data = base64_encode($dataEncoded);
            
			return $data;
		}
		
		public function getGameData() {
			if (!$this->_valid)
				return json_encode("");
			return $this->decodeData($this->_data['TrackingData']);
		}

		public function getProgressData() {
			if (!$this->_valid)
				return json_encode("");
			return $this->decodeData($this->_data['ProgressData']);
		}		
		
		public function getCurrentProduct() {
			if(isset($_COOKIE['fluency_games_product']))
				return $_COOKIE['fluency_games_product'];
			else
				return 0;
		}
		
		public function getColumn($col) {
			return $this->_data[$col];
		}
		
		public function getDisplayUsername() {
			return User::decode($this->_username);
		}

		public function getFirstName() {
			return User::decode($this->_data['Fname']);
		}

		public function getLastName() {
			return User::decode($this->_data['Lname']);
		}
		
		public function getDisplayName() {
			$lname = $this->getLastName();
			$fname = $this->getFirstName();
			$name = '';
			if(!empty($lname))	$name = $lname;
			if(!empty($fname))	{
				if(!empty($name)) $name = $name . ', ';
				$name .= $fname;
			}
			return $name;
		}
				
		public function getNumMedals() {
			return 0;
		}
	}	
?>

		