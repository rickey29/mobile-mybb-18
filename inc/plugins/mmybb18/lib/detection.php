<?php
/*
	project: Mobile MyBB 1.8 (MMyBB18)
	file:    MYBB_ROOT/inc/plugins/mmybb18/lib/detection.php
	version: 0.1.0
	author:  Rickey Gu
	web:     http://flexplat.com
	email:   rickey29@gmail.com
*/

if ( !defined("IN_MYBB") )
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}


function m_get_device($data)
{
	$device_list = array(
		// Apple iOS
		'iPad' => 'tablet',
		'iPhone' => 'smartphone',
		'iPod' => 'smartphone',

		// Kindle Fire
		'Kindle Fire' => 'tablet',
		'Kindle/' => 'tablet',

		// Android
		'Android*Mobile' => 'smartphone',
		'Android' => 'tablet',

		// Chrome
		'Chrome/' => 'desktop',

		// Macintosh
		'Macintosh' => 'desktop',

		// Firefox
		'Firefox/' => 'desktop',

		// Windows Phone
		'Windows Phone' => 'smartphone',

		// Windows Mobile
		'Windows CE' => 'feature-phone',

		// Internet Explorer
		'MSIE ' => 'desktop',

		// Opera Mobile
		'Opera Mobi*Version/' => 'smartphone',

		// Opera Mini
		'Opera Mini/' => 'smartphone',

		// Opera
		'Opera*Version/' => 'desktop',

		// Palm WebOS
		'webOS/*AppleWebKit' => 'smartphone',
		'TouchPad/' => 'tablet',

		// Meego
		'MeeGo' => 'smartphone',

		// BlackBerry
		'BlackBerry*AppleWebKit*Version/' => 'smartphone',
		'PlayBook*AppleWebKit' => 'tablet',
		'BlackBerry*/*MIDP' => 'feature-phone',

		// Safari
		'Safari' => 'desktop',

		// Nokia Symbian
		'Symbian/' => 'smartphone',

		// Google
		'googlebot-mobile' => 'mobile-bot',
		'googlebot' => 'bot',

		// Microsoft
		'bingbot' => 'bot',

		// Yahoo!
		'Yahoo! Slurp' => 'bot'
	);

	$accept_list = array(
		// application/vnd.wap.xhtml+xml
		'application/vnd.wap.xhtml+xml' => 'feature-phone'
	);


	if ( !empty($data['user_agent']) )
	{
		foreach ( $device_list as $key => $value )
		{
			if ( preg_match('#' . str_replace('\*', '.*?', preg_quote($key, '#')) . '#i', $data['user_agent']) )
			{
				return $value;
			}
		}
	}

	if ( !empty($data['accept']) )
	{
		foreach ( $accept_list as $key => $value )
		{
			if ( preg_match('#' . str_replace('\*', '.*?', preg_quote($key, '#')) . '#i', $data['accept']) )
			{
				return $value;
			}
		}
	}

	if ( !empty($data['profile']) )
	{
		return 'feature-phone';
	}

	if ( !empty($data['user_agent']) )
	{
		return 'feature-phone';
	}

	return 'desktop';
}
?>