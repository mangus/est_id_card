<?php
/**
 * @author Mart Mangus
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 * Authentication Plugin: Login with Estonian ID-card
 *
 * Real authentication takes place in file login.php (that file needs to have access to ID-card data)
 *
 */

require_once($CFG->dirroot.'/lib/authlib.php');

class auth_plugin_est_id_card extends auth_plugin_base {

    /** Constructor */
    function auth_plugin_est_id_card() {
        $this->authtype = 'est_id_card';
    }

    /** Login is going through file auth/est_id_card/login.php instead of usual login form */
    function user_login($username, $password) {
        return false;
    }

    private function id_card_inserted() {
        return isset($_SERVER['SSL_CLIENT_S_DN']);
    }

    private function get_id_number() {
        $expression = '~/serialNumber=([0-9]{11})~';
        if (preg_match($expression, $_SERVER['SSL_CLIENT_S_DN'], $matches))
            return $matches[1];
        else
            throw new Exception('Unsupported ID-card data: ' . $_SERVER['SSL_CLIENT_S_DN']);
    }

    /** Real authentication here */
    function authenticate_with_id_card() {
        global $DB, $CFG, $SESSION;
        if ($this->id_card_inserted()) {
            $conditions = array('idnumber' => $this->get_id_number());
            $usertologin = $DB->get_record('user', $conditions, $fields='*');
            if ($usertologin !== false) {
                $USER = complete_user_login($usertologin);
                if (optional_param('password_recovery', false, PARAM_BOOL))
                    $SESSION->wantsurl = $CFG->wwwroot . '/login/change_password.php';
                $goto = isset($SESSION->wantsurl) ? $SESSION->wantsurl : $CFG->wwwroot;
                redirect($goto);
            } else
                $goto = $CFG->wwwroot . '/login/?no_user_with_id=1';
        } else
            $goto = $CFG->wwwroot . '/login/?no_id_card_data=1';
        redirect($goto);
    }

    /** Creates "login with ID-card" link to Moodle login page */
    /*
    function loginpage_idp_list($wantsurl) {
        global $CFG;
        return array(
            array(
                'url' => new moodle_url($CFG->wwwroot . '/auth/est_id_card/login.php'),
                'icon' => new pix_icon('idkaart', 'Login with Estonian ID-card'),
                    // Need to copy this file (cp auth/est_id_card/images/idkaart.gif pix/)
                'name' => get_string('login_with', 'auth_est_id_card')
            )
        );
    }
    */

    /** Shows nice error messages to user */
    function loginpage_hook() {
        global $errormsg;
        if (optional_param('no_id_card_data', false, PARAM_BOOL)) {
            $errormsg = get_string('no_id_card_data', 'auth_est_id_card');
        } else if (optional_param('no_user_with_id', false, PARAM_BOOL)) {
            $errormsg = get_string('no_user_with_id', 'auth_est_id_card');
        }
    }
}

