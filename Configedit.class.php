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
		$this->config = $freepbx->Config;
		$this->brand = $this->config->get("DASHBOARD_FREEPBX_BRAND");
		$this->astetcdir = $this->config->get('ASTETCDIR');
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

	public function ajaxRequest($req, &$setting) {
		return true;
	}
	public function ajaxHandler() {
		$ret = array("status" => false);
		switch ($_REQUEST['command']) {
			case "load":
				$files = $this->getValidFiles();
				if(isset($files[$_POST['path']]['files'][$_POST['file']])) {
					$file = $_POST['path']."/".$_POST['file'];
					if(is_readable($file)) {
						return array(
							"status" => true,
							"writable" => $files[$_POST['path']]['files'][$_POST['file']]['writable'],
							"contents" => file_get_contents($file),
							"modeFile" => "asterisk",
							"mime" => "text/x-asterisk"
						);
					} else {
						return array("status" => false, "message" => sprintf(_("File %s is not readable"),$file));
					}
				} else {
					return array("status" => false, "message" => sprintf(_("File %s is not valid"),$file));
				}
			break;
			case "save":
				$files = $this->getValidFiles();
				if(isset($files[$_POST['path']]['files'][$_POST['file']])) {
					$file = $_POST['path']."/".$_POST['file'];
					if(is_writable($file)) {
						file_put_contents($file, $_POST['contents']);
						return array("status" => true);
					} else {
						return array("status" => false, "message" => sprintf(_("File %s is not writable"),$file));
					}
				} else {
					return array("status" => false, "message" => sprintf(_("File %s is not valid"),$file));
				}
			break;
		}
		return $ret;
	}

	public function showPage() {
		return load_view(__DIR__."/views/main.php",array("listings" => $this->getValidFiles(), "brand" => $this->brand));
	}

	private function getValidFiles() {
		$files = array(
			$this->astetcdir => array(
				'name' => _('Asterisk Configuration Files'),
				'mime' => 'text/x-asterisk',
				'files' => array()
			)
		);
		foreach(glob($this->astetcdir."/*_custom*") as $file) {
			$files[$this->astetcdir]['files'][basename($file)] = array(
				"file" => basename($file),
				"size" => filesize($file),
				"writable" => (is_writable($file))
			);
		}
		if(file_exists($this->astetcdir."/freepbx_menu.conf")) {
			$files[$this->astetcdir]['files']['freepbx_menu.conf'] = array(
				"file" => "freepbx_menu.conf",
				"size" => filesize($this->astetcdir."/freepbx_menu.conf"),
				"writable" => (is_writable($this->astetcdir."/freepbx_menu.conf"))
			);
		}
		return $files;
	}
}
