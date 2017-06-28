<?php
	require_once(__DIR__ . '/Config.class.php');
	require_once(__DIR__ . '/Database.class.php');
	require_once(__DIR__ . '/../services/PHPMailer/PHPMailerAutoload.php');
	
	class Emailer {
		private static $_instance = null;
		private $_mailer = null;
		private $_error = null;
		
		protected function __construct() {
			// Set up email settings
			$this->_mailer = new PHPMailer();
			$this->_mailer->IsHTML(true);
			$this->_mailer->IsSMTP();
			$this->_mailer->SMTPAuth = Config::get('email/auth');
			if ($this->_mailer->SMTPAuth) {
				$this->_mailer->SMTPSecure	= 'ssl'; // Look into TLS vs SSL
				$this->_mailer->Host		= Config::get('email/host');
				$this->_mailer->Port		= Config::get('email/port');
			}
			$this->setDebug(true);
		}
		
		public static function getInstance() {
			if (self::$_instance == null)
				self::$_instance = new self;
			return self::$_instance;
		}
		
		public function setDebug($debug) {
			if ($debug === false)	$debug = 0;
			if ($debug === true)	$debug = 2;
			$this->_mailer->SMTPDebug = $debug;
		}
		
		public function setFrom($name='support') {
			$from = Config::get('email/' . $name);             // changed email structure 4-11-16 mse
			
			if ($this->_mailer->SMTPAuth) {
				$this->_mailer->Username = Config::get('email/smtp-user');
				$this->_mailer->Password = Config::get('email/smtp-password');
			}
			
			$address = $from['address'];
			$this->_mailer->SetFrom($from['address'], $name);
			$this->_mailer->AddReplyTo($from['address'], $name); // Do we need this?
		}
		
		// $to - An array containing 
		public function send($emailType, $to, $data = array()) {
			$this->error = null;
			
			// Get the email contents
			$directory = Config::get('domain') . Config::get('documentroot') . 'media/email/';
			$contents = file_get_contents($directory . $emailType . '.html', FILE_USE_INCLUDE_PATH);
			
			// Parse the email contents
			$dom = new DOMDocument();
			libxml_use_internal_errors(true); // Hide loadHTML errors
			$dom->loadHTML($contents);
			
			$subject = $dom->getElementsByTagName('subject')->item(0)->textContent;
			$from = $dom->getElementsByTagName('from')->item(0)->textContent;
			
			$email = $dom->getElementsByTagName('email')->item(0);
			$message = '';
			foreach ($email->childNodes as $node) {
				$message .= $dom->saveHTML($node);
			}
			
			// Set tags to their designated values
			$data['domain'] = Config::get('domain') . Config::get('documentroot');
			foreach ($data as $key => $value) {
				$message = str_replace("{{$key}}", $value, $message);
				$message = str_replace("%7B{$key}%7D", $value, $message);
			}
			
			// Set to, from, subject, and message values
			foreach ($to as $address => $emailTo) {
				//$this->_mailer->AddAddress($emailTo['address'], $emailTo['name']);
				$this->_mailer->AddAddress($emailTo['address']);
			}
			$this->setFrom($from);
			
			$this->_mailer->Subject = $subject;
			$this->_mailer->MsgHTML($message); // MsgHTML should create AltHTML automatically
			// Send that email! Whoo!
			return $this->_mailer->Send();
		}
		
		public function send_message($to, $subject, $from, $message) {
			$this->error = null;
			
			// Set to, from, subject, and message values
			foreach ($to as $address => $email) {
				$this->_mailer->AddAddress($email['address'], $email['name']);
			}

			$this->setFrom($from);
			$this->_mailer->Subject = $subject;
			$this->_mailer->MsgHTML($message); // MsgHTML should create AltHTML automatically

			// Send that email! Whoo!
			return $this->_mailer->Send();
		}
		
		// TODO: This
		public function error() {
			//return $this->_error;
			return $this->_mailer->ErrorInfo;
		}
	}
?>