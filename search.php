<?php
include("includes/main.php");

$template->set_filenames(array(
	'body' => 'search_body.tpl')
);


$progress_options = "<option value=0 selected></option>\n";
$progresses = doQuery("SELECT status_id, status_title FROM ". PROJECT_STATUSSES_TABLE ." ORDER BY status_perc");
while ($progress = mysql_fetch_object($progresses)) {
	$progress_options .= "<option value=\"$progress->status_id\"". ((isset($search_status) && $search_status == $progress->status_id) ? " selected" : "") .">$progress->status_title</option>\n";
}


$template->assign_block_vars('form', array(
	'QUERY_FIELD'     => 'search',
	'STATUS_FIELD'    => 'search_status',
	'DOWNLOAD_FIELD'  => 'search_download',
	'REVIEWING_FIELD' => 'search_reviewing',
	'ACTION'          => append_sid('search.php'),
	'STATUS_OPTIONS'  => $progress_options
));


if (isset($search)) {
	// Modify search string
	$search = trim($search);
	$search = str_replace(array('(', ')', '<', '>', '\\', '/', '='), "", $search);
	$search = str_replace(array('%', '_'), array('\%', '\_'), $search);

	// Pick keywords
	$candidate_keywords = explode(' ', $search, 5);
	$keywords = array();
	foreach ($candidate_keywords as $cand_keyword) {
		if (strlen($cand_keyword) > 1) {
			$keywords[] = $cand_keyword;
		}
	}


	// Generate search condition
	if (sizeof($keywords) > 0) {
		$fields = array('project_description', 'project_name');
		foreach ($fields as $field)
			$conditions[] = "(($field LIKE '%". implode("%') * ($field LIKE '%", $keywords) ."%'))";
		$condition = implode(' + ', $conditions);
	} else {
		$condition = "1";
	}

	// Include download, reviewing and status demands
	if (isset($search_download)) $condition = "($condition) * IF(LENGTH(download) > 0, 1, 0)";
	if (isset($search_reviewing)) $condition = "($condition) * IF(project_allow_review, 1, 0)";
	if (isset($search_status) && $search_status > 0) $condition = "($condition) * IF(progress_id = $search_status, 1, 0)";

	$query = 
		"SELECT ".
			"project_name, project_description, ". PROJECTS_TABLE .".project_id, project_bbcode_uid, username, ".
			"DATE_FORMAT(project_last_update, '%d-%m-%Y') AS updated, ".
			"IFNULL(AVG(review_score), -1) AS rating, ".
			UPLOAD_FILENAME ." AS icon_file, ".
			"$condition AS result ".
		"FROM (". 
			PROJECTS_TABLE ." ".
			"LEFT JOIN ". USERS_TABLE   ." ON (". PROJECTS_TABLE .".user_id    = ". USERS_TABLE   .".user_id)) ".
			"LEFT JOIN ". REVIEWS_TABLE ." ON (". PROJECTS_TABLE .".project_id = ". REVIEWS_TABLE .".project_id) ".
			"LEFT JOIN ". UPLOADS_TABLE ." ON (project_icon_file = upload_id) ".
		"WHERE $condition > 0 GROUP BY ". PROJECTS_TABLE .".project_id ORDER BY result DESC, project_name";


	$keywords_implode = implode(' ', $keywords);

	$template->assign_vars(array(
		'SEARCH_QUERY'     => $keywords_implode,
		'SEARCH_DOWNLOAD'  => (isset($search_download)) ? 'CHECKED' : '',
		'SEARCH_REVIEWING' => (isset($search_reviewing)) ? 'CHECKED' : '',
		'SEARCH_MYSQL'     => $query,
	));

	if (strlen($keywords_implode) > 0) {
		if ($userdata['session_logged_in']) {
			add_log("A user <!-- ". $userdata['username'] ." --> searched for \"$keywords_implode\".");
		} else {
			add_log("A guest searched for \"$keywords_implode\".");
		}
	}



	// Perform the actual search query
	$result = doQuery($query);

	// Display the results
	if (mysql_num_rows($result) > 0) {
		$template->assign_block_vars('rpglist', array(
			'NR_SEARCH_RESULTS' => mysql_num_rows($result)
		));

		while ($row = mysql_fetch_object($result)) {
			$desc = shorten_text($row->project_description, 256);

			$template->assign_block_vars('rpglist.rpg', array(
				'LINK'        => append_sid("showgame.php?project_id=$row->project_id"),
				'NAME'        => highlight($row->project_name, $keywords),
				'POSTER'      => $row->result,
				'LAST_UPDATE' => $row->updated,
				'BRIEF_DESC'  => highlight(prepare_for_display($desc, $row->project_bbcode_uid, 'bbcode_rip.tpl', false, false), $keywords))
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
}

placeHeader(array(array("Search Projects", 'search.php')));
$template->pparse('body');
placeFooter();
?>