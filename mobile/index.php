<?php
/*
	project: Mobile MyBB 1.8 (MMyBB18)
	file:    MYBB_ROOT/mobile/index.php
	version: 0.0.0
	author:  Rickey Gu
	web:     http://flexplat.com
	email:   rickey29@gmail.com
*/

if ( !defined("IN_MYBB") )
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}


// detection library
require(MYBB_ROOT . '/mobile/lib/detection.php');


global $mybb;

$m_data = array();
$m_data['user_agent'] = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$m_data['accept'] = !empty($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
$m_data['profile'] = !empty($_SERVER['HTTP_PROFILE']) ? $_SERVER['HTTP_PROFILE'] : '';
$m_data['redirection'] = !empty($mybb->input['m-redirection']) ? $mybb->input['m-redirection'] : '';
$m_data['cookie'] = !empty($mybb->cookies['mybb[m_redirection]']) ? $mybb->cookies['mybb[m_redirection]'] : '';

$m_value = m_get_redirection($m_data);

$m_response = array();
$m_response['device_platform'] = $m_value[0];
$m_response['device_grade'] = $m_value[1];
$m_response['device_system'] = $m_value[2];
$m_response['echo_page'] = $m_value[3];
$m_response['set_cookie'] = $m_value[4];


if ( !empty($m_response['set_cookie']) )
{
	if ( $m_response['set_cookie'] == 'mobile' )
	{
		// make the cookie expires in a years time: 60 * 60 * 24 * 365 = 31,536,000
		my_setcookie('mybb[m_redirection]', 'mobile');
	}
	else if ( $m_response['set_cookie'] == 'desktop' )
	{
		// make the cookie expires in a years time: 60 * 60 * 24 * 365 = 31,536,000
		my_setcookie('mybb[m_redirection]', 'desktop');
	}
}

if ( !empty($m_response['echo_page']) && $m_response['echo_page'] == 'mobile' )
{
	if ( $m_response['device_system'] == 'smartphone' )
	{
		define('MMYBB18', 'jQuery-Mobile Smartphone');
	}
	else if ( $m_response['device_system'] == 'tablet' )
	{
		define('MMYBB18', 'jQuery-Mobile Tablet');
	}
	else
	{
		define('MMYBB18', 'Feature Phone');
	}

	$loadstyle = "name='" . $db->escape_string(MMYBB18) . "'";

	// style library
	include(MYBB_ROOT . '/mobile/lib/style.php');
}
?>
