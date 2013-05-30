<?php

/**
 * liOfc class.
 *
 * Provides functions to create charts based on PHP Ofc Object
 *
 * @package    liOfcPlugin
 * @author     Baptiste SIMON <baptiste.simon@e-glop.net>
 */

/**
 * Plugin
 */

//include_once sfConfig::get('li_ofc_object');
require_once 'open-flash-chart/open-flash-chart-object.php';

class liOfc
{
	/**
	 * Creates HTML String using given parameters.
	 *
	 * @author Dawood RASHID
	 * @since 25 mars 2009
	 *
	 * @param String $width
	 * @param String $height
	 * @param String $url
	 * @param Boolean $use_swfobject
	 * @param String $base
	 * @return String HTML as a string
	 */
	public static function createChartToString($width, $height, $url, $useSwfObject = true, $base = '', $message = '')
	{
	  if ( !$base )
  		$base = self::getBaseDir();
		$base = self::getBaseDir();

		return _ofc($width, $height, $url, $useSwfObject, $base, $message);
	}

	/**
	 * Creates chart using given parameters.
	 *
	 * @author Dawood RASHID
	 * @since 25 mars 2009
	 *
	 * @param String $width
	 * @param String $height
	 * @param String $url
	 * @param Boolean $use_swfobject
	 * @param String $base
	 * @return stream the HTML into the page
	 */
	public static function createChart($width, $height, $url, $useSwfObject = true, $base = '', $message = '')
	{
	  if ( !$base )
  		$base = self::getBaseDir();
		return _ofc( $width, $height, $url, $useSwfObject, $base, $message );
	}

	/**
	 * Get base URL dir for plugin.
	 *
	 * @return String
	 */
	protected static function getBaseDir()
	{
		return public_path('liOfcPlugin/images/');
	}
}
