<?php
include("includes/main.php");
require_once $phpbb_root_path . 'includes/functions_display.php';

$user_id = (int) ($_GET['user_id'] ?? 0);
if ($user_id == ANONYMOUS) {
    $mapped = rpgdx_db_fetch_row(doQuery("SELECT user_id FROM rpgdx_user_id_map WHERE legacy_user_id = 1"));
    if ($mapped && $mapped[0] != ANONYMOUS) {
        header('Location: profile.php?user_id=' . (int) $mapped[0], true, 301);
        exit;
    }
}
if (!$user_id || $user_id == ANONYMOUS) {
    abort_with_error('No user ID specified.');
}
$profiledata = rpgdx_db_fetch_assoc(doQuery("SELECT * FROM " . USERS_TABLE . " WHERE user_id = $user_id AND user_type IN (" . USER_NORMAL . ", " . USER_FOUNDER . ", " . USER_INACTIVE . ")"));
if (!$profiledata) {
    abort_with_error('No valid user ID specified.');
}
$fields = rpgdx_db_fetch_assoc(doQuery("SELECT * FROM " . PROFILE_FIELDS_DATA_TABLE . " WHERE user_id = $user_id")) ?: array();
$reviews = rpgdx_db_fetch_assoc(doQuery("SELECT COUNT(*) AS count, AVG(review_score) AS avg_score FROM " . REVIEWS_TABLE . " WHERE user_id = $user_id"));
$memberdays = max(1, round((time() - $profiledata['user_regdate']) / 86400));
$total_posts = (int) $config['num_posts'];
$posts_percentage = $total_posts ? min(100, $profiledata['user_posts'] / $total_posts * 100) : 0;
$rank = phpbb_get_user_rank($profiledata, $profiledata['user_posts']);

$template->set_filenames(array('body' => 'showprofile_body.tpl'));
$avatar = phpbb_get_user_avatar($profiledata, 'USER_AVATAR');
if ($avatar) {
    $template->assign_block_vars('avatar', array('HTML' => $avatar));
}
if (($profiledata['user_allow_viewemail'] && $auth->acl_get('u_sendemail')) || $userdata['user_level'] == ADMIN) {
    $template->assign_block_vars('email', array('URL' => append_sid('forums/memberlist.php', 'mode=email&amp;u=' . $user_id)));
}
$website = $fields['pf_phpbb_website'] ?? '';
if ($website && preg_match('#^https?://#i', $website)) {
    $template->assign_block_vars('website', array('URL' => htmlspecialchars($website, ENT_QUOTES, 'UTF-8')));
}
foreach (array('phpbb_icq' => 'icq', 'phpbb_yahoo' => 'yim', 'phpbb_aim' => 'aim', 'phpbb_msnm' => 'msn') as $field => $block) {
    $value = htmlspecialchars($fields['pf_' . $field] ?? '', ENT_QUOTES, 'UTF-8');
    if ($value !== '') {
        $template->assign_block_vars($block, array('NUMBER' => $value, 'NAME' => $value, 'EMAIL' => $value, 'IMG' => '', 'URL' => append_sid('forums/memberlist.php', 'mode=viewprofile&amp;u=' . $user_id)));
    }
}
$template->assign_vars(array(
    'USERNAME' => $profiledata['username'],
    'JOINED' => $user->format_date($profiledata['user_regdate'], 'd M Y'),
    'POSTS' => $profiledata['user_posts'],
    'POSTS_PERCENTAGE' => sprintf('%01.2f', $posts_percentage),
    'POSTS_PER_DAY' => sprintf('%01.2f', $profiledata['user_posts'] / $memberdays),
    'RANK' => $rank['title'] ?? '',
    'RANK_IMAGE' => $rank['img'] ?? '',
    'PM_URL' => append_sid('forums/ucp.php', 'i=pm&amp;mode=compose&amp;u=' . $user_id),
    'SEARCH_URL' => append_sid('forums/search.php', 'author_id=' . $user_id . '&amp;sr=posts'),
    'LOCATION' => $fields['pf_phpbb_location'] ?? '&nbsp;',
    'OCCUPATION' => $fields['pf_phpbb_occupation'] ?? '&nbsp;',
    'INTERESTS' => $fields['pf_phpbb_interests'] ?? '&nbsp;',
    'REVIEWS' => $reviews['count'],
    'AVG_REVIEW_SCORE' => sprintf('%01.1f', $reviews['avg_score'] ?? 0),
));

/* Get all the projects by this user
 */

$result = doQuery("SELECT project_id, project_name FROM ". PROJECTS_TABLE ." WHERE user_id=". $profiledata['user_id'] ." ORDER BY project_name");
if (rpgdx_db_num_rows($result) > 0) {
	$template->assign_block_vars('projects', array());

	while ($row = rpgdx_db_fetch_array($result))
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
