<?php
namespace local_heyhi;

use core\hook\navigation\primary_extend;
use navigation_node;

/**
 * Adds the "Go to HeyHi" link to the navigation menu.
 */
class hook_callbacks {

    public static function extend_primary_navigation(primary_extend $hook): void {
        if (!isloggedin() || isguestuser()) {
            return;
        }

        // The link points to our own page, not to HeyHi: the SSO token is single-use,
        // so it must be generated at click time.
        $hook->get_primaryview()->add(
            get_string('gotoheyhi', 'local_heyhi'),
            new \moodle_url('/local/heyhi/sso.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            'local_heyhi_sso'
        );
    }
}
