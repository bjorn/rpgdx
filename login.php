<?php
define('IN_LOGIN', true);
include('includes/main.php');

if (isset($_GET['logout']) || isset($_POST['logout'])) {
    $sid = $_POST['sid'] ?? $_GET['sid'] ?? '';
    if ($userdata['session_logged_in']) {
        if (!hash_equals($user->session_id, $sid)) {
            abort_with_error('The logout link has expired. Please try again.');
        }
        $user->session_kill();
    }
    header('Location: index.php');
    exit;
}

if ($userdata['session_logged_in']) {
    header('Location: userpage.php');
    exit;
}

// Post to phpBB so password upgrades, CAPTCHA and login limits stay centralized.
$forward_page = $_GET['redirect'] ?? $_POST['redirect'] ?? '/index.php';
if (!is_string($forward_page) || !preg_match('#^/(?!/)[a-zA-Z0-9_./?&=%+-]*$#D', $forward_page)) {
    $forward_page = '/index.php';
}
add_form_key('login');
$template->assign_vars(array(
    'USERNAME' => '',
    'S_HIDDEN_FIELDS' => build_hidden_fields(array('redirect' => $forward_page, 'sid' => $user->session_id)) . $template->_tpldata['.'][0]['S_FORM_TOKEN'],
    'U_SEND_PASSWORD' => append_sid('forums/ucp.php', 'mode=sendpassword'),
));
placeHeader(array(array('Login')));
$template->set_filenames(array('body' => 'login_body.tpl'));
$template->pparse('body');
placeFooter();
