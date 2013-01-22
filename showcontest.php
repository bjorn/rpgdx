<?php
include("includes/main.php");

// A contest ID must be specified
if (!isset($contest_id)) {
	abort_with_error('No contest_id specified.');
}

/* Grab the direct info about the contest from the database
 */

$result = doQuery(
	"SELECT contest_id, contest_name, contest_description, contest_start, contest_end, contest_status ".
	"FROM ". CONTESTS_TABLE ." ".
	"WHERE contest_id=". intval($contest_id)
);

// The contest ID must be valid
if (!$contest = mysql_fetch_object($result)) {
	abort_with_error('No such contest, it might have been removed.');
}


$template->set_filenames(array(
	'body' => 'showcontest_body.tpl')
);

$template->assign_vars(array(
	'CONTEST_NAME'        => $contest->contest_name,
	'CONTEST_START'       => $contest->contest_start,
	'CONTEST_END'         => $contest->contest_end,
	'CONTEST_DESCRIPTION' => $contest->contest_description
));

/* Display information about the participants
 */

if ($contest->contest_status == CON_CLOSED)
{
	/* Determine which projects have won in which category
	 */

	// Select categories that could be voted for
	$result = doQuery(
		"SELECT category_id, category_name ".
		"FROM rpgdx_contest_categories ".
		"WHERE category_contest = ".$contest->contest_id." ".
		"ORDER BY category_value DESC"
	);

	$i = 0;
	while ($category = mysql_fetch_object($result)) {
		$categories[$i] = $category;
		$i++;
	}

	// Fetch the projects for which was voted, best on top.
	// TODO: Add real category support to this system
	$result = doQuery(
		"SELECT SUM(category_value) AS score, project_id ".
		"FROM rpgdx_contest_votes ".
		"LEFT JOIN rpgdx_contest_entries ON vote_entry = entry_id ".
		"LEFT JOIN rpgdx_projects ON entry_project = rpgdx_projects.project_id ".
		"LEFT JOIN rpgdx_contest_categories ON vote_category = category_id ".
		"WHERE entry_contest = ".$contest->contest_id." ".
		"GROUP BY project_id ".
		"ORDER BY score DESC"
	);

	$i = 0;
	while ($i < sizeof($categories) && $entry = mysql_fetch_object($result)) {
		if (isset($prev_score) && $entry->score != $prev_score) {
			$i++;
		}

		if ($i < sizeof($categories)) {
			// Set category in which this project won
			$entries[$entry->project_id] = $categories[$i];
			$prev_score = $entry->score;
		}
	}
}


$result = doQuery(
	"SELECT username, ". USERS_TABLE .".user_id, project_name, project_id, entry_date, entry_id ".
	"FROM ". CONTEST_ENTRIES_TABLE .
		" LEFT JOIN ". PROJECTS_TABLE ." ON entry_project = project_id ".
		" LEFT JOIN ". USERS_TABLE ." USING (user_id) ".
	"WHERE entry_contest=". intval($contest_id) ." ORDER BY entry_date"
);

if (mysql_num_rows($result) > 0)
{
	$template->assign_block_vars('subscriptions', array(
		'NUMBER' => mysql_num_rows($result)
	));

	while ($row = mysql_fetch_object($result))
	{
		$won = (isset($entries[$row->project_id])) ? $entries[$row->project_id]->category_name : '';
		if ($row->project_name) {
			$template->assign_block_vars('subscriptions.subscription', array(
				'ENTRY_ID'     => $row->entry_id,
				'USER_NAME'    => $row->username,
				'USER_URL'     => append_sid('profile.php?user_id='. $row->user_id),
				'PROJECT_NAME' => $row->project_name,
				'PROJECT_URL'  => append_sid('showgame.php?project_id='. $row->project_id),
				'DATE'         => $row->entry_date,
				'WON'          => $won
			));
		}
		else {
			$template->assign_block_vars('subscriptions.removed_subscription', array(
				'DATE'         => $row->entry_date,
				'WON'          => $won
			));
		}
	}
} else {
	$template->assign_block_vars('no_subscriptions', array());
}


/* Display the subscription section
 */

if ($contest->contest_status == CON_OPEN) {
	if ($userdata['session_logged_in'])
	{
		$result = doQuery(
			"SELECT username, entry_id, project_name ".
			"FROM ". CONTEST_ENTRIES_TABLE .
				" LEFT JOIN ". PROJECTS_TABLE ." ON entry_project = project_id ".
				" LEFT JOIN ". USERS_TABLE ." USING (user_id) ".
			"WHERE ". USERS_TABLE. ".user_id = ". $userdata['user_id'] ." AND entry_contest = ". $contest->contest_id ." ".
			"LIMIT 1"
		);

		if (mysql_num_rows($result) == 1)
		{
			// The user is subscribed, offer unsubscription
			$row = mysql_fetch_object($result);

			$template->assign_block_vars('unsubscribe', array(
				'ACTION'    => append_sid('submit_subscription.php?action=unsubscribe'),
				'HIDDEN'    => '<input type="hidden" name="entry_id" value="'. $row->entry_id . '">',
				'GAME_NAME' => $row->project_name
			));
		}
		else
		{
			// The user is not subscribed, check if he can subscribe
			$result = doQuery("SELECT project_id, project_name FROM ". PROJECTS_TABLE ." WHERE user_id = ". $userdata['user_id']);

			if (mysql_num_rows($result) > 0)
			{
				// The user has at least one project he could subscribe with
				$project_options = '';
				while ($row = mysql_fetch_object($result)) {
					$project_options .= '<option value="'. $row->project_id .'">'. $row->project_name .'</option>\n';
				}

				$template->assign_block_vars('subscribe', array(
					'ACTION'          => append_sid('submit_subscription.php?action=subscribe'),
					'HIDDEN'          => '<input type="hidden" name="contest_id" value="'. $contest->contest_id .'">',
					'PROJECT_FIELD'   => 'project_id',
					'PROJECT_OPTIONS' => $project_options
				));
			}
			else
			{
				// The user should create a project first
				$template->assign_block_vars('no_subscription', array(
					'TEXT' => 'You can add a project to RPGDX to subscribe with.'
				));
			}
		}
	}
	else
	{
		// The visitor needs to login first
		$template->assign_block_vars('no_subscription', array(
			'TEXT' => 'You can login to subscribe to this contest.'
		));
	}
}
else
{
	// Subscription is disabled in CON_ACTIVE, CON_VOTING and CON_CLOSED contest statusses.
	$template->assign_block_vars('no_subscription', array(
		'TEXT' => 'Subscription disabled.'
	));
}


/* Display the voting section
 */

if ($contest->contest_status == CON_VOTING)
{
	if (!$userdata['session_logged_in']) {
		$template->assign_block_vars('no_voting', array(
			'TEXT' => 'You can login to be able to vote.'
		));
	}
	else {
		// Determine the number of categories
		$result = doQuery(
			"SELECT category_id ".
			"FROM ". CONTEST_CATEGORIES_TABLE ." ".
			"WHERE category_contest = ". $contest_id
		);
		$num_categories = mysql_num_rows($result);

		// Determine whether this user has voted yet.
		$result = doQuery(
			"SELECT vote_id ".
			"FROM ". CONTEST_CATEGORIES_TABLE ." LEFT JOIN ". CONTEST_VOTES_TABLE ." ON vote_category = category_id ".
			"WHERE category_contest = ". $contest->contest_id ." AND vote_user = ". $userdata['user_id']
		);

		if (mysql_num_rows($result) == $num_categories) {
			$template->assign_block_vars('no_voting', array(
				'TEXT' => 'Thank you for voting!'
			));
		}
		else {
			// Determine votable projects
			$result = doQuery(
				"SELECT username, entry_id, project_name, project_id, entry_disqualified ".
				"FROM ". CONTEST_ENTRIES_TABLE .
					" LEFT JOIN ". PROJECTS_TABLE ." ON entry_project = project_id ".
					" LEFT JOIN ". USERS_TABLE ." USING (user_id) ".
				"WHERE entry_contest = ". $contest->contest_id ." AND NOT ISNULL(project_name) AND entry_disqualified = 0 ORDER BY entry_date"
			);

			$project_options = '<option value="-1">-</option>';
			while ($row = mysql_fetch_object($result)) {
				$project_options .= '<option value="'. $row->entry_id .'">'. $row->project_name .'</option>\n';
			}

			$template->assign_block_vars('voting', array(
				'ACTION'          => append_sid('submit_contest_votes.php'),
				'HIDDEN'          => '<input type="hidden" name="contest_id" value="'. $contest->contest_id .'">',
				'PROJECT_OPTIONS' => $project_options
			));

			// Determine categories
			$result = doQuery(
				"SELECT category_id, category_name ".
				"FROM ". CONTEST_CATEGORIES_TABLE ." ".
				"WHERE category_contest = ". $contest->contest_id
			);

			if (mysql_num_rows($result) > 0)
			{
				$template->assign_block_vars('voting.categories', array());
				while ($row = mysql_fetch_object($result)) {
					$template->assign_block_vars('voting.categories.categorie', array(
						'NAME'          => $row->category_name,
						'PROJECT_FIELD' => 'categories['. $row->category_id .']'
					));
				}
			}
			else
			{
				$template->assign_block_vars('voting.no_categories', array());
			}
		}
	}
}
else
{
	$template->assign_block_vars('no_voting', array(
		'TEXT' => 'Voting disabled.'
	));
}




placeHeader(array(
	array("Contests", append_sid('showcontests.php')),
	array($contest->contest_name, append_sid('showcontest.php?contest_id='. $contest_id)))
);
$template->pparse('body');
placeFooter();
?>
