<?php
include("includes/main.php");

$template->set_filenames(array(
	'body' => 'showcontests_body.tpl')
);

$result = doQuery(
	"SELECT contest_id, contest_name, contest_start, contest_end, COUNT(entry_id) AS npart ".
	"FROM ". CONTESTS_TABLE. " LEFT JOIN ". CONTEST_ENTRIES_TABLE ." ON entry_contest = contest_id ".
	"WHERE contest_status != ". CON_CLOSED ." GROUP BY contest_id"
);

if (mysql_num_rows($result) > 0)
{
	$template->assign_block_vars('new_contests', array(
		'NUMBER' => mysql_num_rows($result)
	));

	while ($row = mysql_fetch_object($result))
	{
		$template->assign_block_vars('new_contests.contest', array(
			'NAME'  => $row->contest_name,
			'URL'   => append_sid('showcontest.php?contest_id='. $row->contest_id),
			'START' => $row->contest_start,
			'END'   => $row->contest_end,
			'NPART' => $row->npart
		));
	}
}
else {
	$template->assign_block_vars('no_new_contests', array());
}



$result = doQuery(
	"SELECT contest_id, contest_name, contest_start, contest_end, COUNT(entry_id) AS npart ".
	"FROM ". CONTESTS_TABLE. " LEFT JOIN ". CONTEST_ENTRIES_TABLE ." ON entry_contest = contest_id ".
	"WHERE contest_status = ". CON_CLOSED ." GROUP BY contest_id"
);

if (mysql_num_rows($result) > 0)
{
	$template->assign_block_vars('old_contests', array(
		'NUMBER' => mysql_num_rows($result)
	));

	while ($row = mysql_fetch_object($result))
	{
		$template->assign_block_vars('old_contests.contest', array(
			'NAME'  => $row->contest_name,
			'URL'   => append_sid('showcontest.php?contest_id='. $row->contest_id),
			'START' => $row->contest_start,
			'END'   => $row->contest_end,
			'NPART' => $row->npart
		));
	}
}
else {
	$template->assign_block_vars('no_old_contests', array());
}





placeHeader(array(array("Contests", append_sid('showcontests.php'))));
$template->pparse('body');
placeFooter();
?>