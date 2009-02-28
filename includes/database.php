<?php
if (!defined('IN_RPGDX')) {
	die("Hacking attempt?");
}

// Connect to the MySQL server
include_once('database_connect.php');


// Extended database query functions

function doQuery($query, $die = 2)
{
	if (!$result = mysql_query($query)) {
		$error = "<p><b>Error during MySQL query (please notify current <a href=\"mailto:admin@rpgdx.net\">maintainer</a>):</b><br />&nbsp;$query<br>&nbsp;<span style=\"color: rgb(255,0,0);\">". mysql_error() ."</span></p>";
		switch ($die) {
			case 0: break;
			case 1: print($error); break;
			case 2: abort_with_error($error); break;
		}
	}
	return $result;
}

function doVisibleQuery($query)
{
	if ($result = doQuery($query, 1)) {
		print("<p>$query<br />");
		print("<table border=1 cellpadding=2>\n");
		while ($line = @mysql_fetch_array($result, MYSQL_ASSOC)) {
			print("\t<tr>\n");
			foreach ($line as $col_value) {
				print("\t\t<td>$col_value</td>\n");
			}
			print("\t</tr>\n");
		}
		print("</table>");
		print("</p>");
	}
}

function printQueryResult($result)
{
	// Start table
	print "<table border=1 cellpadding=3>\n";

	$line = mysql_fetch_assoc($result);
	if ($line) {
		print "\t<tr>\n";
		foreach ($line as $key => $col_value) {
			print "\t\t<th nowrap align=left>$key</th>\n";
		}
		print "\t</tr>\n";
	}

	while ($line) {
		print "\t<tr>\n";
		foreach ($line as $key => $col_value) {
			print "\t\t<td>$col_value</td>\n";
		}
		print "\t</tr>\n";

		$line = mysql_fetch_assoc($result);
	}
	print "</table>";
}

?>
