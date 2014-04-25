<?php

namespace WP
{
	class Theme extends \System\Object
	{
		public static function CurrentTheme()
		{
			return new Theme(wp_get_theme());
		}

		public static function InstalledThemes($errors = false, $allowed = null, $blog_id = 0)
		{
			$themes = wp_get_themes(array(
				'errors' => $errors,
				'allowed' => $allowed,
				'blog_id' => $blog_id
			));
			
			$ret = array();
			
			foreach( $themes as $t )
				$ret[] = new Theme($t);
				
			return $ret;
		}
		
		protected $internal_theme_object;
		
		public function _getInternalThemeObject() { return $this->internal_theme_object; }
		
		public function _getName() { return $this->internal_theme_object->get('Name'); }
		public function _getThemeURI() { return $this->internal_theme_object->get_theme_root_uri(); }
		public function _getDescription() { return $this->internal_theme_object->display('Description'); }
		public function _getAuthor() { return $this->internal_theme_object->display('Author'); }
		public function _getAuthorURI() { return $this->internal_theme_object->display('AuthorURI'); }
		public function _getVersion() { return $this->internal_theme_object->get('Version'); }
		public function _getTemplate() { return $this->internal_theme_object->get_template(); }
		public function _getStatus() { return $this->internal_theme_object->get('Status'); }
		public function _getTags() { return $this->internal_theme_object->get('Tags'); }
		public function _getTextDomain() { return $this->internal_theme_object->get('TextDomain'); }
		public function _getDomainPath() { return $this->internal_theme_object->get('DomainPath'); }
		
		public function _getParent()
		{
			$parent = $this->internal_theme_object->parent();
			
			if( $parent === null )
				$parent = null;
				
			return $parent;
		}
		
		public function __construct($wp_theme)
		{
			$this->internal_theme_object = $wp_theme;
		}
		
		public function HasErrors()
		{
			return $this->internal_theme_object->errors() !== false;
		}
		
		public function GetErrors()
		{
			$ret = $this->internal_theme_object->errors();
			
			if( $ret === false )
				$ret = array();
				
			return $ret;
		}
		
		public function Exists()
		{
			return $this->internal_theme_object->exists();
		}
		
		public function DeleteCache()
		{
			$this->internal_theme_object->cache_delete();
		}
		
		public function GetHeader($name)
		{
			return $this->internal_theme_object->get($name);
		}
		
		public function GetHeaderForDisplay($name, $markup = true, $translate = true)
		{
			return $this->internal_theme_object->display($name, $markup, $translate);
		}
	}
}