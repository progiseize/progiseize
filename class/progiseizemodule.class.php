<?php
/* Copyright (C) 2021  Progiseize */

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';

// ON CHARGE LA LIBRAIRIE DU MODULE
//dol_include_once('./progiseize/lib/progiseize.lib.php');

class ProgiseizeModule {

	public $db;

	/**/
	public function __construct($db){
		$this->db = $db;
	}

	/**/
	public function getModulesInstalled(){

		global $conf;

		// ON RECUPERE LES INFOS DES MODULES INSTALLES
		$modules_dir = dolGetModulesDirs();
		$modules_set = array();
		foreach ($modules_dir as $dir): $handle = @opendir($dir);

			if (is_resource($handle)):
				while (($file = readdir($handle)) !== false):
					if (is_readable($dir.$file) && substr($file, 0, 3) == 'mod' && substr($file, dol_strlen($file) - 10) == '.class.php'):

						// ON RECUPERE LA CLASSE DU MODULE
						$modName = substr($file, 0, dol_strlen($file) - 10);

						// 
						if($modName):

							// On inclut le fichier
							$res = include_once $dir.$file;

							//Si il y a une classe
							if (class_exists($modName)):

								// On instancie cette classe
								$module = new $modName($this->db);

								// S'il appartient à la famille progiseize
								if(!strcasecmp($module->family,'progiseize')):
										
									// Si url de version
									if(!empty($module->url_last_version)):

										require_once DOL_DOCUMENT_ROOT.'/core/lib/geturl.lib.php';
										$lastVersionInfos = getURLContent($module->url_last_version);

										if(isset($lastVersionInfos['content']) && strlen($lastVersionInfos['content']) < 30):
											$module->lastVersion = preg_replace("/[^a-zA-Z0-9_\.\-]+/", "", $lastVersionInfos['content']);
											if (version_compare($module->lastVersion, $module->version) > 0): $module->needUpdate = true; endif;
										endif;
									endif;

									$modules_set[$modName] = $module;
								endif;
							endif;
						endif;

					endif;
				endwhile;
			endif;

		endforeach;	
		
		uasort($modules_set, fn($a, $b) => strcmp($a->module_position, $b->module_position));
		return $modules_set;


	}

	public function clean_modules_vars(){

		$this->db->begin();

		$error = 0;
		$success = 0;
		$vars_todel = array(
			'modProgiseize' => array('JS','HOOKS'),
			'modFastFactSupplier' => array('CSS','JS','HOOKS'),
			'modGenRapports' => array('CSS','JS','HOOKS'),
			'modFusionCC' => array('CSS','JS','HOOKS'),
			'modLoginPlus' => array('JS'),
			'modGestionParc' => array('CSS','JS')
		);

		foreach($this->modules_local as $localMod_key => $localMod):
			if(isset($vars_todel[$localMod_key])):

				$sql_checkconst = "SELECT rowid FROM ".MAIN_DB_PREFIX."const";
				$sql_checkconst .= " WHERE name IN (";

				$i = 0;
				foreach($vars_todel[$localMod_key] as $nameVar): $i++;
					if($i > 1): $sql_checkconst .= ','; endif;
					$sql_checkconst .= "'".$localMod->const_name."_".$nameVar."'";
				endforeach;			
				
				$sql_checkconst .= ")";

				$result_checkconst = $this->db->query($sql_checkconst);

				if($result_checkconst):
				    if($result_checkconst->num_rows > 0):
				        while($const_todel = $this->db->fetch_object($result_checkconst)):
				            $sql_delconst = "DELETE FROM ".MAIN_DB_PREFIX."const";
				            $sql_delconst .= " WHERE rowid = ".$const_todel->rowid;
				            $result_delconst = $this->db->query($sql_delconst);
				            if(!$result_delconst): $error++; else: $success++;endif;
				        endwhile;
				    endif;
				    else: $error++;
				endif;

			endif;
		endforeach;

		if($error): $this->db->rollback(); return -1;
		else: $this->db->commit(); return $success;
		endif;

		
	}
}