<?php
include("includes/main.php");

if (!isset($project_id)) {
	abort_with_error('Somehow you didn\'t specify a project_id.');
}
if ($error = check_access_level(PROJECTS_TABLE, 'project_id', $project_id)) {
	abort_with_error($error);
}


$template->set_filenames(array(
	'body' => 'editproject_body.tpl')
);


// Get information about the selected project

$result = doQuery("SELECT project_id, project_name FROM ". PROJECTS_TABLE ." WHERE project_id=$project_id");
$row = mysql_fetch_object($result);

$project_id = $row->project_id;
$project_name = $row->project_name;
//$project_icon_file = $row->project_icon_file;

$template->assign_vars(array(
	'EDIT_GENERAL_URL'             => 'editproject_general.php?action=edit&project_id='. $project_id,
	'SCREENSHOT_UPLOAD_TARGET'     => 'submit_screenshots.php?action=add&project_id='. $project_id,
	'SCREENSHOT_REMOVE_TARGET'     => 'submit_screenshots.php?action=remove&project_id='. $project_id,
	'ICON_UPLOAD_TARGET'           => 'submit_icon.php?project_id='. $project_id,
	'ICON_REMOVE_TARGET'           => 'submit_icon.php?project_id='. $project_id,
	'PROJECT_PAGE_URL'             => 'showgame.php?project_id='. $project_id,
	
	'PROJECT_ID_FIELD'             => 'project_id',
	'SCREENSHOT_FILE_FIELD'        => 'uploaded_screenshot',
	'SCREENSHOT_CAPTION_FIELD'     => 'screenshot_caption',
	'SCREENSHOT_CAPTION_MAXLENGTH' => '50',
	'ICON_FILE_FIELD'              => 'uploaded_icon')
);


// Add the currently uploaded screenshots

$result = doQuery(
	"SELECT screenshot_id, ". UPLOAD_FILENAME ." AS screenshot_url, screenshot_title ".
	"FROM ". PROJECT_SCREENSHOTS_TABLE ." LEFT JOIN rpgdx_uploads USING (upload_id) ".
	"WHERE project_id = $project_id ".
	"ORDER BY screenshot_id"
);

if (mysql_num_rows($result) > 0)
{
	$template->assign_block_vars('screenshots', array());

	while ($row = mysql_fetch_object($result))
	{
		$url = $row->screenshot_url;
		$size = getimagesize($url);

		$template->assign_block_vars('screenshots.shot', array(
			'URL'         => $url.'.thumb.jpg',
			'CAPTION'     => $row->screenshot_title,
			'DIMENSIONS'  => $size[0] ."x". $size[1],
			'CHECK_FIELD' => 'screenshots[]',
			'ID'          => $row->screenshot_id)
		);
	}
}


// Add the currently present icon (if present)

$row = mysql_fetch_row(doQuery("SELECT project_icon_file FROM ". PROJECTS_TABLE ." WHERE project_id = $project_id"));
$icon_upload_id = $row[0];

if ($icon_upload_id > 0)
{
	$row = mysql_fetch_array(doQuery("SELECT ". UPLOAD_FILENAME ." AS url FROM ". UPLOADS_TABLE ." WHERE upload_id = $icon_upload_id"));
	$template->assign_block_vars('icon', array(
		'WIDTH'       => 32,
		'HEIGHT'      => 32,
		'URL'         => $row['url'])
	);
}



placeHeader(array(array("User page", ''), array("Editing $project_name")));
$template->pparse('body');
placeFooter();
?>