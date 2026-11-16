<?php

define('IN_RPGDX', true);
require_once __DIR__ . '/phpbb.php';

include_once("includes/constants.php");
include_once("includes/functions.php");
include_once("includes/database.php");
include_once("includes/page.php");
include_once("includes/bbcode.php");

$time_start = getmicrotime();

$preferences = rpgdx_db_fetch_assoc(doQuery("SELECT * FROM " . USER_PREFERENCES_TABLE . " WHERE user_id = " . (int) $userdata['user_id']));
$userdata = array_merge($userdata, $preferences ?: array(
    'user_theme' => 0,
    'user_projects_sort_method' => 0,
    'user_projects_last_sort_method' => PSORT_RATING,
));


/*
 * Create a new instance of the template class.
 */
if ($userdata['session_logged_in'] && $userdata['user_theme'] > 0 && empty($override_theme)) {
  $row = rpgdx_db_fetch_row(doQuery("SELECT theme_dir FROM ". RPGDX_THEMES_TABLE ." WHERE theme_id=". $userdata['user_theme']));
  $template_dir = $row[0] ?? 'modern';
} elseif (empty($override_theme)) {
  $template_dir = 'modern';
}
else {
  $template_dir = $override_theme;
}
if (!is_dir(__DIR__ . "/../templates/$template_dir")) {
    $template_dir = 'modern';
}
$template = new \Rpgdx\Legacy\Template("templates/$template_dir");
$site_host = strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? '')[0]);
if (in_array($site_host, array('rpgdx.net', 'www.rpgdx.net'), true)) {
    $template->assign_block_vars('production_analytics', array());
}




// ============================================================
// Below here lots of variables are added to the template class
// ============================================================

$loginbar_text = "";

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
	$loginbar_text .= " [<a href=\"". append_sid("login.php?logout=true", "sid=" . $user->session_id) ."\">log out</a>]";
}
else
{
	$loginbar_text .= "Not logged in.";
	$loginbar_text .= " [<a href=\"". append_sid("login.php") ."\">log in</a>]";
	$loginbar_text .= " [<a href=\"". append_sid("register.php"). "\">register</a>]";
}


$template->assign_vars(array(
  'TEMPL_DIR'       => "templates/$template_dir/",
  'STYLE_VERSION'   => @filemtime(__DIR__ . "/../templates/$template_dir/stylesheet.css") ?: 0,

  'TITLE'           => 'RPGDX',
  'LOGINBAR_TEXT'   => $loginbar_text,

  'U_NEWS'          => append_sid('index.php'),
  'U_FORUMS'        => append_sid('/forums'),
  'U_WIKI'          => append_sid('wiki/index.php/Main_Page'),
  'U_ABOUT'         => append_sid('about.php'),
  'U_CONTESTS'      => append_sid('showcontests.php'),
  'U_SEARCH'        => append_sid('search.php'),

  'S_LOGIN_ACTION'  => append_sid('forums/ucp.php', 'mode=login'),

  'HIDE_URL'        => 'onmouseover="status=\'\'; return true;"',
  'BBCODE_ON'       => 'bbcode enabled',

  'MAX_SCREENSHOTS' => $rpgdx_config['screenshots_limit'])
);



/* Add the different RPG types and article categories to the menu.
 */

// Get counts from different genres
$result = doQuery("SELECT project_type, COUNT(*) AS number FROM ". PROJECTS_TABLE ." GROUP BY project_type");
while ($row = rpgdx_db_fetch_object($result)) {
	$rpg_genre[$row->project_type] = $row->number;
}

// Add each RPG type
$result = doQuery("SELECT * FROM ". PROJECT_TYPES_TABLE);
while ($row = rpgdx_db_fetch_object($result))
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
while ($row = rpgdx_db_fetch_object($result)) {
	$rpg_category[$row->article_type] = $row->number;
}

// Add each article category
$result = doQuery("SELECT * FROM ". ARTICLE_TYPES_TABLE);
while ($row = rpgdx_db_fetch_object($result))
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
if (rpgdx_db_num_rows($result) > 0) {
	$template->assign_block_vars('submenu', array('TITLE' => 'Game updates'));

	while ($game = rpgdx_db_fetch_object($result))
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
if (rpgdx_db_num_rows($result) > 0) {
	$template->assign_block_vars('submenu', array('TITLE' => 'Latest reviews'));

	while ($review = rpgdx_db_fetch_object($result))
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
if (rpgdx_db_num_rows($result) > 0) {
	$template->assign_block_vars('submenu', array('TITLE' => 'Latest articles'));

	while ($art = rpgdx_db_fetch_object($result))
	{
		$template->assign_block_vars('submenu.update', array(
			'DATE' => $art->created,
			'LINK' => "<a href='". htmlspecialchars($art->article_url) ."'>$art->article_title</a>")
		);
	}
}

?>
