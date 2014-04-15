<?php
/**
 * Copyright (c) 2011 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the COPYING file distributed with this package.
 *
 * @category   WURFL
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * Manages the creation and instatiation of all User Agent Handlers and Normalizers and provides a factory for creating User Agent Handler Chains
 * @package    WURFL
 * @see WURFL_UserAgentHandlerChain
 */
namespace WURFL
{
class UserAgentHandlerChainFactory {

	/**
	 * @var WURFL_UserAgentHandlerChain
	 */
	private static $_userAgentHandlerChain = null;

	/**
	 * Create a WURFL_UserAgentHandlerChain from the given $context
	 * @param WURFL_Context $context
	 * @return WURFL_UserAgentHandlerChain
	 */
	public static function createFrom(\WURFL\Context $context) {
		self::init($context);
		return self::$_userAgentHandlerChain;
	}

	/**
	 * Initializes the factory with an instance of all possible WURFL_Handlers_Handler objects from the given $context
	 * @param WURFL_Context $context
	 */
	static private function init(\WURFL\Context $context) {

		self::$_userAgentHandlerChain = new \WURFL\UserAgentHandlerChain();

		$genericNormalizers = self::createGenericNormalizers();


		$chromeNormalizer = $genericNormalizers->addUserAgentNormalizer(new \WURFL\Request\UserAgentNormalizer\Specific\Chrome ());
		$konquerorNormalizer = $genericNormalizers->addUserAgentNormalizer(new \WURFL\Request\UserAgentNormalizer\Specific\Konqueror ());
		$safariNormalizer = $genericNormalizers->addUserAgentNormalizer(new \WURFL\Request\UserAgentNormalizer\Specific\Safari ());
		$firefoxNormalizer = $genericNormalizers->addUserAgentNormalizer(new \WURFL\Request\UserAgentNormalizer\Specific\Firefox ());
		$msieNormalizer = $genericNormalizers->addUserAgentNormalizer(new \WURFL\Request\UserAgentNormalizer\Specific\MSIE ());

		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\NokiaHandler($context, $genericNormalizers));
		$lguplusNormalizer = $genericNormalizers->addUserAgentNormalizer(new \WURFL\Request\UserAgentNormalizer\Specific\LGUPLUSNormalizer());
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\LGUPLUSHandler($context, $genericNormalizers));

		$androidNormalizer = $genericNormalizers->addUserAgentNormalizer(new \WURFL\Request\UserAgentNormalizer\Specific\Android());
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\AndroidHandler($context, $androidNormalizer));

		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\SonyEricssonHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\MotorolaHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\BlackBerryHandler($context, $genericNormalizers));

		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\SiemensHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\SagemHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\SamsungHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\PanasonicHandler($context, $genericNormalizers));

		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\NecHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\QtekHandler($context, $genericNormalizers));

		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\MitsubishiHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\PhilipsHandler($context, $genericNormalizers));
		$lgNormalizer = $genericNormalizers->addUserAgentNormalizer(new \WURFL\Request\UserAgentNormalizer\Specific\LGNormalizer());
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\LGHandler($context, $lgNormalizer));
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\AppleHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\KyoceraHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\AlcatelHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\SharpHandler($context, $genericNormalizers));

		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\SanyoHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\BenQHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\PantechHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\ToshibaHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\GrundigHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\HTCHandler($context, $genericNormalizers));

		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\VodafoneHandler($context, $genericNormalizers));


		// BOT
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\BotCrawlerTranscoderHandler($context, $genericNormalizers));


		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\SPVHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\WindowsCEHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\PortalmmmHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\DoCoMoHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\KDDIHandler($context, $genericNormalizers));

		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\OperaMiniHandler($context, $genericNormalizers));
		$maemoNormalizer = $genericNormalizers->addUserAgentNormalizer(new \WURFL\Request\UserAgentNormalizer\Specific\Maemo());
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\MaemoBrowserHandler($context, $maemoNormalizer));


		// Web Browsers handlers
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\ChromeHandler($context, $chromeNormalizer));
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\AOLHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\OperaHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\KonquerorHandler($context, $konquerorNormalizer));
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\SafariHandler($context, $safariNormalizer));
		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\FirefoxHandler($context, $firefoxNormalizer));

		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\MSIEHandler($context, $msieNormalizer));

		self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\CatchAllHandler($context, $genericNormalizers));

	}

	/**
	 * Returns an array of all possible User Agent Normalizers
	 * @return array Array of WURFL_Request_UserAgentNormalizer objects
	 */
	private static function createGenericNormalizers() {
		return new \WURFL\Request\UserAgentNormalizer(
			array(
				new \WURFL\Request\UserAgentNormalizer\Generic\UPLink(),
				new \WURFL\Request\UserAgentNormalizer\Generic\BlackBerry(),
				new \WURFL\Request\UserAgentNormalizer\Generic\YesWAP(),
				new \WURFL\Request\UserAgentNormalizer\Generic\BabelFish(),
				new \WURFL\Request\UserAgentNormalizer\Generic\SerialNumbers(),
				new \WURFL\Request\UserAgentNormalizer\Generic\NovarraGoogleTranslator(),
				new \WURFL\Request\UserAgentNormalizer\Generic\LocaleRemover()
				)
			);
	}


}
}