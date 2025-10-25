<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WRAP_Loader {

    public function init() {
        require_once WRAP_PATH . 'core/class-wrap-cpt.php';
        require_once WRAP_PATH . 'core/class-wrap-taxonomy.php';
        
        $cpt = new WRAP_CPT();
        $cpt->register();

        $tax = new WRAP_Taxonomy();
        $tax->register();
    }
}
