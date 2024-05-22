<?php
if (!defined('IN_RPGDX')) {
	die("Hacking attempt?");
}


$do_gzip_compress = FALSE;
$gzip_enabled = FALSE;
$header_placed = FALSE;

/* The placeHeader function is meant to be executed before any
 * other output has been written to the page!
 */
function placeHeader($subtitles)
{
	global $userdata;
	global $template;
	global $rpgdx_config;
	global $do_gzip_compress, $gzip_enabled;
	global $HTTP_USER_AGENT, $HTTP_SERVER_VARS;
	global $s;
	global $header_placed;

	/* Do GZip compression if PHP as well as the browser supports it.
	 * Borrowed from phpBB
	 */
  if ($rpgdx_config['enable_gzip_compression']) {
    $phpver = phpversion();
    if ( $phpver >= '4.0.4pl1' && strstr($HTTP_USER_AGENT,'compatible') )
    {
      if ( extension_loaded('zlib') )
      {
        $gzip_enabled = TRUE;
        ob_start('ob_gzhandler');
      }
    }
    else if ( $phpver > '4.0' )
    {
      if ( strstr($HTTP_SERVER_VARS['HTTP_ACCEPT_ENCODING'], 'gzip') )
      {
        if ( extension_loaded('zlib') )
        {
          $do_gzip_compress = TRUE;
          $gzip_enabled = TRUE;
          ob_start();
          ob_implicit_flush(0);

          header('Content-Encoding: gzip');
        }
      }
    }
  }

	$template->set_filenames(array(
		'header' => 'overall_header.tpl')
	);

	$temp_subtitle = "";

	if (isset($subtitles)) {
		foreach ($subtitles as $subtitle)
		{
			$template->assign_block_vars('subtitle', array('TITLE' => $subtitle[0]));
			if (!empty($subtitle[1])) {
				$template->assign_block_vars('subtitle.url', array('URL' => $subtitle[1]));
			}
			$temp_subtitle = $subtitle[0];
		}
	}

	$page_title = $temp_subtitle;

	$template->assign_vars(array(
		'PAGE_TITLE' => $page_title)
	);


	$template->pparse('header');
	$header_placed = TRUE;
}


/* The placeFooter function is meant to be executed after any
 * other output has been written to the page!
 */

function placeFooter()
{
	global $template;
	global $do_gzip_compress, $gzip_enabled;
	global $time_start;
	global $s;

	$template->set_filenames(array(
		'footer' => 'overall_footer.tpl')
	);

	$template->pparse('footer');


	/*
	echo "<div align=center style=\"font-size: 10px;\">generated in ".
		number_format(getmicrotime() - $time_start, 2) ." seconds - gzip ".
		(($gzip_enabled) ? "on" : "off") ."<br /><br /></div>";
	*/


	/* Compress buffered output if required and send to browser
	 * Borrowed from php.net
	 */
	if ($do_gzip_compress)
	{
		$gzip_contents = ob_get_contents();
		ob_end_clean();

		$gzip_size = strlen($gzip_contents);
		$gzip_crc = crc32($gzip_contents);

		$gzip_contents = gzcompress($gzip_contents, 9);
		$gzip_contents = substr($gzip_contents, 0, strlen($gzip_contents) - 4);

		echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";
		echo $gzip_contents;
		echo pack('V', $gzip_crc);
		echo pack('V', $gzip_size);
	}

	exit;
}
?>
