<?php

define('IN_RPGDX', true);
define('IN_PHPBB', true);
$phpbb_root_path = './forums/';
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/bbcode.'.$phpEx);

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_INDEX);
init_userprefs($userdata);
//
// End session management
//

include_once("includes/constants.php");
include_once("includes/functions.php");
include_once("includes/database.php");
include_once("includes/page.php");
include_once("includes/bbcode.php");

$time_start	= getmicrotime();


/*
 * Add slashes to posted information if not done automatically
 * (now done by forum common.php)
 */
/*
if (!get_cfg_var("magic_quotes_gpc")) {
  array_addslashes($HTTP_POST_VARS);
  array_addslashes($HTTP_GET_VARS);
}
*/
extract($HTTP_POST_VARS, EXTR_OVERWRITE);
extract($HTTP_GET_VARS, EXTR_OVERWRITE);


/*
 * Create a new instance of the template class.
 */
if ($userdata['session_logged_in'] && $userdata['user_theme'] > 0 && empty($override_theme)) {
  $row = mysql_fetch_row(doQuery("SELECT theme_dir FROM ". RPGDX_THEMES_TABLE ." WHERE theme_id=". $userdata['user_theme']));
  $template_dir = $row[0];
} elseif (empty($override_theme)) {
  $template_dir = 'modern';
}
else {
  $template_dir = $override_theme;
}
$template = new Template("templates/$template_dir");



// ============================================================
// Below here lots of variables are added to the template class
// ============================================================


// Determine the login text in the user bar
if ($userdata['session_logged_in']) {
	$template->assign_block_vars('userlinks', array());

	// Add a link to the user page
	$template->assign_block_vars('userlinks.userlink', array(
		'URL'  => append_sid("userpage.php"),
		'TEXT' => "user page")
	);

	if ($userdata['user_level'] == 1) {
		$template->assign_block_vars('userlinks.userlink', array(
			'URL'  => append_sid("admin.php"),
			'TEXT' => "administration")
		);
	}

	$loginbar_text .= "Welcome ". (($userdata['user_level'] == ADMIN) ? 'admin.' : '') ." ". $userdata['username'];
	$loginbar_text .= " [<a href=\"". append_sid("login.php?logout=true") ."\">log out</a>]";
} 
else
{
	$loginbar_text .= "Not logged in.";
	$loginbar_text .= " [<a href=\"". append_sid("login.php") ."\">log in</a>]";
	$loginbar_text .= " [<a href=\"". append_sid("register.php"). "\">register</a>]";
}


$template->assign_vars(array(
	'TEMPL_DIR'       => "templates/$template_dir/",

	'TITLE'           => 'RPGDX',
	'LOGINBAR_TEXT'   => $loginbar_text,

  'U_NEWS'          => append_sid('index.php'),
  'U_FORUMS'        => append_sid('http://forums.rpgdx.net/'),
	'U_WIKI'          => append_sid('http://wiki.rpgdx.net/Main/HomePage'),
  'U_ABOUT'         => append_sid('about.php'),
	'U_CONTESTS'      => append_sid('showcontests.php'),
	'U_SEARCH'        => append_sid('search.php'),

	'S_LOGIN_ACTION'  => append_sid('login.php'),
	
  'HIDE_URL'        => 'onmouseover="status=\'\'; return true;"',
  'BBCODE_ON'       => 'bbcode enabled',
	
	'MAX_SCREENSHOTS' => $rpgdx_config['screenshots_limit'])
);



/* Add the different RPG types and article categories to the menu.
 */

// Get counts from different genres
$result = doQuery("SELECT project_type, COUNT(*) AS number FROM ". PROJECTS_TABLE ." GROUP BY project_type");
while ($row = mysql_fetch_object($result)) {
	$rpg_genre[$row->project_type] = $row->number;
}

// Add each RPG type
$result = doQuery("SELECT * FROM ". PROJECT_TYPES_TABLE);
while ($row = mysql_fetch_object($result))
{
	if (!isset($rpg_genre[$row->type_id])) {
		$rpg_genre[$row->type_id] = 0;
	}

	$template->assign_block_vars('rpgtype', array(
		'NAME'  => strtolower($row->type_title),
		'URL'   => append_sid('showcategory.php?cat='. $row->type_id),
		'COUNT' => $rpg_genre[$row->type_id])
	);
}

// Get counts from different article categories
$result = doQuery("SELECT article_type, COUNT(*) AS number FROM ". ARTICLES_TABLE ." GROUP BY article_type");
while ($row = mysql_fetch_object($result)) {
	$rpg_category[$row->article_type] = $row->number;
}

// Add each article category
$result = doQuery("SELECT * FROM ". ARTICLE_TYPES_TABLE);
while ($row = mysql_fetch_object($result))
{
	if (!isset($rpg_category[$row->type_id])) {
		$rpg_category[$row->type_id] = 0;
	}

	$template->assign_block_vars('artcat', array(
		'NAME'  => strtolower($row->type_title),
		'URL'   => append_sid('showarticles.php?cat='. $row->type_id),
		'COUNT' => $rpg_category[$row->type_id])
	);
}



// Show 5 latest updated games

$result = doQuery(
	"SELECT DATE_FORMAT(project_last_update, '%d-%m-%Y %H:%i:%s') AS updated, project_name AS name, project_id ".
	"FROM ". PROJECTS_TABLE ." WHERE project_last_update ORDER BY project_last_update DESC LIMIT 5"
);
if (mysql_num_rows($result) > 0) {
	$template->assign_block_vars('submenu', array('TITLE' => 'Game updates'));

	while ($game = mysql_fetch_object($result))
	{
		$template->assign_block_vars('submenu.update', array(
			'DATE' => $game->updated,
      'NAME' => $game->name,
      'URL'  => append_sid("showgame.php?project_id=$game->project_id"),
			'LINK' => "<a href=\"". append_sid("showgame.php?project_id=$game->project_id") ."\">$game->name</a>")
		);
	}
}


// Show 5 latest added reviews

$result = doQuery(
	"SELECT username, DATE_FORMAT(review_added, '%d-%m-%Y %H:%i:%s') AS added, ". REVIEWS_TABLE .".project_id, project_name ".
	"FROM ". REVIEWS_TABLE ." LEFT JOIN ". PROJECTS_TABLE ." USING (project_id) LEFT JOIN ". USERS_TABLE ." ON ". REVIEWS_TABLE .".user_id = ". USERS_TABLE .".user_id ".
	"WHERE review_added ORDER BY review_added DESC LIMIT 5"
);
if (mysql_num_rows($result) > 0) {
	$template->assign_block_vars('submenu', array('TITLE' => 'Latest reviews'));

	while ($review = mysql_fetch_object($result))
	{
		$template->assign_block_vars('submenu.update', array(
			'DATE' => $review->added,
			'LINK' => "<a href=\"". append_sid("showgame.php?project_id=$review->project_id") ."\">$review->username reviewed $review->project_name</a>")
		);
	}
}


// Show 5 latest updated articles

$result = doQuery(
	"SELECT DATE_FORMAT(article_created, '%d-%m-%Y %H:%i:%s') AS created, article_title, article_url ".
	"FROM ". ARTICLES_TABLE ." ORDER BY article_created DESC LIMIT 5"
);
if (mysql_num_rows($result) > 0) {
	$template->assign_block_vars('submenu', array('TITLE' => 'Latest articles'));

	while ($art = mysql_fetch_object($result))
	{
		$template->assign_block_vars('submenu.update', array(
			'DATE' => $art->created,
			'LINK' => "<a href='". htmlspecialchars($art->article_url) ."'>$art->article_title</a>")
		);
	}
}

?>
