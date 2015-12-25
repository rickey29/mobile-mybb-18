<?php
/*
	project: Mobile MyBB 1.8 (MMyBB18)
	file:    MYBB_ROOT/inc/plugins/mmybb18.php
	version: 1.3.0
	author:  Rickey Gu
	web:     http://flexplat.com
	email:   rickey29@gmail.com
*/

// Disallow direct access to this file for security reasons
if ( !defined("IN_MYBB") )
{
	die("Direct initialization of this file is not allowed.");
}


$plugins->add_hook('error', 'mphpbb18_error');
$plugins->add_hook('global_end', 'mphpbb18_global_end');
$plugins->add_hook('global_intermediate', 'mphpbb18_global_intermediate');
$plugins->add_hook('global_start', 'mphpbb18_global_start');
$plugins->add_hook('index_end', 'mphpbb18_index_end');
$plugins->add_hook('member_login_end', 'mphpbb18_member_login_end');
$plugins->add_hook('member_profile_end', 'mphpbb18_member_profile_end');
$plugins->add_hook('member_register_end', 'mphpbb18_member_register_end');
$plugins->add_hook('newreply_start', 'mphpbb18_newreply_start');
$plugins->add_hook('newthread_start', 'mphpbb18_newthread_start');
$plugins->add_hook('pre_output_page', 'mphpbb18_pre_output_page');
$plugins->add_hook('redirect', 'mphpbb18_redirect');


function mmybb18_info()
{
	return array(
		"name"          => "Mobile MyBB 1.8",
		"description"   => "Mobile MyBB 1.8 (MMyBB18) is a mobile-friendly MyBB 1.8 theme.",
		"website"       => "http://flexplat.com/mobile-mybb-18",
		"author"        => "Rickey Gu",
		"authorsite"    => "http://flexplat.com",
		"version"       => "1.3.0",
		"guid"          => str_replace('.php', '', basename(__FILE__)),
		"codename"      => str_replace('.php', '', basename(__FILE__)),
		"compatibility" => "18*"
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

	$file = MYBB_ROOT . 'inc/languages/english/mmybb18.lang.php';
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
	$query = $db->simple_select("themes", "tid", "name='".$db->escape_string($name)."'", array("limit" => 1));
	$theme = $db->fetch_array($query);
	$db->delete_query("themes", "tid='{$theme['tid']}'");

	$title = 'Mobile MyBB 1.8 Templates';
	$query = $db->simple_select("templatesets", "sid", "title='".$db->escape_string($title)."'", array("limit" => 1));
	$templateset = $db->fetch_array($query);
	$db->delete_query("templatesets", "sid='{$templateset['sid']}'");
	$db->delete_query("templates", "sid='{$templateset['sid']}'");
}


function mphpbb18_inline_error($errors, $title)
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


function mphpbb18_error($error)
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

function mphpbb18_global_end()
{
	if ( !defined('MMYBB18') )
	{
		return;
	}

	global $lang;
	global $headerinclude;

	if ( $lang->settings['rtl'] == 1 )
	{
		$pattern = '#(jquery\.mobile\-\d+\.\d+\.\d+)(\.min\.css)#i';
		$headerinclude = preg_replace($pattern, '$1' . '.rtl' . '$2', $headerinclude);
	}
}

function mphpbb18_global_intermediate()
{
	if ( !defined('MMYBB18') )
	{
		return;
	}

	global $lang, $templates;
	global $header_personal;

	if ( !empty($lang->personal_header) )
	{
		eval('$header_personal = "'.$templates->get('header_personal').'";');
	}
}

function mphpbb18_global_start()
{
	require(MYBB_ROOT . 'inc/plugins/mmybb18/lib/detection.php');

	global $mybb, $db, $lang;

	if ( defined("IN_ADMINCP") )
	{
		return;
	}

	if ( isset($mybb->user['style']) && (int)$mybb->user['style'] != 0 )
	{
		return;
	}

	$redirection = !empty($mybb->input['m-redirection']) ? $mybb->input['m-redirection'] : '';
	if ( !empty($redirection) && $redirection != 'mobile' )
	{
		// make the cookie expires in a year time: 60 * 60 * 24 * 365 = 31,536,000
		my_setcookie('mybb[m_style]', 'desktop');

		return;
	}

	$style = !empty($mybb->cookies['mybb']['m_style']) ? $mybb->cookies['mybb']['m_style'] : '';
	if ( empty($redirection) && !empty($style) && $style == 'desktop' )
	{
		return;
	}

	if ( empty($style) || $style == 'desktop' )
	{
		$data = array();
		$data['user_agent'] = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$data['accept'] = !empty($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
		$data['profile'] = !empty($_SERVER['HTTP_PROFILE']) ? $_SERVER['HTTP_PROFILE'] : '';

		$device = m_get_device($data);

		if ( $device == 'desktop' || $device == 'bot' )
		{
			// make the cookie expires in a year time: 60 * 60 * 24 * 365 = 31,536,000
			my_setcookie('mybb[m_style]', 'desktop');

			return;
		}

		// make the cookie expires in a year time: 60 * 60 * 24 * 365 = 31,536,000
		my_setcookie('mybb[m_style]', 'mobile');
	}

	define('MMYBB18', 'Mobile');

	$name = 'Mobile MyBB 1.8';
	$query = $db->simple_select("themes", "tid", "name='".$db->escape_string($name)."'", array("limit" => 1));
	$theme = $db->fetch_array($query);
	$mybb->user['style'] = $theme['tid'];

	$lang->load("mmybb18");
}

function mphpbb18_index_end()
{
	if ( !defined('MMYBB18') )
	{
		return;
	}

	global $lang, $templates;
	global $footer_personal;

	if ( !empty($lang->personal_footer) )
	{
		eval('$footer_personal = "'.$templates->get('footer_personal').'";');
	}
}

function mphpbb18_member_login_end()
{
	if ( !defined('MMYBB18') )
	{
		return;
	}

	global $errors, $title, $inline_errors, $member_loggedin_notice;

	$inline_errors = mphpbb18_inline_error($errors, $title);

	$pattern = '#<a[^>]*>\s*(.*)\s*</a>#i';
	$member_loggedin_notice = preg_replace($pattern, '$1', $member_loggedin_notice);
}

function mphpbb18_member_profile_end()
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

function mphpbb18_member_register_end()
{
	if ( !defined('MMYBB18') )
	{
		return;
	}

	global $errors, $title, $regerrors;

	$regerrors = mphpbb18_inline_error($errors, $title);
}

function mphpbb18_newreply_start()
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

	$reply_errors = mphpbb18_inline_error($post_errors, $title);
}

function mphpbb18_newthread_start()
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

	$thread_errors = mphpbb18_inline_error($post_errors, $title);
}

function mphpbb18_pre_output_page($contents)
{
	if ( !defined('MMYBB18') )
	{
		return;
	}

	$pattern = '#<!--.*-->#i';
	$contents = preg_replace($pattern, '', $contents);

	$pattern = '#<title>\s*(.*)\s*</title>#i';
	if ( preg_match($pattern, $contents, $matches) )
	{
		$pattern2 = '#(<h1>)\s*(.*)\s*(</h1>)#i';
		$contents = preg_replace($pattern2, '$1' . $matches[1] . '$3', $contents);
	}

	$pattern = '#<p>\s*</p>#i';
	$contents = preg_replace($pattern, '', $contents);


	$contents2 = '';

	$contents = explode("\n", $contents);
	foreach ( $contents as $line )
	{
		$line = trim($line);
		if ( empty($line) )
		{
			continue;
		}

		if ( !empty($contents2) )
		{
			$contents2 .= "\n";
		}
		$contents2 .= $line;
	}

	return $contents2;
}

function mphpbb18_redirect($redirect_args)
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
?>