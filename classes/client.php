<?php
namespace local_heyhi;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/filelib.php');

/**
 * Server-to-server calls to the HeyHi API. Nothing here is ever sent to the browser.
 */
class client {

    /** HeyHi role IDs. */
    const ROLE_TUTOR = 1;
    const ROLE_STUDENT = 2;
    const ROLE_PARENT = 3;

    /**
     * Step 1: OAuth2 access token (client-credentials grant). Cached until shortly before it expires.
     */
    public static function get_access_token(bool $forcenew = false): string {
        $cached = get_config('local_heyhi', 'accesstoken');
        $expires = (int) get_config('local_heyhi', 'accesstokenexpires');
        if (!$forcenew && $cached && $expires > time() + 300) {
            return $cached;
        }

        $data = self::post('/oauth2/token', [
            'grant_type' => 'client_credentials',
            'client_id' => get_config('local_heyhi', 'clientid'),
            'client_secret' => get_config('local_heyhi', 'clientsecret'),
        ]);
        if (empty($data['access_token'])) {
            throw new \moodle_exception('ssofailed', 'local_heyhi', '',
                $data['message'] ?? $data['error'] ?? 'no access token');
        }

        set_config('accesstoken', $data['access_token'], 'local_heyhi');
        set_config('accesstokenexpires', time() + (int) ($data['expires_in'] ?? 0), 'local_heyhi');

        return $data['access_token'];
    }

    /**
     * Step 2: single-use SSO token for one user.
     */
    public static function get_sso_token(string $username, int $userrole): string {
        $params = ['username' => $username, 'user_role' => $userrole];

        $data = self::post('/api/v1/access', $params, self::get_access_token());
        if (($data['httpcode'] ?? 0) == 401) {
            // Cached access token was rejected: get a fresh one and retry once.
            $data = self::post('/api/v1/access', $params, self::get_access_token(true));
        }

        // Failures come back as HTTP 200 with {"success": false, "message": "..."}.
        if (empty($data['token'])) {
            throw new \moodle_exception('ssofailed', 'local_heyhi', '', $data['message'] ?? 'no token');
        }

        return $data['token'];
    }

    /**
     * Map the Moodle user to a HeyHi role. ADAPT THIS to the school's own role setup.
     *
     * Moodle roles are assigned per context, so this looks at every role the user holds anywhere:
     * teacher-type role => Tutor, configured parent role => Parent, otherwise Student.
     */
    public static function get_heyhi_role(int $userid): int {
        global $DB;

        $roles = $DB->get_records_sql(
            "SELECT DISTINCT r.id, r.shortname, r.archetype
               FROM {role_assignments} ra
               JOIN {role} r ON r.id = ra.roleid
              WHERE ra.userid = ?", [$userid]);

        $parentrole = get_config('local_heyhi', 'parentrole');
        $isparent = false;
        foreach ($roles as $role) {
            if (in_array($role->archetype, ['editingteacher', 'teacher'])) {
                return self::ROLE_TUTOR;
            }
            if ($parentrole && $role->shortname === $parentrole) {
                $isparent = true;
            }
        }

        return $isparent ? self::ROLE_PARENT : self::ROLE_STUDENT;
    }

    /**
     * POST to the HeyHi API and decode the JSON response.
     */
    private static function post(string $path, array $params, ?string $bearer = null): array {
        $curl = new \curl();
        $curl->setHeader('Accept: application/json');
        if ($bearer) {
            $curl->setHeader('Authorization: Bearer ' . $bearer);
        }

        $url = rtrim(get_config('local_heyhi', 'apibase'), '/') . $path;
        $body = $curl->post($url, $params, ['CURLOPT_TIMEOUT' => 15]);
        if ($curl->get_errno()) {
            throw new \moodle_exception('ssofailed', 'local_heyhi', '', $curl->error);
        }

        $data = json_decode($body, true);
        $data = is_array($data) ? $data : [];
        $data['httpcode'] = $curl->get_info()['http_code'] ?? 0;

        return $data;
    }
}
