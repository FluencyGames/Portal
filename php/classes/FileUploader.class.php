<?php
	/**
	 * FileUploader class uploads and verifies files
	 *
	 * @copyright	  Copyright (c) indie(Function); LLC. (http://indiefunction.com)
	 * @since		  Indie Burst (tm) Alpha v 0.3
	 */
	class FileUploader
	{
		/**
		 * A variable to hold the uploaded file array
		 *
		 * @var array $file
		 */
		private $file;
		
		/**
		 * The directory to upload the file
		 *
		 * @var string $directory
		 */
		private $directory;
		
		/**
		 * The new filename, excluding the extension
		 *
		 * @var bool $newFilename
		 */
		private $newFilename;
		
		/**
		 * The extension of the file uploaded
		 *
		 * @var bool $extension
		 */
		private $extension;
		
		/**
		 * Checks to see if the file uploaded obeys all restrictions
		 *
		 * @param string $name The name of the DOM element containing the file
		 * @param string $type The restrictions of the upload
		 */
		public function __construct($name, $type = 'image_5120')
		{
			$this->file = ((isset($_FILES[$name])) ? $_FILES[$name] : null);
			$this->file['error'] = null;
			if ($this->file)
			{
				$name_explode = explode('.', $this->file['name']);
				$this->extension = end($name_explode);
			}
			else
			{
				$this->file = array();
				$this->file['error'] = 'Invalid file';
			}
		}
		
		/**
		 * Checks to see if the file is valid
		 *
		 * @return bool
		 */
		public function isValidFile()
		{
			return $this->file != null;
		}
		
		/**
		 * Sets the directory the file will be uploaded to
		 *
		 * @param $dir The directory
		 */
		public function setDirectory($dir)
		{
			$this->directory = $dir;
		}
		
		/**
		 * The filename to use on the server for the uploaded file
		 *
		 * @return $filename The filename
		 */
		public function setFilename($filename = null)
		{
			$this->newFilename = $filename;
		}
		
		/**
		 * Returns the filename with or without an extension
		 *
		 * @param bool $ext
		 *
		 * @return string The filename
		 */
		public function getFilename($ext = true)
		{
			if ($this->newFilename != null)
				return ($ext) ? $this->newFilename . '.' . $this->extension : $this->newFilename;
			return null;
		}
		
		/**
		 * Checks to see if the filename has been set. If not, it creates a random
		 * filename based on the time
		 */
		private function checkFilename()
		{
			if ($this->newFilename == null)
			{
				// Create a random filename if the filename was not given
				$filename = time();
				$this->newFilename = substr(md5(microtime()), rand(0, 26), 1) . substr($filename, 0, 2) . substr(md5(microtime()), rand(0, 26), 1) . substr($filename, 2, 2) . substr(md5(microtime()), rand(0, 26), 1) . substr($filename, 4, 2) . substr(md5(microtime()), rand(0, 26), 1) . substr($filename, 6, 2) . substr(md5(microtime()), rand(0, 26), 1) . substr($filename, 8, 2);
			}
		}
		
		/**
		 * Getter function for $this->file['error']
		 * 
		 * @return array The current error(s)
		 */
		public function errors()
		{
			return $this->file['error'];
		}
		
		/**
		 * Finishes uploading the file, assigning it the proper name and moving
		 * it to its new directory
		 */
		public function upload()
		{
			$this->checkFilename();
			
			if ($this->directory == null)
			{
				$this->file['error'] = 'Directory not given (Please contact support)';
				return;
			}
			
			if (!move_uploaded_file($this->file['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . Config::get('documentroot') . $this->directory . $this->newFilename . '.' . $this->extension))
				$this->file['error'] = 'File upload failed';
		}
	}
?>