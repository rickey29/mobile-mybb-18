<?php
/*
	project: Mobile MyBB 1.8 (MMyBB18)
	file:    MYBB_ROOT/inc/plugins/mmybb18.php
	version: 1.7.0
	author:  Rickey Gu
	web:     http://flexplat.com
	email:   rickey29@gmail.com
*/

// Disallow direct access to this file for security reasons
if ( !defined("IN_MYBB") )
{
	die("Direct initialization of this file is not allowed.");
}


$plugins->add_hook('error', 'mmybb18_error');
$plugins->add_hook('global_intermediate', 'mmybb18_global_intermediate');
$plugins->add_hook('global_start', 'mmybb18_global_start');
$plugins->add_hook('index_end', 'mmybb18_index_end');
$plugins->add_hook('member_login_end', 'mmybb18_member_login_end');
$plugins->add_hook('member_profile_end', 'mmybb18_member_profile_end');
$plugins->add_hook('member_register_end', 'mmybb18_member_register_end');
$plugins->add_hook('newreply_start', 'mmybb18_newreply_start');
$plugins->add_hook('newthread_start', 'mmybb18_newthread_start');
$plugins->add_hook('pre_output_page', 'mmybb18_pre_output_page');
$plugins->add_hook('redirect', 'mmybb18_redirect');


function mmybb18_info()
{
	return array(
		'name'          => 'Mobile MyBB 1.8',
		'description'   => 'Mobile MyBB 1.8 (MMyBB18) is a mobile-friendly MyBB 1.8 theme.',
		'website'       => 'http://flexplat.com/mobile-mybb-18',
		'author'        => 'Rickey Gu',
		'authorsite'    => 'http://flexplat.com',
		'version'       => '1.7.0',
		'guid'          => str_replace('.php', '', basename(__FILE__)),
		'codename'      => str_replace('.php', '', basename(__FILE__)),
		'compatibility' => '18*'
	);
}

function mmybb18_install()
{
}

function mmybb18_is_installed()
{
	$file = MYBB_ROOT . 'inc/plugins/mmybb18/Mobile MyBB 1.8-theme.xml';
	if ( !file_exists($file) )
	{
		return false;
	}

	return true;
}

function mmybb18_uninstall()
{
}

function mmybb18_activate()
{
	require(MYBB_ADMIN_DIR . '/inc/functions_themes.php');

	$file = MYBB_ROOT . 'inc/plugins/mmybb18/Mobile MyBB 1.8-theme.xml';
	if ( !file_exists($file) )
	{
		flash_message('Mobile MyBB 1.8 theme file is NOT exist.', 'error');
		admin_redirect('index.php?module=config/plugins');
	}

	$xml = @file_get_contents($file);
	if ( empty($xml) )
	{
		return;
	}

	$options = array(
		'force_name_check' => true,
		'version_compat' => 1,
		'no_templates' => 0,
		'parent' => 1,
		'no_stylesheets' => 1,
	);

	import_theme_xml($xml, $options);
}

function mmybb18_deactivate()
{
	global $db;

	$name = 'Mobile MyBB 1.8';
	$query = $db->simple_select('themes', 'tid', 'name="' . $db->escape_string($name) . '"', array('limit' => 1));
	$theme = $db->fetch_array($query);
	$db->delete_query('themes', 'tid="' . $theme['tid'] . '"');

	$title = 'Mobile MyBB 1.8 Templates';
	$query = $db->simple_select('templatesets', 'sid', 'title="' . $db->escape_string($title) . '"', array('limit' => 1));
	$templateset = $db->fetch_array($query);
	$db->delete_query('templatesets', 'sid="' . $templateset['sid'] . '"');
	$db->delete_query('templates', 'sid="' . $templateset['sid'] . '"');
}


function mmybb18_inline_error($errors, $title)
{
	global $lang;

	if ( empty($errors) )
	{
		return;
	}

	if ( empty($title) )
	{
		$title = $lang->please_correct_errors;
	}

	$inline_error = '
<li data-theme="e">
' . $title;

	$pattern = '#<a[^>]*>\s*(.*)\s*</a>#i';
	$pattern2 = '#\s*<br\s/>\s*<br\s/>\s*#i';
	foreach ( $errors as $error )
	{
		$error = preg_replace($pattern, '$1', $error);
		$error = preg_replace($pattern2, '  ', $error);

		$inline_error .= '
<br />
' . $error;
	}

	$inline_error .= '
</li>';

	return $inline_error;
}

function mmybb18_get_input($name, $template)
{
	global $mybb;

	$value = $mybb->get_input($name);
	if ( !empty($value) )
	{
		return $value;
	}

	$pattern = '#<input\stype="hidden"\sname="' . $name . '"\svalue="([^"]+)"\sid="[^"]+"\s/>#i';
	if ( preg_match($pattern, $template, $matches) )
	{
		$value = $matches[1];
	}

	return $value;
}

function mmybb18_update_user_theme()
{
	global $mybb;

	require_once MYBB_ROOT . 'inc/datahandlers/user.php';
	$userhandler = new UserDataHandler('update');

	$user = array(
		'uid' => $mybb->user['uid'],
		'style' => 0,
		'usergroup' => $mybb->user['usergroup'],
		'additionalgroups' => $mybb->user['additionalgroups']
	);

	$userhandler->set_data($user);

	if ( $userhandler->validate_user() )
	{
		$mybb->user['style'] = $user['style'];

		if ( $mybb->user['uid'] )
		{
			if ( isset($mybb->cookies['mybbtheme']) )
			{
				my_unsetcookie('mybbtheme');
			}

			$userhandler->update_user();
		}
		else
		{
			my_setcookie('mybbtheme', $user['style']);
		}
	}
}


function mmybb18_error($error)
{
	if ( !defined('MMYBB18') )
	{
		return;
	}

	$pattern = '#^\s*<!--\sstart:\s.+\s-->\s*#i';
	if ( preg_match($pattern, $error) )
	{
		return $error;
	}

	$pattern = '#<a[^>]*>\s*(.*)\s*</a>#i';
	$error = preg_replace($pattern, '$1', $error);

	$pattern = '#\s*<br\s/>\s*<br\s/>\s*#i';
	$error = preg_replace($pattern, '  ', $error);

	$pattern = '#\s*<p>\s*#i';
	$error = preg_replace($pattern, '<br />', $error);

	return $error;
}

function mmybb18_global_intermediate()
{
	if ( !defined('MMYBB18') )
	{
		return;
	}

	global $lang, $templates;
	global $header_personal;

	if ( !empty($lang->personal_header) )
	{
		eval('$header_personal = "' . $templates->get('header_personal') . '";');
	}
}

function mmybb18_global_start()
{
	global $mybb, $db, $lang;

	$file = MYBB_ROOT . 'inc/plugins/mmybb18p/Mobile MyBB 1.8 Premium-theme.xml';
	if ( file_exists($file) )
	{
		return;
	}

	if ( defined("IN_ADMINCP") )
	{
		return;
	}

	$name = 'Mobile MyBB 1.8';
	$query = $db->simple_select('themes', 'tid', 'name="' . $db->escape_string($name) . '"', array('limit' => 1));
	$theme = $db->fetch_array($query);
	if ( empty($theme) )
	{
		return;
	}
	$mobile_tid = $theme['tid'];

	$query = $db->simple_select('themes', 'tid', 'def=1', array('limit' => 1));
	$theme = $db->fetch_array($query);
	if ( empty($theme) )
	{
		return;
	}
	$default_tid = $theme['tid'];

	if ( isset($mybb->input['theme']) )
	{
		$mybb->input['theme'] = (int)$mybb->input['theme'];

		if ( $mybb->input['theme'] == $mobile_tid )
		{
			$mybb_redirection = 'mobile';
		}
		elseif ( $mybb->input['theme'] != 0 && $mybb->input['theme'] != $default_tid )
		{
			return;
		}
	}
	elseif ( $mybb->user['uid'] )
	{
		$mybb->user['style'] = (int)$mybb->user['style'];

		if ( $mybb->user['style'] == $mobile_tid )
		{
			$mybb_style = 'mobile';
		}
		elseif ( $mybb->user['style'] != 0 && $mybb->user['style'] != $default_tid )
		{
			return;
		}
	}
	else
	{
		$mybb->cookies['mybbtheme'] = (int)$mybb->cookies['mybbtheme'];

		if ( $mybb->cookies['mybbtheme'] == $mobile_tid )
		{
			$mybb_style = 'mobile';
		}
		elseif ( $mybb->cookies['mybbtheme'] != 0 && $mybb->cookies['mybbtheme'] != $default_tid )
		{
			return;
		}
	}

	$redirection = !empty($mybb->input['m-redirection']) ? $mybb->input['m-redirection'] : '';
	$style = !empty($mybb->cookies['mybb']['m_style']) ? $mybb->cookies['mybb']['m_style'] : '';

	if ( !empty($mybb_redirection) )
	{
		$device = $mybb_redirection;

		// make the cookie expires right now
		my_setcookie('mybb[m_style]', '', -1);
	}
	elseif ( !empty($redirection) )
	{
		$device = $redirection != 'mobile' ? 'desktop' : 'mobile';

		// make the cookie expires in a year time: 60 * 60 * 24 * 365 = 31,536,000
		my_setcookie('mybb[m_style]', $device);

		if ( !empty($mybb_style) )
		{
			mmybb18_update_user_theme();
		}
	}
	elseif ( !empty($mybb_style) )
	{
		$device = $mybb_style;

		// make the cookie expires right now
		my_setcookie('mybb[m_style]', '', -1);
	}
	elseif ( !empty($style) )
	{
		$device = $style != 'mobile' ? 'desktop' : 'mobile';

		// make the cookie expires in a year time: 60 * 60 * 24 * 365 = 31,536,000
		my_setcookie('mybb[m_style]', $device);
	}
	else
	{
		require(MYBB_ROOT . 'inc/plugins/mmybb18/lib/detection.php');

		$data = array();
		$data['user_agent'] = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$data['accept'] = !empty($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
		$data['profile'] = !empty($_SERVER['HTTP_PROFILE']) ? $_SERVER['HTTP_PROFILE'] : '';
		$device = mmybb18_get_device($data);

		$device = ( $device == 'desktop' || $device == 'bot' ) ? 'desktop' : 'mobile';

		// make the cookie expires in a year time: 60 * 60 * 24 * 365 = 31,536,000
		my_setcookie('mybb[m_style]', $device);
	}

	if ( $device != 'mobile' )
	{
		return;
	}

	define('MMYBB18', 'Mobile');

	$mybb->user['style'] = $mobile_tid;

	$lang->load('mmybb18');
}

function mmybb18_index_end()
{
	if ( !defined('MMYBB18') )
	{
		return;
	}

	global $lang, $templates;
	global $footer_personal;

	if ( !empty($lang->personal_footer) )
	{
		eval('$footer_personal = "' . $templates->get('footer_personal') . '";');
	}
}

function mmybb18_member_login_end()
{
	if ( !defined('MMYBB18') )
	{
		return;
	}

	global $errors, $title, $inline_errors, $member_loggedin_notice;

	$inline_errors = mmybb18_inline_error($errors, $title);

	$pattern = '#<a[^>]*>\s*(.*)\s*</a>#i';
	$member_loggedin_notice = preg_replace($pattern, '$1', $member_loggedin_notice);
}

function mmybb18_member_profile_end()
{
	if ( !defined('MMYBB18') )
	{
		return;
	}

	global $warning_level, $bannedbit;

	$pattern = '#<span[^>]*>\s*(.*)\s*</span>#i';
	$warning_level = preg_replace($pattern, '$1', $warning_level);
	$bannedbit = preg_replace($pattern, '$1', $bannedbit);

	$pattern = '#<a\shref="([^"]*)"[^>]*>\s*(.*)\s*</a>#i';
	$bannedbit = preg_replace($pattern, '<a href="' . '$1' . '" rel="external">' . '$2' . '</a>', $bannedbit);
}

function mmybb18_member_register_end()
{
	if ( !defined('MMYBB18') )
	{
		return;
	}

	global $templates;
	global $errors, $title, $regerrors;
	global $passboxes, $regimage, $questionbox, $tzselect, $boardlanguage, $time;
	global $allownoticescheck, $hideemailcheck, $receivepmscheck, $pmnoticecheck, $pmnotifycheck, $invisiblecheck;

	$regerrors = mmybb18_inline_error($errors, $title);

	$template = &$templates->cache['member_register'];

	$user = array(
		'username' => mmybb18_get_input('username', $template),
		'password' => mmybb18_get_input('password', $passboxes),
		'password2' => mmybb18_get_input('password2', $passboxes),
		'email' => mmybb18_get_input('email', $template),
		'email2' => mmybb18_get_input('email2', $template),
		'imagestring' => mmybb18_get_input('imagestring', $regimage),
		'imagehash' => mmybb18_get_input('imagehash', $regimage),
		'answer' => mmybb18_get_input('answer', $questionbox),
		'question_id' => mmybb18_get_input('question_id', $questionbox),
		'allownotices' => mmybb18_get_input('allownotices', $template),
		'allownoticescheck' => mmybb18_get_input('allownoticescheck', $template),
		'hideemail' => mmybb18_get_input('hideemail', $template),
		'hideemailcheck' => mmybb18_get_input('hideemailcheck', $template),
		'receivepms' => mmybb18_get_input('receivepms', $template),
		'receivepmscheck' => mmybb18_get_input('receivepmscheck', $template),
		'pmnotice' => mmybb18_get_input('pmnotice', $template),
		'pmnoticecheck' => mmybb18_get_input('pmnoticecheck', $template),
		'pmnotify' => mmybb18_get_input('pmnotify', $template),
		'pmnotifycheck' => mmybb18_get_input('pmnotifycheck', $template),
		'invisible' => mmybb18_get_input('invisible', $template),
		'invisiblecheck' => mmybb18_get_input('invisiblecheck', $template),
		'subscriptionmethod' => mmybb18_get_input('subscriptionmethod', $template),
		'timezone' => mmybb18_get_input('timezoneoffset', $tzselect),
		'dstcorrection' => mmybb18_get_input('dstcorrection', $template),
		'language' => mmybb18_get_input('language', $boardlanguage),
		'regtime' => mmybb18_get_input('regtime', $template),
		'status' => mmybb18_get_input('status', $template),
	);

	if ( $user['status'] === '' )
	{
		$user['status'] = 1;
	}
	else
	{
		$user['status']++;
		$user['status'] = !empty($questionbox) ? ( $user['status'] % 8 ) : ( $user['status'] % 7 );
	}

	if ( !empty($allownoticescheck) )
	{
		$user['allownoticescheck'] = 'checked';
	}

	if ( !empty($hideemailcheck) )
	{
		$user['hideemailcheck'] = 'checked';
	}

	if ( !empty($receivepmscheck) )
	{
		$user['receivepmscheck'] = 'checked';
	}

	if ( !empty($pmnoticecheck) )
	{
		$user['pmnoticecheck'] = 'checked';
	}

	if ( !empty($pmnotifycheck) )
	{
		$user['pmnotifycheck'] = 'checked';
	}

	if ( !empty($invisiblecheck) )
	{
		$user['invisiblecheck'] = 'checked';
	}

	if ( $user['status'] == 6 )
	{
		$pattern = '#imagehash=([^"]+)"#i';
		if ( preg_match($pattern, $regimage, $matches) )
		{
			$user['imagehash'] = $matches[1];
		}
	}

	if ( $user['regtime'] === '' )
	{
		$user['regtime'] = (string)($time - 10);
	}

	if ( $user['status'] != 0 )
	{
		$regerrors = '';
	}

	if ( $user['status'] != 1 )
	{
		$pattern = '#<legend>{\$lang->account_details}</legend>#i';
		$template = preg_replace($pattern, '', $template);

		$pattern = '#<div data-role="fieldcontain">[^\$]+{\$lang->username}.+</div>#isU';
		$pattern2 = '<input type="hidden" name="username" value="' . $user['username'] . '" id="username" />';
		$template = preg_replace($pattern, $pattern2, $template);

		$passboxes = '<input type="hidden" name="password" value="' . $user['password'] . '" id="password" />
<input type="hidden" name="password2" value="' . $user['password2'] . '" id="password2" />';
	}

	if ( $user['status'] != 2 )
	{
		$pattern = '#<div data-role="fieldcontain">[^\$]+{\$lang->email}.+</div>#isU';
		$pattern2 = '<input type="hidden" name="email" value="' . $user['email'] . '" id="email" />';
		$template = preg_replace($pattern, $pattern2, $template);

		$pattern = '#<div data-role="fieldcontain">[^\$]+{\$lang->confirm_email}.+</div>#isU';
		$pattern2 = '<input type="hidden" name="email2" value="' . $user['email2'] . '" id="email2" />';
		$template = preg_replace($pattern, $pattern2, $template);
	}

	if ( $user['status'] != 3 )
	{
		$pattern = '#<legend>{\$lang->account_prefs}</legend>#i';
		$template = preg_replace($pattern, '', $template);

		$pattern = '#<div data-role="fieldcontain">[^\$]+{\$allownoticescheck}.+</div>#isU';
		$pattern2 = '<input type="hidden" name="allownotices" value="' . $user['allownotices'] . '" id="allownotices" />
<input type="hidden" name="allownoticescheck" value="' . $user['allownoticescheck'] . '" id="allownoticescheck" />
<input type="hidden" name="hideemail" value="' . $user['hideemail'] . '" id="hideemail" />
<input type="hidden" name="hideemailcheck" value="' . $user['hideemailcheck'] . '" id="hideemailcheck" />
<input type="hidden" name="receivepms" value="' . $user['receivepms'] . '" id="receivepms" />
<input type="hidden" name="receivepmscheck" value="' . $user['receivepmscheck'] . '" id="receivepmscheck" />
<input type="hidden" name="pmnotice" value="' . $user['pmnotice'] . '" id="pmnotice" />
<input type="hidden" name="pmnoticecheck" value="' . $user['pmnoticecheck'] . '" id="pmnoticecheck" />
<input type="hidden" name="pmnotify" value="' . $user['pmnotify'] . '" id="pmnotify" />
<input type="hidden" name="pmnotifycheck" value="' . $user['pmnotifycheck'] . '" id="pmnotifycheck" />
<input type="hidden" name="invisible" value="' . $user['invisible'] . '" id="invisible" />
<input type="hidden" name="invisiblecheck" value="' . $user['invisiblecheck'] . '" id="invisiblecheck" />';
		$template = preg_replace($pattern, $pattern2, $template);
	}
	else
	{
		$pattern = '#<input type="checkbox" name="allownotices" id="allownotices" value="1" {\$allownoticescheck} />#i';
		if ( !empty($user['allownoticescheck']) )
		{
			$pattern2 = '<input type="checkbox" name="allownotices" id="allownotices" value="1" checked="checked" />';
		}
		else
		{
			$pattern2 = '<input type="checkbox" name="allownotices" id="allownotices" value="1" />';
		}
		$template = preg_replace($pattern, $pattern2, $template);

		$pattern = '#<input type="checkbox" name="hideemail" id="hideemail" value="1" {\$hideemailcheck} />#i';
		if ( !empty($user['hideemailcheck']) )
		{
			$pattern2 = '<input type="checkbox" name="hideemail" id="hideemail" value="1" checked="checked" />';
		}
		else
		{
			$pattern2 = '<input type="checkbox" name="hideemail" id="hideemail" value="1" />';
		}
		$template = preg_replace($pattern, $pattern2, $template);

		$pattern = '#<input type="checkbox" name="receivepms" id="receivepms" value="1" {\$receivepmscheck} />#i';
		if ( !empty($user['receivepmscheck']) )
		{
			$pattern2 = '<input type="checkbox" name="receivepms" id="receivepms" value="1" checked="checked" />';
		}
		else
		{
			$pattern2 = '<input type="checkbox" name="receivepms" id="receivepms" value="1" />';
		}
		$template = preg_replace($pattern, $pattern2, $template);

		$pattern = '#<input type="checkbox" name="pmnotice" id="pmnotice" value="1"{\$pmnoticecheck} />#i';
		if ( !empty($user['pmnoticecheck']) )
		{
			$pattern2 = '<input type="checkbox" name="pmnotice" id="pmnotice" value="1" checked="checked" />';
		}
		else
		{
			$pattern2 = '<input type="checkbox" name="pmnotice" id="pmnotice" value="1" />';
		}
		$template = preg_replace($pattern, $pattern2, $template);

		$pattern = '#<input type="checkbox" name="pmnotify" id="pmnotify" value="1" {\$pmnotifycheck} />#i';
		if ( !empty($user['pmnotifycheck']) )
		{
			$pattern2 = '<input type="checkbox" name="pmnotify" id="pmnotify" value="1" checked="checked" />';
		}
		else
		{
			$pattern2 = '<input type="checkbox" name="pmnotify" id="pmnotify" value="1" />';
		}
		$template = preg_replace($pattern, $pattern2, $template);

		$pattern = '#<input type="checkbox" name="invisible" id="invisible" value="1" {\$invisiblecheck} />#i';
		if ( !empty($user['invisiblecheck']) )
		{
			$pattern2 = '<input type="checkbox" name="invisible" id="invisible" value="1" checked="checked" />';
		}
		else
		{
			$pattern2 = '<input type="checkbox" name="invisible" id="invisible" value="1" />';
		}
		$template = preg_replace($pattern, $pattern2, $template);
	}

	if ( $user['status'] != 4 )
	{
		$pattern = '#<div data-role="fieldcontain">[^\$]+{\$lang->subscription_method}.+</div>#isU';
		$pattern2 = '<input type="hidden" name="subscriptionmethod" value="' . $user['subscriptionmethod'] . '" id="subscriptionmethod" />';
		$template = preg_replace($pattern, $pattern2, $template);
	}

	if ( ( $user['status'] != 4 ) && !empty($boardlanguage) )
	{
		$boardlanguage = '<input type="hidden" name="language" value="' . $user['language'] . '" id="language" />';
	}

	if ( $user['status'] != 5 )
	{
		$pattern = '#<legend>{\$lang->time_offset}</legend>#i';
		$template = preg_replace($pattern, '', $template);

		$pattern = '#<div data-role="fieldcontain">[^\$]+{\$lang->time_offset_desc}.+</div>#isU';
		$pattern2 = '<input type="hidden" name="timezoneoffset" value="' . $user['timezone'] . '" id="timezoneoffset" />';
		$template = preg_replace($pattern, $pattern2, $template);

		$pattern = '#<div data-role="fieldcontain">[^\$]+{\$lang->dst_correction}.+</div>#isU';
		$pattern2 = '<input type="hidden" name="dstcorrection" value="' . $user['dstcorrection'] . '" id="dstcorrection" />';
		$template = preg_replace($pattern, $pattern2, $template);
	}

	if ( $user['status'] != 6 )
	{
		$regimage = '<input type="hidden" name="imagestring" value="' . $user['imagestring'] . '" id="imagestring" />
<input type="hidden" name="imagehash" value="' . $user['imagehash'] . '" id="imagehash" />';
	}
	else
	{
		$pattern = '#<input type="hidden" name="imagehash" value="[^"]+" id="imagehash" />#i';
		$pattern2 = '<input type="hidden" name="imagehash" value="' . $user['imagehash'] . '" id="imagehash" />';
		$regimage = preg_replace($pattern, $pattern2, $regimage);
	}

	if ( ( $user['status'] != 7 ) && !empty($questionbox) )
	{
		$questionbox = '<input type="hidden" name="answer" value="' . $user['answer'] . '" id="answer" />
<input type="hidden" name="question_id" value="' . $user['question_id'] . '" id="question_id" />';
	}

	$pattern = '#<input type="hidden" name="regtime" value="{\$time}" />#i';
	$pattern2 = '<input type="hidden" name="regtime" value="' . $user['regtime'] . '" />';
	$template = preg_replace($pattern, $pattern2, $template);

	$pattern = '#<input type="hidden" name="action" value="do_register" />#i';
	$pattern2 = '<input type="hidden" name="action" value="do_register" />
<input type="hidden" name="status" value="' . $user['status'] . '" id="status" />';
	$template = preg_replace($pattern, $pattern2, $template);

	if ( ( ( $user['status'] != 7 ) && !empty($questionbox) ) || ( ( $user['status'] != 6 ) && empty($questionbox) ) )
	{
		$pattern = '#<input type="submit" name="regsubmit" value="{\$lang->submit_registration}" data-theme="a" />#i';
		$pattern2 = '<input type="submit" name="regsubmit" value="{\$lang->continue_registration}" data-theme="a" />';
		$template = preg_replace($pattern, $pattern2, $template);
	}
}

function mmybb18_newreply_start()
{
	if ( !defined('MMYBB18') )
	{
		return;
	}

	global $lang;
	global $post_errors, $title, $reply_errors;

	$pattern = '#</?(b|strong)>#i';
	$lang->options_sig = preg_replace($pattern, '', $lang->options_sig);
	$lang->options_disablesmilies = preg_replace($pattern, '', $lang->options_disablesmilies);
	$lang->close_thread = preg_replace($pattern, '', $lang->close_thread);
	$lang->stick_thread = preg_replace($pattern, '', $lang->stick_thread);

	$reply_errors = mmybb18_inline_error($post_errors, $title);
}

function mmybb18_newthread_start()
{
	if ( !defined('MMYBB18') )
	{
		return;
	}

	global $lang;
	global $post_errors, $title, $thread_errors;

	$pattern = '#</?(b|strong)>#i';
	$lang->options_sig = preg_replace($pattern, '', $lang->options_sig);
	$lang->options_disablesmilies = preg_replace($pattern, '', $lang->options_disablesmilies);
	$lang->close_thread = preg_replace($pattern, '', $lang->close_thread);
	$lang->stick_thread = preg_replace($pattern, '', $lang->stick_thread);

	$thread_errors = mmybb18_inline_error($post_errors, $title);
}

function mmybb18_pre_output_page($contents)
{
	if ( !defined('MMYBB18') )
	{
		return;
	}

	global $mybb;

	$pattern = '#<!--.*-->#i';
	$contents = preg_replace($pattern, '', $contents);

	$pattern = '#<title>\s*(.*)\s*</title>#i';
	if ( preg_match($pattern, $contents, $matches) )
	{
		$pattern2 = '#(<h1[^>]*>).*(</h1>)#i';
		$contents = preg_replace($pattern2, '$1' . $matches[1] . '$2', $contents);
	}

	if ( $lang->settings['rtl'] == 1 )
	{
		$pattern = '#(<link rel="stylesheet" href=")http://code.jquery.com/mobile/1.3.2/[^"]+(" />)#i';
		$contents = preg_replace($pattern, '$1' . $mybb->settings['bburl'] . '/inc/plugins/mmybb18/themes/jquery/mobile/1.3.2/jquery.mobile-1.3.2.rtl.min.css' . '$2', $contents);
	}

	if ( ( !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ) || $_SERVER['SERVER_PORT'] == 443 )
	{
		$pattern = '#(<link rel="stylesheet" href=")http://([^"]+" />)#i';
		$contents = preg_replace($pattern, '$1' . 'https://' . '$2', $contents);

		$pattern = '#(<script src=")http://([^"]+"></script>)#i';
		$contents = preg_replace($pattern, '$1' . 'https://' . '$2', $contents);
	}

	$pattern = '#<p[^>]*>\s*</p>#i';
	$contents = preg_replace($pattern, '', $contents);

	$contents = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $contents);

	return $contents;
}

function mmybb18_redirect($redirect_args)
{
	if ( !defined('MMYBB18') )
	{
		return;
	}

	$pattern = '#<a[^>]*>\s*(.*)\s*</a>#i';
	$redirect_args['message'] = preg_replace($pattern, '$1', $redirect_args['message']);

	$pattern = '#\s*<br\s/>\s*<br\s/>\s*#i';
	$redirect_args['message'] = preg_replace($pattern, '  ', $redirect_args['message']);

	return $redirect_args;
}