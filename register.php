<?php
include("includes/main.php");
placeHeader(array(array("Register")));

$template->set_filenames(array(
	'body' => 'register_body.tpl')
);

$template->assign_vars(array(
	'REGISTER_LINK' => append_sid('forums/ucp.php', 'mode=register'))
);

$template->pparse('body');

placeFooter();
?>