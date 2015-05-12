<?php
// vim: set ai ts=4 sw=4 ft=php:
/**
 * This is the User Control Panel Object.
 *
 * License for all code of this FreePBX module can be found in the license file inside the module directory
 * Copyright 2006-2014 Schmooze Com Inc.
 */

class Configedit implements BMO {
	private $brand = 'FreePBX';
	public function __construct($freepbx = null) {
		if ($freepbx == null) {
			throw new Exception("Not given a FreePBX Object");
        }

		$this->FreePBX = $freepbx;
		$this->Userman = $this->FreePBX->Userman;
		$this->db = $freepbx->Database;
		$this->brand = \FreePBX::Config()->get("DASHBOARD_FREEPBX_BRAND");
	}

	public function install() {
	}
	public function uninstall() {
	}
	public function backup(){

	}
	public function restore($backup){
	}
    
    public function doConfigPageInit($display) {
    }
}
