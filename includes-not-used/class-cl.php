<?php

class complete_login{

	protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {

		if ( defined( 'CL_VERSION' ) ) {
			$this->version = CL_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'cl';

		$this->load_dependencies();

	}

	private function load_dependencies(){

		require_once plugin_dir_path( dirname( CL_PATH_FILE ) ) . 'includes/class-cl-loader.php';

		$this->loader = new ddtp_Db_Loader();
	
	}

}