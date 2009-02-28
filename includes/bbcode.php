<?php
/* ===========================================================================
 *  Here follow a few functions that form the bbcode/smily support API for
 *  all places on RPGDX that support this.
 */

$html_entities_match = array('#&#', '#<#', '#>#');
$html_entities_replace = array('&amp;', '&lt;', '&gt;');

$unhtml_specialchars_match = array('#&gt;#', '#&lt;#', '#&quot;#', '#&amp;#');
$unhtml_specialchars_replace = array('>', '<', '"', '&');


/*  Convert an edited message to the format used to store it in the database.
 */
function prepare_for_store($message, $bbcode_uid = '')
{
	global $html_entities_match, $html_entities_replace;
  global $rpgdx_config;

  // Clean up the message
	$message = trim($message);

  // Replace any HTML stuff
	$message = preg_replace($html_entities_match, $html_entities_replace, $message);

	if ($rpgdx_config['use_bbcode'] && $bbcode_uid != '') {
		$message = bbencode_first_pass($message, $bbcode_uid);
	}

	return $message;
}


/* Convert a message from the database for display purposes
 */
function prepare_for_display($message, $bbcode_uid = '', $tpl_filename = 'bbcode.tpl', $newlines = true, $links = true)
{
  // Convert newlines to <br /> tags
	if ($newlines) {
		$message = nl2br($message);
	}

  if ($bbcode_uid != '') {
    $message = bbencode_second_pass($message, $bbcode_uid, $tpl_filename);
  }

  // Convert text links to clickable links
	if ($links) {
		$message = make_clickable($message);
	}

  return $message;
}


/* Convert a message from the database for editing purposes
 */
function prepare_for_edit($message, $bbcode_uid = '')
{
	global $unhtml_specialchars_match, $unhtml_specialchars_replace;

  // Put HTML characters back
  $message = preg_replace($unhtml_specialchars_match, $unhtml_specialchars_replace, $message);

	if ($bbcode_uid != '') {
    $message = preg_replace('/\:(([a-z0-9]:)?)' . $bbcode_uid . '/s', '', $message);
  }

  return $message;
}

?>
