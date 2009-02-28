<?php
include("includes/main.php");

$template->set_filenames(array(
	'body' => 'showcategory_body.tpl')
);

if (isset($cat) && $cat > 0) {
	if ($result = doQuery("SELECT * FROM ". PROJECT_TYPES_TABLE ." WHERE type_id = $cat")) {
		$genre = mysql_fetch_object($result);
	}

	$template->assign_vars(array(
		'TYPE_DESCRIPTION' => $genre->type_description
	));
}

if (!(isset($genre) && $genre->type_id > 0)) {
	abort_with_error("No such category.");
}


if (!isset($order)) {
	if ($userdata['session_logged_in']) {
		if ($userdata['user_projects_sort_method'] > 0) {
			$order = $userdata['user_projects_sort_method'];
		} else {
			$result = doQuery("SELECT user_projects_last_sort_method AS sort_method FROM ". USERS_TABLE. " WHERE user_id=". $userdata['user_id']);
			$row = mysql_fetch_object($result);
			$order = $row->sort_method;
		}
	} else {
		$order = PSORT_RATING;
	}
}
else {
	if ($userdata['session_logged_in'] && $order >= 1 && $order <= 4) {
		doQuery("UPDATE ". USERS_TABLE ." SET user_projects_last_sort_method = ". intval($order) ." WHERE user_id=". $userdata['user_id']);
	}
}

$sortmode_options = '';
$sortmode_options .= '<option value='. PSORT_NAME   . (($order == PSORT_NAME)   ? ' selected' : '') .'>Name';
$sortmode_options .= '<option value='. PSORT_RATING . (($order == PSORT_RATING) ? ' selected' : '') .'>Rating';
$sortmode_options .= '<option value='. PSORT_UPDATE . (($order == PSORT_UPDATE) ? ' selected' : '') .'>Last update';

if      ($order == PSORT_UPDATE) $order_part = 'project_last_update DESC';
else if ($order == PSORT_USER)   $order_part = 'username';
else if ($order == PSORT_RATING) $order_part = 'rating DESC';
else                             $order_part = 'project_name';

// Select all RPGs of the current category
$result = doQuery(
	"SELECT ".
		"project_name, project_summary, ". PROJECTS_TABLE .".project_id, username, ".
		"DATE_FORMAT(project_last_update, '%d-%m-%Y') AS updated, ".
		"IFNULL(AVG(review_score), -1) AS rating, ".
		UPLOAD_FILENAME ." AS icon_file ".
	"FROM (". 
		PROJECTS_TABLE ." ".
		"LEFT JOIN ". USERS_TABLE   ." ON (". PROJECTS_TABLE .".user_id    = ". USERS_TABLE   .".user_id)) ".
		"LEFT JOIN ". REVIEWS_TABLE ." ON (". PROJECTS_TABLE .".project_id = ". REVIEWS_TABLE .".project_id) ".
		"LEFT JOIN ". UPLOADS_TABLE ." ON (project_icon_file = upload_id) ".
	"WHERE project_type = $genre->type_id GROUP BY ". PROJECTS_TABLE .".project_id ORDER BY $order_part"
);

if (mysql_num_rows($result) > 0) {
	$template->assign_block_vars('rpglist', array(
		'HIDDEN_FORM_DATA'  => '<input type="hidden" name="cat" value="'. $cat .'">',
		'SORTMODE_ACTION'   => 'showcategory.php',
		'SORTMODE_NAME'     => 'order',
		'SORTMODE_OPTIONS'  => $sortmode_options)
	);

	while ($row = mysql_fetch_object($result)) {
		$template->assign_block_vars('rpglist.rpg', array(
			'LINK'        => append_sid("showgame.php?project_id=$row->project_id"),
			'NAME'        => $row->project_name,
			'POSTER'      => $row->username,
			'LAST_UPDATE' => $row->updated,
			'RATING'      => round($row->rating * 10),
			'BRIEF_DESC'  => $row->project_summary)
		);
		if (strlen($row->icon_file) > 0) {
			$template->assign_block_vars('rpglist.rpg.icon', array(
				'WIDTH'       => 32,
				'HEIGHT'      => 32,
				'URL'         => $row->icon_file)
			);
		}
		if ($row->rating > -1) {
			$template->assign_block_vars('rpglist.rpg.rating', array(
				'SCORE'       => round($row->rating * 10)
			));
		}
	}
}
else
{
	$template->assign_block_vars('zeroprojects', array());
}

placeHeader(array(array($genre->type_title_plural, append_sid('showcategory.php?cat='. $genre->type_id))));

$template->pparse('body');

placeFooter();
?>