<?php

declare(strict_types=1);

namespace Site\Systems;

class Email {
	public static function Send($to, $from, $subject, $message, $tags = []) {
		$data = [
			'html' => $message,
			'subject' => $subject,
			'from_email' => $from,
			'to' => [
				[ 'email' => $to ]
			],
			'preserve_recipients' => false,
			'tags' => $tags
		];

		try {
			$mandrill = new \Mandrill\Manager(\Core\Application::Get()->Config->Mandrill->api_key);
			$result = $mandrill->messages->send($data, true);
		} catch( \Mandrill\Error $e ) {
			return false;
		}

		return true;
	}

	public static function SendTemplate($to, $from, $subject, $tmpl, $template_vars = [], $tags = []) {
		$tv = [];
		foreach( $template_vars as $n => $v ) {
			$tv[] = ['name' => $n, 'content' => $v];
		}

		$data = [
			'subject' => $subject,
			'from_email' => $from,
			'to' => [
				[ 'email' => $to ]
			],
			'preserve_recipients' => false,
			'tags' => $tags,
			'global_merge_vars' => $tv
		];

		try {
			$mandrill = new \Mandrill\Manager(\Core\Application::Get()->Config->Mandrill->api_key);
			$result = $mandrill->messages->sendTemplate($tmpl, $tv, $data, true);
		} catch( \Mandrill\Error $e ) {
			return false;
		}

		return true;
	}

	public static function SendSimpleTemplate($to, $from, $subject, $tmpl, $data = [], $tags = []) {
		$mand_data = [
			'html' => $message,
			'subject' => $subject,
			'from_email' => $from,
			'to' => [
				[ 'email' => $to ]
			],
			'preserve_recipients' => false,
			'tags' => $tags
		];

		try {
			$mandrill = new \Mandrill\Manager(\Core\Application::Get()->Config->Mandrill->api_key);
			$result = $mandrill->messages->send($mand_data, true);
		} catch( \Mandrill\Error $e ) {
			return false;
		}

		return true;
	}

	public static function GetTemplates() {
		$ret = false;
		try {
			$mandrill = new \Mandrill\Manager(\Core\Application::Get()->Config->Mandrill->api_key);
			$ret = $mandrill->templates->getList();
		} catch( \Mandrill\Error $e ) {
			$ret = false;
		}

		return $ret;
	}
}