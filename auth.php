<?php
/**
 * @author Mart Mangus
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 * Authentication Plugin: Login with Estonian ID-card
 *
 */

require_once($CFG->dirroot.'/lib/authlib.php');

class auth_plugin_est_id_card extends auth_plugin_base {

    /** Constructor */
    function auth_plugin_est_id_card() {
        $this->authtype = 'est_id_card';
    }
    private function id_card_inserted()
    {   // TODO
        return false;
    }
    private function get_id_number_from_id_card()
    {   // TODO
        return '38401275724';
    }
    function user_login($username, $password) {
        return false;
    }

    function authenticate_with_id_card() {
        global $DB, $CFG, $SESSION;
        if ($this->id_card_inserted()) {
            $conditions = array('idnumber' => $this->get_id_number_from_id_card());
            $usertologin = $DB->get_record('user', $conditions, $fields='*');
            if ($usertologin !== false) {
                $USER = complete_user_login($usertologin);
                $goto = isset($SESSION->wantsurl) ? $SESSION->wantsurl : $CFG->wwwroot;
                redirect($goto);
            } else
                $goto = $CFG->wwwroot . '/login/?no_user_with_id=1';
        } else
            $goto = $CFG->wwwroot . '/login/?no_id_card_data=1';
        redirect($goto);
    }

    /**
     * Returns a list of potential IdPs that this authentication plugin supports.
     * This is used to provide links on the login page.
     *
     * @param string $wantsurl the relative url fragment the user wants to get to.  You can use this to compose a returnurl, for example
     *
     * @return array like:
     *              array(
     *                  array(
     *                      'url' => 'http://someurl',
     *                      'icon' => new pix_icon(...),
     *                      'name' => get_string('somename', 'auth_yourplugin'),
     *                 ),
     *             )
     */
    function loginpage_idp_list($wantsurl) {
        global $OUTPUT;
        return array(
            array(
                'url' => new moodle_url('https://h1.moodle.e-ope.ee/auth/est_id_card/login.php'),
                'icon' => new pix_icon('idkaart', 'Login with Estonian ID-card'), //'https://h1.moodle.e-ope.ee/auth/est_id_card/images/idkaart.gif',
                'name' => get_string('login_with', 'auth_est_id_card')
            )
        );
    }

    function loginpage_hook() {
        global $errormsg;
        if (isset($_GET['no_id_card_data'])) {
            $errormsg = get_string('no_id_card_data', 'auth_est_id_card');
        } else if (isset($_GET['no_user_with_id'])) {
            $errormsg = get_string('no_user_with_id', 'auth_est_id_card');
        }
        /*
        global $frm;  // can be used to override submitted login form
        global $user; // can be used to replace authenticate_user_login()
        echo "uhuu?";
        var_dump($frm);
        var_dump($user);
        die('loginpage_hook');
        */
    }
}

