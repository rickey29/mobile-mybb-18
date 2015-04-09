<?php
/*
	project: Mobile MyBB 1.8 (MMyBB18)
	file:    MYBB_ROOT/mobile/lib/detection.php
	version: 0.0.0
	author:  Rickey Gu
	web:     http://flexplat.com
	email:   rickey29@gmail.com
*/

if ( !defined("IN_MYBB") )
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}


function m_get_detection($data)
{
	$device_list = array(
		// Apple iOS
		'iPad' => array(
			'Apple iPad',
			'jQuery Mobile A-grade',
			'tablet'
		),
		'iPhone' => array(
			'Apple iPhone',
			'jQuery Mobile A-grade',
			'smartphone'
		),
		'iPod' => array(
			'Apple iPod',
			'jQuery Mobile A-grade',
			'smartphone'
		),

		// Kindle Fire
		'Kindle Fire' => array(
			'Kindle Fire',
			'jQuery Mobile A-grade',
			'tablet'
		),
		'Kindle/' => array(
			'Kindle',
			'jQuery Mobile A-grade',
			'tablet'
		),

		// Android
		'Android*Mobile' => array(
			'Android',
			'jQuery Mobile A-grade',
			'smartphone'
		),
		'Android' => array(
			'Android Honeycomb',
			'jQuery Mobile A-grade',
			'tablet'
		),

		// Chrome
		'Chrome/' => array(
			'Chrome',
			'jQuery Mobile A-grade',
			'desktop'
		),

		// Macintosh
		'Macintosh' => array(
			'Macintosh',
			'jQuery Mobile A-grade',
			'desktop'
		),

		// Firefox
		'Firefox/' => array(
			'Firefox',
			'jQuery Mobile A-grade',
			'desktop'
		),

		// Windows Phone
		'Windows Phone' => array(
			'Windows Phone',
			'jQuery Mobile A-grade',
			'smartphone'
		),

		// Windows Mobile
		'Windows CE' => array(
			'Windows Mobile',
			'jQuery Mobile C-grade',
			'feature_phone'
		),

		// Internet Explorer
		'MSIE ' => array(
			'Internet Explorer',
			'jQuery Mobile A-grade',
			'desktop'
		),

		// Opera Mobile
		'Opera Mobi*Version/' => array(
			'Opera Mobile',
			'jQuery Mobile A-grade',
			'smartphone'
		),

		// Opera Mini
		'Opera Mini/' => array(
			'Opera Mini',
			'jQuery Mobile B-grade',
			'feature_phone'
		),

		// Opera
		'Opera*Version/' => array(
			'Opera',
			'jQuery Mobile A-grade',
			'desktop'
		),

		// Palm WebOS
		'webOS/*AppleWebKit' => array(
			'Palm WebOS',
			'jQuery Mobile A-grade',
			'smartphone'
		),
		'TouchPad/' => array(
			'Palm WebOS Pad',
			'jQuery Mobile A-grade',
			'tablet'
		),

		// Meego
		'MeeGo' => array(
			'Meego',
			'jQuery Mobile A-grade',
			'smartphone'
		),

		// BlackBerry
		'BlackBerry*AppleWebKit*Version/' => array(
			'BlackBerry Smartphone',
			'jQuery Mobile A-grade',
			'smartphone'
		),
		'PlayBook*AppleWebKit' => array(
			'BlackBerry Playbook',
			'jQuery Mobile A-grade',
			'tablet'
		),
		'BlackBerry*/*MIDP' => array(
			'BlackBerry Feature Phone',
			'jQuery Mobile C-grade',
			'feature_phone'
		),

		// Safari
		'Safari' => array(
			'Safari',
			'jQuery Mobile A-grade',
			'desktop'
		),

		// Nokia Symbian
		'Symbian/' => array(
			'Nokia Symbian',
			'jQuery Mobile B-grade',
			'smartphone'
		),

		// Google
		'googlebot-mobile' => array(
			'Google Mobile Bot',
			'jQuery Mobile A-grade',
			'mobile-bot'
		),
		'googlebot' => array(
			'Google Bot',
			'',
			'bot'
		),

		// Microsoft
		'bingbot' => array(
			'Microsoft Bing',
			'',
			'bot'
		),

		// Yahoo!
		'Yahoo! Slurp' => array(
			'Yahoo! Slurp',
			'',
			'bot'
		)
	);

	// application/vnd.wap.xhtml+xml
	$accept_list = array(
		'application/vnd.wap.xhtml+xml' => array(
			'unrecognized',
			'jQuery Mobile C-grade',
			'feature-phone'
		)
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
		return array('unrecognized', 'jQuery Mobile C-grade', 'feature-phone');
	}

	if ( empty($data['user_agent']) )
	{
		return array('', '', 'desktop');
	}

	return array('unrecognized', 'unrecognized', 'unrecognized');
}

function m_get_redirection($data)
{
	list($device_platform, $device_grade, $device_system) = m_get_detection($data);
	if ( $device_system == 'desktop' || $device_system == 'bot' )
	{
		return array($device_platform, $device_grade, $device_system, 'desktop', '');
	}
	else if ( $device_system == 'mobile-bot' )
	{
		return array($device_platform, $device_grade, $device_system, 'mobile', '');
	}

	if ( !empty($data['redirection']) )
	{
		if ( $data['redirection'] == 'mobile' )
		{
			return array($device_platform, $device_grade, $device_system, 'mobile', 'mobile');
		}
		else if ( $data['redirection'] == 'desktop' )
		{
			return array($device_platform, $device_grade, $device_system, 'desktop', 'desktop');
		}
	}

	if ( !empty($data['cookie']) )
	{
		if ( $data['cookie'] == 'mobile' )
		{
			return array($device_platform, $device_grade, $device_system, 'mobile', 'mobile');
		}
		else if ( $data['cookie'] == 'desktop' )
		{
			return array($device_platform, $device_grade, $device_system, 'desktop', 'desktop');
		}
	}

	return array($device_platform, $device_grade, $device_system, 'mobile', '');
}
?>
