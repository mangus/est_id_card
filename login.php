<?php

require('../../config.php');
require_once('auth.php');

$login = new auth_plugin_est_id_card();
$login->authenticate_with_id_card();

