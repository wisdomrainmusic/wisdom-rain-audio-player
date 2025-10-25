<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WRAP_Loader {

    public function init() {
        require_once WRAP_PATH . 'core/class-wrap-cpt.php';
        $cpt = new WRAP_CPT();
        $cpt->register();
    }
}
