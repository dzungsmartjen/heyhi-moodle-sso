<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_heyhi', get_string('pluginname', 'local_heyhi'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configtext('local_heyhi/apibase',
        get_string('apibase', 'local_heyhi'), '', 'https://api.heyhiapi.com', PARAM_URL));
    $settings->add(new admin_setting_configtext('local_heyhi/clientid',
        get_string('clientid', 'local_heyhi'), '', '', PARAM_RAW_TRIMMED));
    $settings->add(new admin_setting_configpasswordunmask('local_heyhi/clientsecret',
        get_string('clientsecret', 'local_heyhi'), '', ''));
    $settings->add(new admin_setting_configtext('local_heyhi/branchurl',
        get_string('branchurl', 'local_heyhi'), get_string('branchurl_desc', 'local_heyhi'), '', PARAM_URL));
    $settings->add(new admin_setting_configtext('local_heyhi/parentrole',
        get_string('parentrole', 'local_heyhi'), get_string('parentrole_desc', 'local_heyhi'), 'parent', PARAM_ALPHANUMEXT));
}
