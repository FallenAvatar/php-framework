<?php declare(strict_types=1);

namespace Site\Systems;

class Recaptcha {
	public static function Verify($response, $client_ip = null) {
		$App = \Core\Application::Get();
		if( !isset($client_ip) )
			$client_ip = $_SERVER['REMOTE_ADDR'];

		$ret = \Google\Recaptcha::Verify( $App->Config->Recaptcha->secret_key, $response, $client_ip);

		if( !isset($ret['error-codes']) || !is_array($ret['error-codes']) )
			$ret['error-codes'] = [];

		if( $ret['hostname'] != $_SERVER['SERVER_NAME'] && $ret['hostname'] != 'www.'.$_SERVER['SERVER_NAME'] ) {
			// TODO: look for locations by hostname
			$ret['success'] = false;
			$ret['error-codes'][] = 'bad-hostname';
		}

		$error_codes = [
			'missing-input-secret' => 'The secret parameter is missing.',
			'invalid-input-secret' => 'The secret parameter is invalid or malformed.',
			'missing-input-response' => 'The response parameter is missing.',
			'invalid-input-response' => 'The response parameter is invalid or malformed.',
			'bad-request' => 'The request is invalid or malformed.',
			'bad-hostname' => 'The hostname is invalid.'
		];
		$ecs = [];
		foreach( $ret['error-codes'] as $ec )
			$ecs[$ec] = $error_codes[$ec];

		$ret['error-codes'] = $ecs;

		return $ret;
	}

	public static function GetClientSideDetails($id = null, $classes = null) {
		$App = \Core\Application::Get();

		return [
			'script' => \Google\Recaptcha::GetScriptUrl(),
			'html' => \Google\Recaptcha::GetHTML( $App->Config->Recaptcha->site_key, $id, $classes )
		];
	}
}