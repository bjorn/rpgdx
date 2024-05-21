<?php
if (!defined('IN_RPGDX')) {
	die("Hacking attempt?");
}

function getmicrotime()
{	
	list($usec, $sec) = explode(" ",microtime()); 
	return ((float)$usec + (float)$sec); 
}


function add_log($string)
{
  doQuery("INSERT INTO rpgdx_log (log_text, log_time) VALUES ('". addslashes($string) ."', NOW())");
}
/*
function array_stripslashes(&$array) {
  while (list($key) = each($array)) {
    if (is_array($array[$key])) {
      array_stripslashes($array[$key]);
    } else {
      $array[$key] = stripslashes($array[$key]);
    }
  }
}

function array_addslashes(&$array) {
  while (list($key) = each($array)) {
    if (is_array($array[$key])) {
      array_addslashes($array[$key]);
    } else {
      $array[$key] = addslashes($array[$key]);
    }
  }
}
*/


//===============================
// Some error checking functions
//===============================

/* check_access_level() succeeds when a user is logged in and is either the
 * expected user or an administrator.
 */
function check_access_level($object_table, $object_id_name, $object_id)
{
	global $error, $critical, $userdata;
	$object_id = intval($object_id);

	if (!$userdata['session_logged_in']) {
		return 'You have to login to be able to view this page.';
	}
	else if (($object_id < 1) ||
		       !($object = mysql_fetch_object(doQuery("SELECT user_id FROM $object_table WHERE $object_id_name = $object_id"))))
	{
		return 'No valid '. $object_id_name .' specified.';
	}
	else if (($userdata['user_id'] != $object->user_id) &&
		       ($userdata['user_level'] != ADMIN))
	{
		return 'You do not have permission to view this page.';
	}
	else
	{
		return false;
	}
}

/* abort_with_error() will use the error message template to show the given
 * error message after which it adds the footer and dies.
 */
function abort_with_error($error_msg)
{
	global $template, $header_placed;

	if (!$header_placed) placeHeader(array(array('Error', '')));

	$template->set_filenames(array('body' => 'error_body.tpl'));
	$template->assign_vars(array('ERROR' => $error_msg));
	$template->pparse('body');

	placeFooter();
	die();
}



//=============================
// File upload helper functions
//=============================

function add_uploaded_file($temp_filename, $type, $extension)
{
	// Add the file entry to the database
	$result = doQuery("INSERT INTO rpgdx_uploads (upload_type, upload_ext) VALUES ('$type', '$extension')");
	$id = mysql_insert_id();
	
	$final_filename = 'uploads/' . $type . $id . $extension;

	// Attempt to move the file to it's final place
	if (!move_uploaded_file($temp_filename, $final_filename))
	{
		doQuery("DELETE FROM rpgdx_uploads WHERE upload_id = $id");
		abort_with_error("Error while trying to move the uploaded file ('$temp_filename' -> '$final_filename').");
	}
	else
	{
		// Give read permissions to all users
		chmod($final_filename, 0644);
	}

	return $id;
}


function remove_uploaded_file($upload_id)
{
	$result = mysql_fetch_array(doQuery(
		"SELECT upload_type, upload_id, upload_ext FROM rpgdx_uploads WHERE upload_id = $upload_id"
	));

	$filename = 'uploads/'.$result['upload_type'].$result['upload_id'].$result['upload_ext'];
	unlink($filename);
}


// Creates a thumbnail alongside the image, adding _thumb to the filename.
function create_thumbnail($img_filename)
{
	$img_size = getimagesize($img_filename);
	$img = false;

	switch ($img_size[2])
	{
		case 1: $img = imagecreatefromgif($img_filename); break; // GIF
		case 2: $img = imagecreatefromjpeg($img_filename); break; // JPG
		case 3: $img = imagecreatefrompng($img_filename); break; // PNG
	}

	if (!$img) {
		abort_with_error("Creating thumbnail failed, could not open image ($img_filename).");
	} else {
		$thumb = imagecreatetruecolor(140, imagesy($img) * (140 / imagesx($img)));
		imagecopyresampled($thumb, $img, 0, 0, 0, 0, 140, imagesy($img) * (140 / imagesx($img)), imagesx($img), imagesy($img));
		imagejpeg($thumb, $img_filename.'.thumb.jpg', 90);
	}
}



//=================================
// Contest results helper functions
//=================================

function get_contest_results($project_id)
{
	// Fetch all the contests this game has participated in.
	$contests = doQuery(
		"SELECT contest_id ".
		"FROM ". CONTESTS_TABLE ." ".
		"LEFT JOIN ". CONTEST_ENTRIES_TABLE ." ON entry_contest = contest_id ".
		"WHERE entry_project = $project_id AND contest_status = ". CON_CLOSED
	);

	// For each contest...
	while ($contest = mysql_fetch_object($contests))
	{
		/* Initialize variables
		 */
		unset($categories);
		unset($prev_score);
		unset($entries);

		/* Determine which projects have won in which category
		 */

		// Select categories that could be voted for
		$result = @doQuery(
			"SELECT category_id, category_name, category_image, contest_id, contest_name ".
			"FROM rpgdx_contest_categories ".
			"LEFT JOIN ". CONTESTS_TABLE ." ON category_contest = contest_id ".
			"WHERE category_contest = $contest->contest_id ".
			"ORDER BY category_value DESC"
		);

		$i = 0;
		while ($category = mysql_fetch_object($result)) {
			$categories[$i] = $category;
			$i++;
		}

		// Fetch the projects for which was voted, best on top.
		// TODO: Add real category support to this system
		$result = doQuery(
			"SELECT sum( category_value ) AS score, project_id ".
			"FROM `rpgdx_contest_votes` ".
			"LEFT JOIN rpgdx_contest_entries ON vote_entry = entry_id ".
			"LEFT JOIN rpgdx_projects ON entry_project = rpgdx_projects.project_id ".
			"LEFT JOIN rpgdx_contest_categories ON vote_category = category_id ".
			"WHERE entry_contest = ".$contest->contest_id." ".
			"GROUP BY project_id ".
			"ORDER BY score DESC"
		);

		$i = 0;
		while ($i < sizeof($categories) && $entry = mysql_fetch_object($result)) {
			if (isset($prev_score) && $entry->score != $prev_score) {
				$i++;
			}

			if ($i < sizeof($categories)) {
				// Set category in which this project won
				$entries[$entry->project_id] = $categories[$i];
				$prev_score = $entry->score;
			}
		}

		// Determine if our project has won anything here
		if (isset($entries[$project_id])) {
			$winnings[] = $entries[$project_id];
		}
	}

	if (isset($winnings)) {return $winnings;}
	else {return;}
}



//================================
// Search results helper functions
//================================

// Cut project description without cutting words
function shorten_text($text, $maxTextLength)
{
  if (strlen($text) > $maxTextLength) {
     $text = substr( trim($text), 0, $maxTextLength ); 
     $text = substr( $text, 0, strlen($text) - strpos(strrev($text), ' ') - 1 );
     $text = $text . '...';
  }
	return $text;
}

function add_links($string) {
	$string = preg_replace(
		"/(?<!quot;|[=\"]|:\/\/)\b((\w+:\/\/|www\.).+?)(?=\W*([<>\s]|$))/i",
		"<a href=\"$1\">$1</a>",
		$string
	);
	return preg_replace(
		"/href=\"www/i",
		"href=\"http://www",
		$string
	);
}


// Highlights words from the given array
function highlight($text, $words)
{
	$replacements = array();
	foreach ($words as $key => $word) {
		$replacements[$key] = '<u>\\0</u>';
		$word = str_replace(array('.'), array('\.'), $word);
		$words[$key] = "/$word/i";
	}
	$text = preg_replace($words, $replacements, $text);
	return $text;
}


?>