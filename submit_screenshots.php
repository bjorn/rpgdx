<?php
include_once("includes/main.php");

// Standard authorisation
if (!isset($project_id)) {
	abort_with_error('Somehow you didn\'t specify a project_id.');
}
if ($error = check_access_level(PROJECTS_TABLE, 'project_id', $project_id)) {
	abort_with_error($error);
}
if (!isset($action) || !($action == 'add' || $action == 'remove')) {
	abort_with_error('No or invalid action specified.');
}


if ($action == 'add') {
	// Check if this project has already reached its screenshot limit
	$row = mysql_fetch_array(doQuery(
		"SELECT COUNT(*) AS scr_count FROM ". PROJECT_SCREENSHOTS_TABLE ." WHERE project_id=$project_id"
	));
	if ($row['scr_count'] >= $rpgdx_config['screenshots_limit']) {
		abort_with_error('We\'re sorry, but this game as already reached the screenshot limit ('. $rpgdx_config['screenshots_limit'] .').');
	}
	

	// Do the adding of the screenshots
	if (isset($_FILES['uploaded_screenshot']) && is_uploaded_file($_FILES['uploaded_screenshot']['tmp_name']))
	{
		$temp_file = $_FILES['uploaded_screenshot']['tmp_name'];
		$error = '';

		// Check caption
		if (!(isset($_POST['screenshot_caption']) && strlen($_POST['screenshot_caption']) > 0)) {
			$error .= ((strlen($error) > 0) ? '<br />' : '' ) . 'No caption specified.';
		}
		else if (strlen($_POST['screenshot_caption']) > 50) {
			$error .= ((strlen($error) > 0) ? '<br />' : '' ) . 'Caption exceeds maximum length (50 characters).';
		}
		// Check the image type and size
		$img = getimagesize($temp_file);
		if (!($img[2] == 1 || $img[2] == 2 || $img[2] == 3)) {
			$error .= ((strlen($error) > 0) ? '<br />' : '' ) . 'Uploaded file has wrong type.';
		}
		else if ($img[1] > $img[0] || $img[0] < 10 || $img[1] < 10 || $img[0] > 1600 || $img[1] > 1600) {
			$error .= ((strlen($error) > 0) ? '<br />' : '' ) . 'Image dimensions not allowed (height cannot exceed width, and both should be between 10 and 1600 pixels).';
		}
		// Check the file size
		if ($_FILES['uploaded_screenshot']['size'] > (150 * 1024)) {
			$error .= ((strlen($error) > 0) ? '<br />' : '' ) . "Uploaded file too large (". round($_FILES['uploaded_screenshot']['size'] / 1024) ." kb exceeds ". 150 ." kb) ";
		}

		if (strlen($error) > 0) {
			abort_with_error($error);
		}

		$extensions = array(0, '.gif', '.jpg', '.png');
		$extension = $extensions[$img[2]];

		// Add the screenshot file to the database
		$upload_id = add_uploaded_file($temp_file, 'screenshot', $extension);

		// Create a thumbnail
		create_thumbnail('uploads/screenshot' . $upload_id . $extension);

		// Add screenshot to the database
		doQuery(
			"INSERT INTO ". PROJECT_SCREENSHOTS_TABLE ." (project_id, upload_id, screenshot_title) VALUES ".
			"($project_id, $upload_id, '". $_POST['screenshot_caption'] ."')"
		);
	}
	else
	{
		abort_with_error('No file seems to be uploaded.');
	}
}


if ($action == 'remove' && isset($_POST['screenshots']))
{
	// Do the removing of the screenshots
	foreach($_POST['screenshots'] as $screenshot_id)
	{
		// Validate screenshot id
		if (!intval($screenshot_id) > 0) {
			abort_with_error('Error, invalid screenshot id: '. $screenshot_id);
		}
		
		$row = mysql_fetch_array(doQuery(
			"SELECT project_id, ". PROJECT_SCREENSHOTS_TABLE .".upload_id, upload_ext ".
			"FROM ". PROJECT_SCREENSHOTS_TABLE ." LEFT JOIN ". UPLOADS_TABLE ." USING (upload_id) ".
			"WHERE screenshot_id = ". intval($screenshot_id)));

		// Check whether this user is authorised to change the related game.
		if ($error = check_access_level(PROJECTS_TABLE, 'project_id', $row['project_id'])) {
			abort_with_error('Authorisation error while trying to remove screenshot (screenshot_id: '. intval($screenshot_id) .').');
		}

		// When the above has succeeded, remove the screenshot and its file.
		doQuery("DELETE FROM ". PROJECT_SCREENSHOTS_TABLE ." WHERE screenshot_id = ". intval($screenshot_id));
		remove_uploaded_file($row['upload_id']);

		// Remove the thumbnail
		unlink('uploads/screenshot' . $row['upload_id'] . $row['upload_ext'] . '.thumb.jpg');
	}
}

header("Location: editproject.php?project_id=$project_id");
?>