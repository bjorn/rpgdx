<?php
include("includes/main.php");

placeHeader(array(array("User page", append_sid('userpage.php'))));

if ($userdata['session_logged_in']) {
?>
	<h4>Profile settings</h4>
	<form action="<?php echo append_sid('submit_profile.php') ?>" method="post">
	<table width="50%">
    <tr>
      <td class="td" width="50%">Theme:</td>
      <td class="td" width="50%">
        <select name="user_theme" class="formInput">
        <?php
        echo "<option value=\"0\" ". (($userdata['user_theme'] == 0) ? " selected" : "") .">&lt;default&gt;</option>\n";
        $result = doQuery("SELECT theme_id, theme_name FROM ". RPGDX_THEMES_TABLE ." ORDER BY theme_name");
        while ($row = mysql_fetch_object($result)) {
          echo "<option value=\"$row->theme_id\"". (($userdata['user_theme'] == $row->theme_id) ? " selected" : "") .">$row->theme_name</option>\n";
        }
        ?>
        </select>
      </td>
    </tr>
    <tr>
      <td class="td" width="50%">Projects sort method:</td>
      <td class="td" width="50%">
        <select name="sort_method" class="formInput">
				<?php
				echo '<option value='. PSORT_USE_LAST . (($userdata['user_projects_sort_method'] == PSORT_USE_LAST) ? ' selected' : '') .'>&lt;use previous&gt;';
				echo '<option value='. PSORT_NAME     . (($userdata['user_projects_sort_method'] == PSORT_NAME)     ? ' selected' : '') .'>Name';
				echo '<option value='. PSORT_RATING   . (($userdata['user_projects_sort_method'] == PSORT_RATING)   ? ' selected' : '') .'>Rating';
				echo '<option value='. PSORT_UPDATE   . (($userdata['user_projects_sort_method'] == PSORT_UPDATE)   ? ' selected' : '') .'>Last update';
        ?>
        </select>
      </td>
    </tr>
    <tr><td colspan="2" align="left"><input type="reset" value="Reset"> <input type="submit" value="Apply changes"></td></tr>
	</table>
	</form>

	<h4>Your projects</h4>
	<table cellspacing="1" cellpadding="0" border="0" width="100%">
		<tr>
			<td class="th" width="100%">Name</td>
			<td class="th" width="0">Genre</td>
			<td class="th" width="0">Last updated</td>
			<td class="th" width="0">Action</td>
		</tr>
	<?php
	// Select all of the games by the current user
	$result = doQuery(
		"SELECT project_id, project_name, type_title AS project_type, DATE_FORMAT(project_last_update, '%d-%m-%Y %H:%i:%s') AS project_last_update ".
		"FROM ". PROJECTS_TABLE ." LEFT JOIN ". PROJECT_TYPES_TABLE ." ON project_type = type_id ".
		"WHERE user_id = ". $userdata['user_id'] ." ORDER BY project_name"
	);

	if (mysql_num_rows($result) > 0) {
		while ($row = mysql_fetch_object($result)) {
			echo "<tr>";
			echo "<td class=\"td\">$row->project_name</td>";
			echo "<td class=\"td\" nowrap>$row->project_type</td>";
			echo "<td class=\"td\" nowrap>$row->project_last_update<img src=\"images/pixel.gif\" height=0 width=0></td>";
			echo "<td class=\"td\" nowrap>";
			echo "[<a href=\"". append_sid("editproject.php?project_id=$row->project_id") ."\">edit</a>]";
			echo " [<a href=\"". append_sid("editproject_general.php?action=remove&amp;project_id=$row->project_id&amp;confirmed=1"). "\" onClick=\"if (!(window.confirm('Are you sure you want to remove this project?'))) { return false; }\">remove</a>]";
			echo "</td>";
			echo "</tr>";
		}
	} else {
		echo "<tr><td colspan=5 class=\"td\" align=center>You don't have any games on RPGDX</td></tr>";
	}
	?>
	</table>
	<br />

	<h4>Your articles</h4>
	<table cellspacing=1 cellpadding=0 border=0 width="100%">
		<tr>
			<td class="th" width="100%">Name</td>
			<td class="th" width=0>Type</td>
			<td class="th" width=0>Added</td>
			<td class="th" width=0>Action</td>
		</tr>
	<?php
	// Select all of the articles by the current user
	$result = doQuery(
		"SELECT article_id, article_title, type_title AS article_type, DATE_FORMAT(article_created, '%d-%m-%Y %H:%i:%s') AS article_created ".
		"FROM ". ARTICLES_TABLE ." LEFT JOIN ". ARTICLE_TYPES_TABLE ." ON article_type = type_id ".
		"WHERE user_id = ". $userdata['user_id'] ." ORDER BY article_title"
	);

	if (mysql_num_rows($result) > 0) {
		while ($row = mysql_fetch_object($result)) {
			echo "<tr>";
			echo "<td class=\"td\">$row->article_title</td>";
			echo "<td class=\"td\" nowrap>$row->article_type</td>";
			echo "<td class=\"td\" nowrap>$row->article_created<img src=\"images/pixel.gif\" height=0 width=0></td>";
			echo "<td class=\"td\" nowrap>";
			echo "[<a href=\"". append_sid("editarticle.php?action=edit&article_id=$row->article_id") ."\">edit</a>]";
			echo " [<a href=\"". append_sid("editarticle.php?action=remove&article_id=$row->article_id&amp;confirmed=1") ."\" onClick=\"if (!(window.confirm('Are you sure you want to remove this article?'))) { return false; }\">remove</a>]";
			echo "</td>";
			echo "</tr>";
		}
	} else {
		echo "<tr><td colspan=5 class=\"td\" align=center>You don't have any articles on RPGDX</td></tr>";
	}
	?>
	</table>
	<br />

	<h4>Your reviews</h4>
	<table cellspacing=1 cellpadding=0 border=0 width="100%">
		<tr>
			<td class="th" width="100%">Game</td>
			<td class="th" width=0 nowrap>Rating</td>
			<td class="th" width=0>Added</td>
			<td class="th" width=0>Action</td>
		</tr>
	<?php
	// Select all of the games by the current user
	$result = doQuery(
		"SELECT review_id, project_name, review_score, DATE_FORMAT(review_added, '%d-%m-%Y %H:%i:%s') AS review_added ".
		"FROM ". REVIEWS_TABLE ." r LEFT JOIN ". PROJECTS_TABLE ." USING (project_id) WHERE r.user_id = ". $userdata['user_id'] ." ORDER BY review_added"
	);

	if (mysql_num_rows($result) > 0) {
		while ($row = mysql_fetch_object($result)) {
			echo "<tr>";
			echo "<td class=\"td\">$row->project_name</td>";
			echo "<td class=\"td\">$row->review_score</td>";
			echo "<td class=\"td\" nowrap>$row->review_added<img src=\"images/pixel.gif\" height=0 width=0></td>";
			echo "<td class=\"td\" nowrap>";
			echo "[<a href=\"". append_sid("editreview.php?action=edit&amp;review_id=$row->review_id") ."\">edit</a>]";
			echo " [<a href=\"". append_sid("editreview.php?action=remove&amp;review_id=$row->review_id&amp;confirmed=1") ."\" onClick=\"if (!(window.confirm('Are you sure you want to remove this review?'))) { return false; }\">remove</a>]";
			echo "</td>";
			echo "</tr>";
		}
	} else {
		echo "<tr><td colspan=5 class=\"td\" align=center>You don't have any reviews on RPGDX</td></tr>";
	}
	?>
	</table>
	<br />
	<div align=center>
	[<a href="<?php echo append_sid("editproject_general.php?action=add") ?>">add game</a>] 
	[<a href="<?php echo append_sid("editarticle.php?action=add") ?>">add article</a>]
	[<a href="<?php echo append_sid("editnews.php?action=add") ?>">post news</a>]
	</div><br />
<?php
} else {
	// Login failed
	echo "<center>You have to be logged in if you want to edit your game info or post news.</center>";
}

placeFooter();
?>