<?php
include_once("includes/main.php");

if ($userdata['session_logged_in']) {
  doQuery(
		"UPDATE ". USERS_TABLE ." SET ".
			"user_theme=". intval($user_theme) .", ".
			"user_projects_sort_method=". intval($sort_method) ." ".
		"WHERE user_id=". $userdata['user_id']
	);
}
else
{
  abort_with_error("You have to login to update your profile.");
}

header("Location: ". append_sid("userpage.php"));
?>