<?php
/* 
 * Copyright (C) 2022 ProgiSeize <contact@progiseize.fr>
 *
 * This program and files/directory inner it is free software: you can 
 * redistribute it and/or modify it under the terms of the 
 * GNU Affero General Public License (AGPL) as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AGPL for more details.
 *
 * You should have received a copy of the GNU AGPL
 * along with this program.  If not, see <https://www.gnu.org/licenses/agpl-3.0.html>.
 */


$res=0;
if (! $res && file_exists("../main.inc.php")): $res=@include '../main.inc.php'; endif;
if (! $res && file_exists("../../main.inc.php")): $res=@include '../../main.inc.php'; endif;

//require_once DOL_DOCUMENT_ROOT.'/admin/dolistore/class/dolistore.class.php';

dol_include_once('./progiseize/lib/progiseize.lib.php');
dol_include_once('./progiseize/class/progiseizemodule.class.php');

// ON RECUPERE LA VERSION DE DOLIBARR
$version = explode('.', DOL_VERSION);

// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");

// Protection if external user
if ($user->societe_id > 0): accessforbidden(); endif;


/*******************************************************************
* VARIABLES
********************************************************************/
$pgsz = new ProgiseizeModule($db);

$list_modules = $pgsz->getModulesInstalled();

/*******************************************************************
* ACTIONS
********************************************************************/
/*if(!empty($mod->menu)): 
						$module_link = dol_buildpath($mod->menu[0]['url'],1);
						$module_label = '<a href="'.$module_link.'" title="'.$langs->trans('pgsz_alt_module_goto').'">';
						$module_label .= $mod->name; 
						$module_label .= '</a>'; 
					else: $module_label = $mod->name; endif;*/
/***************************************************
* VIEW
****************************************************/

llxHeader('','Modules Progiseize',''); ?>

<?php // dol_htmloutput_errors($errmsg); ?>

<!-- CONTENEUR GENERAL -->
<div class="dolpgs-main-wrapper">

	<h1><i class="far fa-list-alt"></i> <?php echo $langs->transnoentities('pgsz_listModulesInstalled'); ?></h1>

	<div class="dolpgs-flex-wrapper">
	<?php foreach ($list_modules as $mod):

		$mod_enabled = $conf->global->{$mod->const_name}; ?>

		<div class="dolpgs-flex-item flex-4">

			<div class="module-card">

				<div class="module-title <?php echo $mod_enabled?'enabled':'disabled'; ?>">
					<div class="update-info">
						<?php if($mod->needUpdate): ?>
							<span class="need-update"><?php echo $langs->transnoentities('pgsz_moduleUpdateAvailable',$mod->lastVersion); ?></span>
						<?php else: ?>
							<span><?php echo $langs->trans('pgsz_moduleUpToDate'); ?> <i class="fas fa-check"></i></span>
						<?php endif; ?>
					</div>
					<h3><?php echo $mod->name; ?></h3>
				</div>
				<p class="module-desc"><?php echo $langs->transnoentities($mod->descriptionlong); ?></p>

				<ul class="module-infos-list">

					<li class="module-info">
						<span class="dolpgs-font-semibold">Statut : </span>
						<?php echo ($mod_enabled)?$langs->transnoentities('pgsz_moduleActive'):$langs->transnoentities('pgsz_moduleInactive'); ?>
						
					</li>

					<li class="module-info">
						<span class="dolpgs-font-semibold">Version : </span>
						<span class="<?php if($mod->needUpdate): echo 'version-outdated'; endif; ?>"><?php echo $mod->version; ?></span>
					</li>

					<?php // LIEN VERS LA PAGE D'OPTION ?>
					<?php if(!empty($mod->config_page_url)): $opt = explode('@', $mod->config_page_url);  ?>
						<li class="module-info module-setup"><a href="<?php echo '../'.$opt[1].'/admin/'.$opt[0]; ?>" title="<?php print $langs->trans('pgsz_gotoSetup'); ?>"><i class="fas fa-cog"></i></a></li>
					<?php endif; ?>

				</ul>
			</div>
		</div>

	<?php endforeach; ?>


</div>



<?php

// End of page
llxFooter();
$db->close();

?>