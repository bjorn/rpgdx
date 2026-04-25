<?php
include_once("includes/main.php");

$user_theme  = (int) ($_POST['user_theme']  ?? 0);
$sort_method = (int) ($_POST['sort_method'] ?? 0);

if ($userdata['session_logged_in']) {
  doQuery(
		"UPDATE ". USERS_TABLE ." SET ".
			"user_theme=". $user_theme .", ".
			"user_projects_sort_method=". $sort_method ." ".
		"WHERE user_id=". $userdata['user_id']
	);
}
else
{
  abort_with_error("You have to login to update your profile.");
}

header("Location: ". append_sid("userpage.php"));
?>