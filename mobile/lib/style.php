<?php
/*
	project: Mobile MyBB 1.8 (MMyBB18)
	file:    MYBB_ROOT/mobile/lib/style.php
	version: 0.0.0
	author:  Rickey Gu
	web:     http://flexplat.com
	email:   rickey29@gmail.com
*/

if ( !defined("IN_MYBB") )
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}


function m_update_inline_error($errors)
{
	if ( empty($errors) )
	{
		return '';
	}

	if ( MMYBB18 != 'Feature Phone' )
	{
		$inline_errors = '<li>';
	}
	else
	{
		$inline_errors = '<br />';
	}

	$pattern = '#<br\s*/>\s*<br\s*/>#isU';
	$pattern2 = '#^(.*)<a.*\s+href\s*=\s*("|\')([^\\2]*)\\2.*>\s*(.*)\s*</a>(.*)$#isU';
	foreach ( $errors as $num => $error )
	{
		if ( $num != 0 )
		{
			$inline_errors .= '
<br />';
		}

		foreach ( preg_split($pattern, $error) as $key => $value )
		{
			$value = trim($value);

			if ( $key == 0 )
			{
				$inline_errors .= '
';
			}
			else
			{
				$inline_errors .= ' ';
			}

			if ( preg_match($pattern2, $value, $matches) )
			{
				$inline_errors .= $matches[1] . $matches[4] . $matches[5];
			}
			else
			{
				$inline_errors .= $value;
			}
		}
	}

	if ( MMYBB18 != 'Feature Phone' )
	{
		$inline_errors .= '
</li>';
	}

	return $inline_errors;
}
?>
