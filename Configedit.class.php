<?php
// vim: set ai ts=4 sw=4 ft=php:
/**
 * This is the User Control Panel Object.
 *
 * License for all code of this FreePBX module can be found in the license file inside the module directory
 * Copyright 2006-2014 Schmooze Com Inc.
 */

class Configedit extends \FreePBX_Helpers implements BMO {
	private $brand = 'FreePBX';
	private $writableFiles = array();
	public function __construct($freepbx = null) {
		if ($freepbx == null) {
			throw new Exception("Not given a FreePBX Object");
		}

		$this->FreePBX = $freepbx;
		$this->config = $freepbx->Config;
		$this->brand = $this->config->get("DASHBOARD_FREEPBX_BRAND");
		$this->astetcdir = $this->config->get('ASTETCDIR');

		//Special files that are always writable
		$this->writableFiles = array(
			$this->astetcdir."/freepbx_menu.conf",
			$this->astetcdir."/freepbx_module_admin.conf"
		);

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
		switch ($_REQUEST['command']) {
			case "load":
			case "save":
			case "add":
			case "delete":
				return true;
			break;
		}
		return false;
	}

	public function ajaxHandler() {
		$ret = array("status" => false);
		switch ($_REQUEST['command']) {
			case "add":
				$file = preg_replace("/\s+|'+|`+|\"+|<+|>+|\?+|\*|&+/","-",strtolower(basename($_POST['file'])));
				if(file_exists($this->astetcdir."/".$file)) {
					return array("status" => false, "message" => sprintf(_("File %s already exists"),$file));
				}
				if(!is_writable($this->astetcdir)) {
					return array("status" => false, "message" => sprintf(_("To able to write to %s"),$this->astetcdir));
				}
				if(touch($this->astetcdir."/".$file)) {
					$files = $this->getConfig('customFiles');
					if(empty($files) || !is_array($files)) {
						$files = array($file);
					} else {
						$files[] = $file;
					}
					$this->setConfig('customFiles',$files);
					return array("status" => true, "file" => $file);
				} else {
					return array("status" => false, "message" => sprintf(_("There was a problem creating %s"),$this->astetcdir."/".$file));
				}
			break;
			case "load":
				$files = $this->getValidFiles();
				if(isset($files[$_POST['type']][$_POST['path']]['files'][$_POST['file']])) {
					$file = $_POST['path']."/".$_POST['file'];
					if(is_readable($file)) {
						return array(
							"status" => true,
							"writable" => $files[$_POST['type']][$_POST['path']]['files'][$_POST['file']]['writable'],
							"contents" => file_get_contents($file),
							"modeFile" => "asterisk",
							"mime" => "text/x-asterisk"
						);
					} else {
						return array("status" => false, "message" => sprintf(_("File %s is not readable"),$file));
					}
				} else {
					return array("status" => false, "message" => sprintf(_("File %s is not valid"),$_POST['file']));
				}
			break;
			case "save":
				$files = $this->getValidFiles();
				$file = $_POST['path']."/".$_POST['file'];
				if(isset($files[$_POST['type']][$_POST['path']]['files'][$_POST['file']])) {
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
			case "delete":
				$files = $this->getValidFiles();
				$file = $_POST['path']."/".$_POST['file'];
				if(isset($files[$_POST['type']][$_POST['path']]['files'][$_POST['file']]) && $_POST['type'] == "custom") {
					if(is_writable($file)) {
						if(!unlink($file)) {
							return array("status" => false, "message" => sprintf(_("Unable to delete file %s"),$file));
						} else {
							$cf = $this->getConfig('customFiles');
							if(in_array($_POST['file'],$cf)) {
								$cf = array_diff($cf, array($_POST['file']));
								$this->setConfig('customFiles',$cf);
							}
							return array("status" => true);
						}
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
		$listings = $this->getValidFiles();
		$names = array();
		foreach($listings as $type) {
			foreach($type as $dir) {
				if(!is_array($dir['files'])) {
					continue;
				}
				foreach(array_keys($dir['files']) as $file) {
					$names[] = $file;
				}
			}
		}
		return load_view(__DIR__."/views/main.php",array("names" => $names, "listings" => $listings, "brand" => $this->brand));
	}

	private function getValidFiles() {
		$files['custom'] = array(
			$this->astetcdir => array(
				'name' => _('Asterisk Custom Configuration Files'),
				'mime' => 'text/x-asterisk',
				'files' => array()
			)
		);
		$files['system'] = array(
			$this->astetcdir => array(
				'name' => _('Asterisk System Configuration Files'),
				'mime' => 'text/x-asterisk',
				'files' => array()
			)
		);
		$customs = array();
		foreach(glob($this->astetcdir."/*_custom*") as $file) {
			if(is_dir($file)) {
				continue;
			}
			$customs[] = basename($file);
			$files['custom'][$this->astetcdir]['files'][basename($file)] = array(
				"type" => "custom",
				"file" => basename($file),
				"size" => filesize($file),
				"writable" => (is_writable($file))
			);
		}

		foreach($this->writableFiles as $file) {
			if(file_exists($file)) {
				$base = basename($file);
				$dir = dirname($file);
				$files['custom'][$dir]['files'][$base] = array(
					"type" => "custom",
					"file" => $base,
					"size" => filesize($dir."/".$base),
					"writable" => (is_writable($dir."/".$base))
				);
				$customs[] = $base;
				asort($files['custom'][$dir]['files']);
			}
		}
		$cf = $this->getConfig('customFiles');
		if(!empty($cf) && is_array($cf)) {
			foreach($cf as $file) {
				$file = $this->astetcdir."/".$file;
				$customs[] = basename($file);
				$files['custom'][$this->astetcdir]['files'][basename($file)] = array(
					"type" => "custom",
					"file" => basename($file),
					"size" => filesize($file),
					"writable" => (is_writable($file))
				);
			}
			if(!empty($files['custom'][$this->astetcdir]['files'])) {
				asort($files['custom'][$this->astetcdir]['files']);
			}
		}
		foreach(glob($this->astetcdir."/*") as $file) {
			if(in_array(basename($file),$customs) || is_dir($file)) {
				continue;
			}
			$files['system'][$this->astetcdir]['files'][basename($file)] = array(
				"type" => "system",
				"file" => basename($file),
				"size" => filesize($file),
				"writable" => false
			);
		}
		return $files;
	}
}
