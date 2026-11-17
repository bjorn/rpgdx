<?php
if (!defined('IN_RPGDX')) {
	die("Hacking attempt?");
}

// All site queries share phpBB's configured mysqli connection.
function rpgdx_db_query($query)
{
    global $db;
    try {
        return mysqli_query($db->get_db_connect_id(), $query);
    } catch (mysqli_sql_exception $exception) {
        return false;
    }
}

function rpgdx_db_error()
{
    global $db;
    return mysqli_error($db->get_db_connect_id());
}

function rpgdx_db_insert_id()
{
    global $db;
    return mysqli_insert_id($db->get_db_connect_id());
}

function rpgdx_db_fetch_assoc($result) { return $result ? mysqli_fetch_assoc($result) : null; }
function rpgdx_db_fetch_row($result) { return $result ? mysqli_fetch_row($result) : null; }
function rpgdx_db_fetch_object($result) { return $result ? mysqli_fetch_object($result) : null; }
function rpgdx_db_fetch_array($result, $mode = MYSQLI_BOTH) { return $result ? mysqli_fetch_array($result, $mode) : null; }
function rpgdx_db_num_rows($result) { return $result ? mysqli_num_rows($result) : 0; }

// Extended database query functions

function doQuery($query, $die = 2)
{
	if (!$result = rpgdx_db_query($query)) {
		$error = "<p><b>Error during MySQL query (please notify current <a href=\"mailto:admin@rpgdx.net\">maintainer</a>):</b><br />&nbsp;$query<br>&nbsp;<span style=\"color: rgb(255,0,0);\">". rpgdx_db_error() ."</span></p>";
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
		while ($line = @rpgdx_db_fetch_array($result, MYSQLI_ASSOC)) {
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

	$line = rpgdx_db_fetch_assoc($result);
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

		$line = rpgdx_db_fetch_assoc($result);
	}
	print "</table>";
}

?>
