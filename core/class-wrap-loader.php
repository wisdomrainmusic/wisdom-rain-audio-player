<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WRAP_Loader {

    /**
     * Stored module instances to keep hooks active after init.
     *
     * @var WRAP_Shortcode
     */
    private $shortcode;

    public function init() {
        require_once WRAP_PATH . 'core/class-wrap-cpt.php';
        require_once WRAP_PATH . 'core/class-wrap-taxonomy.php';
        require_once WRAP_PATH . 'core/class-wrap-shortcode.php';
        require_once WRAP_PATH . 'admin/class-wrap-metabox.php';

        $cpt = new WRAP_CPT();
        $cpt->register();

        $tax = new WRAP_Taxonomy();
        $tax->register();

        $metabox = new WRAP_Metabox();
        $metabox->register();

        $this->shortcode = new WRAP_Shortcode();
        $this->shortcode->register();
    }
}
