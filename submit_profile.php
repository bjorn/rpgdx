<?php
include_once("includes/main.php");

$user_theme  = (int) ($_POST['user_theme']  ?? 0);
$sort_method = (int) ($_POST['sort_method'] ?? 0);

if ($userdata['session_logged_in']) {
  if ($user_theme < 0 || ($user_theme && !rpgdx_db_num_rows(doQuery("SELECT theme_id FROM " . RPGDX_THEMES_TABLE . " WHERE theme_id = $user_theme")))) {
    abort_with_error("Invalid theme.");
  }
  if ($sort_method < PSORT_USE_LAST || $sort_method > PSORT_USER) {
    abort_with_error("Invalid sort method.");
  }
  if (!check_form_key('rpgdx_preferences')) {
    abort_with_error("Your form has expired. Please try again.");
  }
  rpgdx_save_preferences(array('user_theme' => $user_theme, 'user_projects_sort_method' => $sort_method));
}
else
{
  abort_with_error("You have to login to update your profile.");
}

header("Location: ". append_sid("userpage.php"));
?>