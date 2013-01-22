<?php
include_once("includes/main.php");


function remove_project_icon($project_id)
{
	$row = mysql_fetch_row(doQuery("SELECT project_icon_file FROM ". PROJECTS_TABLE ." WHERE project_id = $project_id"));
	$upload_id = $row[0];

	// If an icon file exists, delete it and set the icon variable of the project to NULL

	if ($upload_id > 0) {
		remove_uploaded_file($upload_id);
		doQuery("UPDATE ". PROJECTS_TABLE ." SET project_icon_file = NULL WHERE project_id = $project_id");
	}
}


if (isset($_POST['upload'])) {$action = 'upload';}
if (isset($_POST['remove'])) {$action = 'remove';}


// Standard authorisation
if (!isset($project_id)) {
	abort_with_error('Somehow you didn\'t specify a project_id.');
}
if ($error = check_access_level(PROJECTS_TABLE, 'project_id', $project_id)) {
	abort_with_error($error);
}
if (!isset($action) || !($action == 'upload' || $action == 'remove')) {
	abort_with_error('No or invalid action specified.');
}

$project_id = intval($project_id);


if ($action == 'upload')
{
	// Put new icon in place.
	if (isset($_FILES['uploaded_icon']) && is_uploaded_file($_FILES['uploaded_icon']['tmp_name']))
	{
		$temp_file = $_FILES['uploaded_icon']['tmp_name'];
		$error = '';

		// Check the image type and size
		$img = getimagesize($temp_file);
		if (!($img[2] == 1 || $img[2] == 2 || $img[2] == 3)) {
			$error .= ((strlen($error) > 0) ? '<br />' : '' ) . 'Uploaded file has wrong type.';
		}
		else if ($img[0] != 32 || $img[1] != 32) {
			$error .= ((strlen($error) > 0) ? '<br />' : '' ) . "Image dimensions not allowed ($img[0]x$img[1], but should be 32x32).";
		}
		// Check the file size
		if ($_FILES['uploaded_icon']['size'] > (10 * 1024)) {
			$error .= ((strlen($error) > 0) ? '<br />' : '' ) . "Uploaded file too large (". round($_FILES['uploaded_icon']['size'] / 1024) ." kb exceeds ". 10 ." kb) ";
		}

		if (strlen($error) > 0) {
			abort_with_error($error);
		}

		$extensions = array(0, '.gif', '.jpg', '.png');
		$extension = $extensions[$img[2]];

		// Add the icon file to the database
		$upload_id = add_uploaded_file($temp_file, 'icon', $extension);

		// Remove the previous icon from the project
		remove_project_icon($project_id);

		// Assign the icon to the project
		doQuery("UPDATE ". PROJECTS_TABLE ." SET project_icon_file = $upload_id WHERE project_id = $project_id");
	}
	else
	{
		abort_with_error('No file seems to be uploaded.');
	}
}


if ($action == 'remove')
{
	// Remove the current icon
	remove_project_icon($project_id);
}

header("Location: editproject.php?project_id=$project_id");
?>
