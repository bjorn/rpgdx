<?php
$override_theme = 'admin';
include("./includes/main.php");

$template->assign_vars(array(
	'U_RPGDX'       => append_sid("index.php"),
	'U_FORUMS'      => append_sid("http://forums.rpgdx.net/"),
	'U_LOG'         => append_sid("admin.php?page=log"),
	'U_STATS'       => append_sid("admin.php?page=stats"),
	'U_PROJECTS'    => append_sid("admin.php?page=list&class=project"),
	'U_ARTICLES'    => append_sid("admin.php?page=list&class=article"),
	'U_REVIEWS'     => append_sid("admin.php?page=list&class=review"),
	'U_USERS'       => append_sid("admin.php?page=list&class=member"),
    'U_NEWSLETTER'  => append_sid("admin.php?page=newsletter"))
);

if (!($userdata['session_logged_in'] && $userdata['user_level'] == 1)) {
	placeHeader(array(array("Admin section")));
  $template->set_filenames(array('body' => 'acces_denied.tpl'));
  $template->pparse('body');
  placeFooter();
  exit();
}


if (!isset($page)) {$page = 'log';}

if ($page == 'log')
{
	$subsection = "Information";
	$subpage = "Log";

	$result = doQuery("SELECT log_text, DATE_FORMAT(log_time, '%d-%m %H:%i') AS log_time FROM ". LOG_TABLE ." ORDER BY ". LOG_TABLE .".log_time DESC");

	$template->set_filenames(array(
		'body' => 'log_body.tpl')
	);

	while ($row = mysql_fetch_object($result))
	{
        $template->assign_block_vars('log', array(
            'TEXT' => $row->log_text,
            'TIME' => $row->log_time)
        );
    }
}
else if ($page == 'stats')
{
    $subsection = "Information";
    $subpage = "Statistics";

	$usercount = get_db_stat('usercount');
	$activeusercount = get_db_stat('activeusercount');
	$projectcount = get_db_stat('projectcount');
	$reviewcount = get_db_stat('reviewcount');
	$articlecount = get_db_stat('articlecount');

	$row = mysql_fetch_object(doQuery("SELECT username, user_posts FROM ".
        USERS_TABLE ." WHERE username != 'Anonymous' ".
        "ORDER BY user_posts DESC LIMIT 1"));
	$stats->highest_postcount_username = $row->username;
	$stats->highest_postcount = $row->user_posts;
	$row = mysql_fetch_object(doQuery("SELECT COUNT(project_id) AS ".
        "project_count FROM ". PROJECTS_TABLE ." WHERE progress_id = 6"));
	$stats->finished_project_count = $row->project_count;
	$row = mysql_fetch_object(doQuery("SELECT AVG(review_score) AS ".
        "average_review_score FROM ". REVIEWS_TABLE));
	$stats->average_review_score = $row->average_review_score;


	$template->set_filenames(array(
		'body' => 'stats_body.tpl'
	));

	$template->assign_vars(array(
		'MEMBER_COUNT'               => $usercount,
		'ACTIVE_MEMBER_COUNT'        => $activeusercount,
		'ACTIVE_MEMBERS_PERC'        =>
            round(($activeusercount / $usercount) * 100),
		'HIGHEST_POSTCOUNT_USERNAME' => $stats->highest_postcount_username,
		'HIGHEST_POSTCOUNT'          => $stats->highest_postcount,
        'PROJECT_COUNT'              => $projectcount,
        'FINISHED_PROJECT_COUNT'     => $stats->finished_project_count,
        'FINISHED_PROJECTS_PERC'     =>
            round(($stats->finished_project_count / $projectcount) * 100),
        'REVIEW_COUNT'               => $reviewcount,
        'AVERAGE_REVIEW_SCORE'       =>
            sprintf("%01.2f", $stats->average_review_score),
        'ARTICLE_COUNT'              => $articlecount
    ));
}
else if ($page == 'newsletter')
{
	$subsection = "Information";
	$subpage = "Newsletter";
    
    // Newsletter template
    $template->set_filenames(array(
        'body' => 'newsletter_body.tpl'
    ));

    $new_topics = 0;
    $new_posts = 0;
    $new_reviews = 0;
    $new_projects = 0;
    $new_members = 0;

    $template->assign_vars(array(
        'NEW_TOPICS' => $new_topics,
        'NEW_POSTS' => $new_posts,
        'NEW_REVIEWS' => $new_reviews,
        'NEW_PROJECTS' => $new_projects,
        'NEW_MEMBERS' => $new_members
    ));
}
else if ($page == 'list')
{
    $subsection = "Administration";

    if ($class == 'project') {
        $subpage = "Projects";
        $result = doQuery(
            'SELECT '.
            'project_id, '.
            'CONCAT("<a href=\"showgame.php?project_id=", project_id ,"\">", project_name, "</a>") AS project_name, '.
				'project_added, '.
				'project_last_update, '.
				'CONCAT("<a href=\"editproject.php?project_id=", project_id ,"\"><img src=\"images/icon_edit.png\" border=\"0\"></a>") AS e, '.
				'CONCAT("<a href=\"editproject_general.php?action=remove&project_id=", project_id ,"\"><img src=\"images/icon_nack.png\" border=\"0\"></a>") AS d '.
			'FROM '. PROJECTS_TABLE .' '.
			'ORDER BY project_id');
	}
	else if ($class == 'article') {
		$subpage = "Articles";
		$result = doQuery(
			'SELECT '.
				'article_id, '.
				'CONCAT("<a href=\"", article_url ,"\">", article_title, "</a>") AS article_title, '.
				'article_created, '.
				'CONCAT("<a href=\"editarticle.php?action=edit&article_id=", article_id ,"\"><img src=\"images/icon_edit.png\" border=\"0\"></a>") AS e, '.
				'CONCAT("<a href=\"editarticle.php?action=remove&article_id=", article_id ,"\"><img src=\"images/icon_nack.png\" border=\"0\"></a>") AS d '.
			'FROM '. ARTICLES_TABLE .' '.
			'ORDER BY article_id');
	}
	else if ($class == 'review') {
		$subpage = "Reviews";
		$result = doQuery(
			'SELECT '.
				'review_id, '.
				'CONCAT("<a href=\"showgame.php?project_id=", '. REVIEWS_TABLE .'.project_id ,"\">", project_name, "</a>") AS project_name, '.
				'review_score, '.
				'review_added, '.
				'CONCAT("<a href=\"editreview.php?action=edit&review_id=", review_id ,"\"><img src=\"images/icon_edit.png\" border=\"0\"></a>") AS e, '.
				'CONCAT("<a href=\"editreview.php?action=remove&review_id=", review_id ,"\"><img src=\"images/icon_nack.png\" border=\"0\"></a>") AS d '.
			'FROM '. REVIEWS_TABLE .' '.
				'LEFT JOIN '. PROJECTS_TABLE .' USING(project_id) '.
			'ORDER BY review_id');
	}
	else if ($class == 'member') {
		$subpage = "Members";
		$result = doQuery(
			'SELECT '.
				'user_id, '.
				'CONCAT("<a href=\"profile.php?user_id=", user_id ,"\">", username, "</a>") AS username, '.
				'user_email, '.
				'user_posts, '.
				'theme_name '.
			'FROM '. USERS_TABLE .' '.
				'LEFT JOIN '. RPGDX_THEMES_TABLE .' ON user_theme = theme_id '.
			'ORDER BY user_id');
	}

	$row = mysql_fetch_assoc($result);
	if ($row) {
		$template->assign_block_vars('header', array());
		foreach ($row as $key => $col_value) {
			$template->assign_block_vars('header.column', array('TITLE' => strtoupper($key)));
		}
	}

	while ($row) {
		$template->assign_block_vars('row', array());
		foreach ($row as $key => $col_value) {
			$template->assign_block_vars('row.cell', array('VALUE' => $col_value));
		}
		$row = mysql_fetch_assoc($result);
	}

	$template->set_filenames(array(
		'body' => 'list_body.tpl'
	));
}


// Put down the page

placeHeader(array(array($subsection), array($subpage)));
$template->pparse('body');
placeFooter();

?>
