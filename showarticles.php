<?php
include("includes/main.php");

if ($cat > 0) {
	if ($result = doQuery("SELECT * FROM ". ARTICLE_TYPES_TABLE ." WHERE type_id = $cat")) {
		$category = mysql_fetch_object($result);
	}
}

$template->set_filenames(array(
	'body' => 'showarticles_body.tpl')
);

if (isset($category) && $category->type_id > 0)
{
	placeHeader(array(
		array($category->type_long_title, append_sid('showarticles.php?cat='. $category->type_id))
	));

	// Select all articles of the current category
	$result = doQuery("SELECT * FROM ". ARTICLES_TABLE ." WHERE article_type = $cat ORDER BY article_title");

	// Print list of selected Articles
	while ($row = mysql_fetch_object($result)) {
		$template->assign_block_vars('article', array(
			'URL'  => $row->article_url,
			'NAME' => $row->article_title,
			'DESC' => $row->article_summary)
		);
	}
}
else {
	// Non existing category
	placeHeader(array(array("Non-existing category")));
	$template->assign_block_vars('nosuchcategory', array());
}

$template->pparse('body');

placeFooter();
?>