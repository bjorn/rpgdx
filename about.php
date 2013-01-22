<?php
include("includes/main.php");
placeHeader(array(array("About", 'about.php')));

$template->set_filenames(array(
	'body' => 'about_body.tpl')
);

$template->pparse('body');

placeFooter();
?>
