<?php

namespace Site\Systems;

class Redirects {
	use \Core\Traits\TStaticClass;
	
	public static function Check($app, $loc, $settings) {
		$req = $app->Request;
		$redir = \Site\Data\Redirect::FindForUrl($app->UrlToRelative($req->Url));
		
		if( !isset($redir) )
			return null;
		
		if( !isset($redir->to_url) )
			return null;
		
		return $redir->to_url;
	}
}