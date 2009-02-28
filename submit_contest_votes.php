<?php
include_once("includes/main.php");

// - The voter needs to be logged in
// - The voter most have voted for some categories
// - A contest ID must be specified

if (!$userdata['session_logged_in']) {
	abort_with_error('You have to login to be able to vote!');
}
if (!isset($categories)) {
	abort_with_error('Somehow your submission lacked the categories.');
}
if (!isset($contest_id)) {
	abort_with_error('No contest_id specified.');
} else {
  $contest_id = intval($contest_id);
}

// Get all the information about the contest
$result = doQuery(
	"SELECT contest_id, contest_name, contest_description, contest_start, contest_end, contest_status ".
	"FROM ". CONTESTS_TABLE ." ".
	"WHERE contest_id=". $contest_id
);

// The contest ID must be valid
if (!$contest = mysql_fetch_object($result)) {
	abort_with_error('No such contest, it might have been removed.');
}

// Get all the categories
$result = doQuery(
	"SELECT category_id, category_name ".
	"FROM ". CONTEST_CATEGORIES_TABLE ." ".
	"WHERE category_contest = ". $contest_id
);
$num_categories = mysql_num_rows($result);

// The number of categories voted on must match the number of
// categories of the contest
if ($num_categories != sizeof($categories)) {
	abort_with_error('Number of categories doesn\'t match.');
}

// The voter cannot vote for the same project in different categories
// and each vote needs to be on a valid category (existing and belonging
// to the right contest)
$projects = array();
$blank_votes = 0;
foreach ($categories as $key => $col_value)
{
	// Check whether this is a valid category 
	$result = doQuery(
		"SELECT category_id FROM ". CONTEST_CATEGORIES_TABLE ." ".
		"WHERE category_id = ". $key ." AND category_contest = ". $contest_id
	);
	if (mysql_num_rows($result) != 1) {
		abort_with_error('At least one category you voted for seems to be invalid.');
	}

	// Check whether this is a valid entry
  if (intval($col_value) != -1) {
    $result = doQuery(
      "SELECT entry_id FROM ". CONTEST_ENTRIES_TABLE ." ".
      "WHERE entry_id = ". $col_value ." AND entry_contest = ". $contest_id
    );
    if (mysql_num_rows($result) != 1) {
      abort_with_error('At least one entry you voted on seems to be invalid.');
    }

    if (isset($projects[$col_value])) {
      abort_with_error('You cannot vote multiple times for the same project.');
    } else {
      $projects[$col_value] = $key;
    }
  } else {
    // Blank entry, there can be as much as lacking qualified entries
    $blank_votes += 1;
  }
}

// Get number of qualified entries
$result = doQuery(
	"SELECT entry_id FROM ". CONTEST_ENTRIES_TABLE ." ".
	"WHERE entry_disqualified = 0 AND entry_contest = ". $contest_id
);
$qualified_entries = mysql_num_rows($result);

// Number of blank votes cannot supercede lacking qualified entries
if ($num_categories - $qualified_entries > 0) {
  if ($blank_votes > $num_categories - $qualified_entries) {
    abort_with_error('Too many blank votes.');
  }
}

// All errors have been checked, apply vote to the database.

foreach ($categories as $category_id => $entry_id)
{
	// Remove any previous votes by this user for this category
	doQuery(
    "DELETE FROM ". CONTEST_VOTES_TABLE ." ".
    "WHERE vote_user = ". $userdata['user_id'] ." ".
    "AND vote_category = $category_id");

  if (intval($entry_id) != -1) {
    // Add current vote for this category to the database
    doQuery(
      "INSERT INTO ". CONTEST_VOTES_TABLE ." ".
      "(vote_entry, vote_user, vote_category) VALUES (".
      "$entry_id, ". $userdata['user_id'] .", $category_id)");
  }
};

header("Location: showcontest.php?contest_id=$contest_id");
?>
