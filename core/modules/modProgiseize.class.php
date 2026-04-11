<?php
/* Copyright (C) 2004-2018  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019  Nicolas ZABOURI         <info@inovea-conseil.com>
 * Copyright (C) 2019-2020  Frédéric France         <frederic.france@netlogic.fr>
 * Copyright (C) 2023       Anthony Damhet          <a.damhet@progiseize.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *  \defgroup   progiseize Module Framework Progiseize
 *  \brief      Module framework Progiseize
 *  \file       htdocs/custom/progiseize/core/modules/modProgiseize.class.php
 *  \ingroup    progiseize
 *  \brief      Fichier de description et activation du module Progiseize
 */
include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 *  Description and activation class for module
 */
class modProgiseize extends DolibarrModules
{
    /**
     * Constructor. Define names, constants, directories, boxes, permissions
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        global $langs, $conf;
        $this->db = $db;

        // Id for module (must be unique).
        $this->numero = 300300;

        // Key text used to identify module (for permissions, menus, etc...)
        $this->rights_class = 'progiseize';

        // Family
        $this->family = 'Progiseize';

        // Module position in the family on 2 digits ('01', '10', '20', ...)
        $this->module_position = '01';

        // $this->name is derived from get_class($this) → modProgiseize → Progiseize
        // Le nom affiché est traduit via la clé "Progiseize" dans le fichier de langue
        $this->name = preg_replace('/^mod/i', '', get_class($this));

        // Module description
        $this->description    = 'ProgiseizeDescription';
        $this->descriptionlong = 'ProgiseizeDescriptionLong';

        // Author
        $this->editor_name = 'Progiseize';
        $this->editor_url = 'https://progiseize.fr';

        // Version
        $this->version = '2.0.2';
        $this->url_last_version = "https://modules-api.progiseize.fr/modules/version/300300";

        // Key used in llx_const table to save module status enabled/disabled
        // → MAIN_MODULE_PROGISEIZE
        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);

        $this->picto = 'fa-flask_fas_#6c6aa8';

        // Define some features supported by module
        $this->module_parts = array(
            'triggers'      => 0,
            'login'         => 0,
            'substitutions' => 0,
            'menus'         => 0,
            'theme'         => 0,
            'tpl'           => 0,
            'barcode'       => 0,
            'models'        => 0,
            'css'           => array(),
            'js'            => array(),
            'hooks'         => array(),
            'dir'           => array(),
            'workflow'      => array(),
            'apis'          => 1,
        );

        // Data directories to create when module is enabled.
        $this->dirs = array();

        // Config pages
        $this->config_page_url = '';

        // Dependencies
        $this->hidden = false;
        $this->depends = array();
        $this->requiredby = array();
        $this->conflictwith = array();

        // Language files
        $this->langfiles = array("progiseize@progiseize");

        // Prerequisites
        $this->phpmin = array(7, 0);
        $this->need_dolibarr_version = array(13, 0);

        // Messages at activation
        $this->warnings_activation = array();
        $this->warnings_activation_ext = array();

        // Constants
        $this->const = array();

        if (!isset($conf->progiseize) || !isset($conf->progiseize->enabled)) {
            $conf->progiseize = new stdClass();
            $conf->progiseize->enabled = 0;
        }

        $this->tabs        = array();
        $this->dictionaries = array();
        $this->boxes       = array();
        $this->cronjobs    = array();
        $this->rights      = array();
        $this->menu        = array();
    }

    /**
     * Function called when module is enabled.
     *
     * @param  string $options Options when enabling module ('', 'noboxes')
     * @return int             1 if OK, 0 if KO
     */
    public function init($options = '')
    {
        global $conf, $langs;

        $result = $this->_load_tables('');
        if ($result < 0) {
            return -1;
        }

        $this->remove($options);

        $sql = array();

        return $this->_init($sql, $options);
    }

    /**
     * Function called when module is disabled.
     *
     * @param  string $options Options when disabling module ('', 'noboxes')
     * @return int             1 if OK, 0 if KO
     */
    public function remove($options = '')
    {
        $sql = array();
        return $this->_remove($sql, $options);
    }
}
