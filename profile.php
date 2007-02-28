<?php
include("includes/main.php");

if (empty($HTTP_GET_VARS['user_id']) || $HTTP_GET_VARS['user_id'] == ANONYMOUS) {
	die('<b>Error</b>: No user ID specified');
}

$template->set_filenames(array(
	'body' => 'showprofile_body.tpl')
);


// Get the board config information

$board_config = array();

if(!($result = doQuery("SELECT * FROM ". CONFIG_TABLE))) {
	die("<b>Error:</b> Could not query config information");
}
while ($row = mysql_fetch_array($result)) {
	$board_config[$row['config_name']] = $row['config_value'];
}


// Get the ranks table

$sql = "SELECT * FROM " . RANKS_TABLE . " ORDER BY rank_special, rank_min";
if (!($result = doQuery($sql))) {
	die('<b>Error:</b> Could not obtain ranks information');
}
while ($row = mysql_fetch_array($result)) {
	$ranksrow[] = $row;
}


// Grab all direct user info

$result = doQuery("SELECT * FROM ". USERS_TABLE ." WHERE user_id=". intval($user_id));
if (mysql_num_rows($result) == 0) {
	die('<b>Error:</b> No valid user ID specified');
}
$profiledata = mysql_fetch_array($result);


// Select review count and average review score

$result = doQuery("SELECT COUNT(*) AS count, AVG(review_score) AS avg_score FROM ". REVIEWS_TABLE ." WHERE user_id=". $profiledata['user_id']);
$reviews = mysql_fetch_array($result);


// Calculate the number of days this user has been a member ($memberdays)
// Then calculate their posts per day

$regdate = $profiledata['user_regdate'];
$memberdays = max(1, round((time() - $regdate) / 86400));
$posts_per_day = $profiledata['user_posts'] / $memberdays;


// Get the users percentage of total posts

$total_posts = get_db_stat('postcount');
if ($total_posts && $profiledata['user_posts'] != 0) {
	$posts_percentage = min(100, ($profiledata['user_posts'] / $total_posts) * 100);
} else {
	$posts_percentage = 0;
}


// Determine user avatar link

$avatar_img = '';
if ($profiledata['user_avatar_type'] && $profiledata['user_allowavatar']) {
	switch($profiledata['user_avatar_type'])
	{
		case USER_AVATAR_UPLOAD:
			$avatar_img = ($board_config['allow_avatar_upload']) ? 'forums/'. $board_config['avatar_path'] .'/'. $profiledata['user_avatar'] : '';
			break;
		case USER_AVATAR_REMOTE:
			$avatar_img = ($board_config['allow_avatar_remote']) ? $profiledata['user_avatar'] : '';
			break;
		case USER_AVATAR_GALLERY:
			$avatar_img = ($board_config['allow_avatar_local']) ? $board_config['avatar_gallery_path'] .'/'. $profiledata['user_avatar'] : '';
			break;
	}
}

if (strlen($avatar_img) > 0) {
	$template->assign_block_vars('avatar', array('IMG_URL' => $avatar_img));
}


// Do the contact stuff

$pm_url = append_sid("http://forums.rpgdx.net/privmsg.php?mode=post&amp;" . POST_USERS_URL . "=" . $profiledata['user_id']);

if (!empty($profiledata['user_viewemail']) || ($userdata['session_logged_in'] && $userdata['user_level'] == ADMIN))
{
	$template->assign_block_vars('email', array(
		'URL' => 'mailto:'. $profiledata['user_email'])
	);
}

if (!empty($profiledata['user_website'])) {
	$template->assign_block_vars('website', array(
		'URL' => $profiledata['user_website'])
	);
}

if (!empty($profiledata['user_icq']))
{
	$template->assign_block_vars('icq', array(
		'IMG'    => '<a href="http://wwp.icq.com/'. $profiledata['user_icq'] .'#pager"><img src="http://web.icq.com/whitepages/online?icq='. $profiledata['user_icq'] .'&amp;img=5" width="18" height="18" border="0" align=top /></a>',
		'NUMBER' => $profiledata['user_icq'])
	);
}

if (!empty($profiledata['user_aim'])) {
	$template->assign_block_vars('aim', array(
		'URL'  => 'aim:goim?screenname=' . $profiledata['user_aim'] . '&amp;message=Hello+Are+you+there?',
		'NAME' => $profiledata['user_aim'])
	);
}

if (!empty($profiledata['user_msnm'])) {
	$template->assign_block_vars('msn', array(
		'EMAIL' => $profiledata['user_msnm'])
	);
}

if (!empty($profiledata['user_yim'])) {
	$template->assign_block_vars('yim', array(
		'URL'  => 'http://edit.yahoo.com/config/send_webmesg?.target=' . $profiledata['user_yim'] . '&amp;.src=pg',
		'NAME' => $profiledata['user_yim'])
	);
}

$search_url = append_sid("http://forums.rpgdx.net/search.php?search_author=" . urlencode($profiledata['username']) . "&amp;showresults=posts");


// Determine user rank text and image

$poster_rank = '';
$rank_image = '';
if ($profiledata['user_rank']) {
	for ($i = 0; $i < count($ranksrow); $i++) {
		if ($profiledata['user_rank'] == $ranksrow[$i]['rank_id'] && $ranksrow[$i]['rank_special'])
		{
			$poster_rank = $ranksrow[$i]['rank_title'];
			$rank_image = ($ranksrow[$i]['rank_image']) ? '<img src="'. $ranksrow[$i]['rank_image'] .'" alt="'. $poster_rank .'" title="'. $poster_rank .'" border="0" /><br />' : '';
		}
	}
}
else {
	for($i = 0; $i < count($ranksrow); $i++) {
		if ($profiledata['user_posts'] >= $ranksrow[$i]['rank_min'] && !$ranksrow[$i]['rank_special'])
		{
			$poster_rank = $ranksrow[$i]['rank_title'];
			$rank_image = ($ranksrow[$i]['rank_image']) ? '<img src="'. $ranksrow[$i]['rank_image'] .'" alt="'. $poster_rank .'" title="'. $poster_rank .'" border="0" /><br />' : '';
		}
	}
}




$template->assign_vars(array(
	'USERNAME'         => $profiledata['username'],
	'JOINED'           => create_date('d M Y', $profiledata['user_regdate'], $board_config['board_timezone']),
	'POSTS'            => $profiledata['user_posts'],
	'POSTS_PERCENTAGE' => sprintf("%01.2f", $posts_percentage),
	'POSTS_PER_DAY'    => sprintf("%01.2f", $posts_per_day),
	'RANK'             => $poster_rank,
	'RANK_IMAGE'       => $rank_image,

	'PM_URL'           => $pm_url,
	'SEARCH_URL'       => $search_url,

	'LOCATION'         => ($profiledata['user_from'])      ? $profiledata['user_from']      : '&nbsp;',
	'OCCUPATION'       => ($profiledata['user_occ'])       ? $profiledata['user_occ']       : '&nbsp;',
	'INTERESTS'        => ($profiledata['user_interests']) ? $profiledata['user_interests'] : '&nbsp;',

	'REVIEWS'          => $reviews['count'],
	'AVG_REVIEW_SCORE' => sprintf("%01.1f", $reviews['avg_score'])
	)
);


/* Get all the projects by this user
 */

$result = doQuery("SELECT project_id, project_name FROM ". PROJECTS_TABLE ." WHERE user_id=". $profiledata['user_id'] ." ORDER BY project_name");
if (mysql_num_rows($result) > 0) {
	$template->assign_block_vars('projects', array());

	while ($row = mysql_fetch_array($result))
	{
		$template->assign_block_vars('projects.project', array(
			'NAME' => $row['project_name'],
			'URL'  => append_sid('showgame.php?project_id='. $row['project_id']))
		);
	}
}
else {
	$template->assign_block_vars('no_projects', array());
}


placeHeader(array(array('Profile'), array($profiledata['username'])));
$template->pparse('body');
placeFooter();
?>
