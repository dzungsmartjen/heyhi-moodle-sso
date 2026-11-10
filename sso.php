<?php
// Entry point behind the "Go to HeyHi" menu link:
// 1. generate an SSO token for the current user, 2. redirect the browser to HeyHi.

require_once(__DIR__ . '/../../config.php');

require_login(null, false);
if (isguestuser()) {
    throw new \moodle_exception('noguest');
}

$branchurl = rtrim((string) get_config('local_heyhi', 'branchurl'), '/');
if (!$branchurl || !get_config('local_heyhi', 'clientid') || !get_config('local_heyhi', 'clientsecret')) {
    throw new \moodle_exception('notconfigured', 'local_heyhi');
}

// The Moodle username must be the same as the user's HeyHi username.
$token = \local_heyhi\client::get_sso_token(
    $USER->username,
    \local_heyhi\client::get_heyhi_role($USER->id)
);

// The API returns the token already URL-encoded, so append it as-is. Do not build this
// with moodle_url/urlencode: that would encode it a second time and HeyHi would reject it.
header('Location: ' . $branchurl . '/profile?token=' . $token);
exit;
