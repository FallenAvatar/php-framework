<?php

namespace System\Web
{
	class HttpPostedFile extends \System\Object
	{
		protected $contentLength;
		public function _getContentLength() { return $this->contentLength; }
		
		protected $contentType;
		public function _getContentType() { return $this->contentType; }
		
		protected $fileName;
		public function _getFileName() { return $this->fileName; }
		
		protected $tempName;
		public function _getTempFileName() { return $this->tempName; }
		
		protected $error;
		public function _getIsError() { return $this->error; }
		
		public function __construct($file_info)
		{
			$this->contentLength = intval($file_info['size']);
			$this->contentType = $file_info['type'];
			$this->fileName = $file_info['name'];
			$this->tempName = $file_info['tmp_name'];
			$this->error = $file_info['error'] !== 0;
		}
		
		public function SaveAs($filename)
		{
			move_uploaded_file($this->tempName, $filename);
		}
	}
}