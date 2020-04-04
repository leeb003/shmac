<?php
/**
 * SHMAC Class options
 */

    class shmac_options {
        // properties
        public $shmac_settings;
        public $shmac_email;

        // methods
        public function __construct() {
            $this->shmac_settings = get_option('shmac_settings');
            $this->shmac_email = get_option('shmac_email');
        }
	}
