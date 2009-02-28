<?php
include("./includes/main.php");


/* Query for news
 */

$result = doQuery(
	"SELECT ".
  "  news_id, news_message, news_title, news_bbcode_uid, ". NEWS_TABLE .".user_id, ".
  "  DATE_FORMAT(news_posted, '%d-%m-%Y %H:%i') AS news_posted_f, ".
  "  ". NEWS_TABLE .".user_id, username AS news_poster ".
	"FROM ". NEWS_TABLE ." LEFT JOIN ". USERS_TABLE ." USING (user_id) ".
	"ORDER BY news_posted DESC LIMIT 15"
);


/* Put news on the page
 */

$template->set_filenames(array(
	'body' => 'news_body.tpl')
);

while ($row = mysql_fetch_object($result))
{
	$editlink = '';
	if ($userdata['session_logged_in'] && ($row->user_id == $userdata['user_id'] || $userdata['user_level'] == ADMIN)) {
		$editlink = '[<a href="'. append_sid('editnews.php?news_id='. $row->news_id .'&action=edit') .'">edit</a>]';
	}

	$template->assign_block_vars('newspost', array(
		'POSTER_NAME'        => $row->news_poster,
		'POSTER_PROFILE_URL' => append_sid('profile.php?user_id='. $row->user_id),
		'POST_DATE'          => $row->news_posted_f,
		'POST_ID'            => $row->news_id,
		'MESSAGE'            => prepare_for_display($row->news_message, $row->news_bbcode_uid),
    'TITLE'              => prepare_for_display($row->news_title, ''),
		'EDIT_LINK'          => $editlink)
	);
}


placeHeader(array(array("News", append_sid('index.php'))));
$template->pparse('body');
placeFooter();
?>
