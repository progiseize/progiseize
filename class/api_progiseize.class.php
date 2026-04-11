<?php
/* Copyright (C) 2025  Progiseize <contact@progiseize.fr>
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

use Luracast\Restler\RestException;

require_once DOL_DOCUMENT_ROOT.'/api/class/api.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';

/**
 * API Progiseize - Routes personnalisées
 *
 * @access protected
 * @class  DolibarrApiAccess {@requires user,external}
 */
class Progiseize extends DolibarrApi
{
    /**
     * @var Project $project Objet projet Dolibarr
     */
    public $project;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $db;
        $this->db = $db;
        $this->project = new Project($db);
    }

    /**
     * Lier un contact externe à un projet
     *
     * Ajoute un contact (socpeople) à un projet avec le rôle spécifié.
     * Types valides : PROJECTLEADER (Chef de projet), PROJECTCONTRIBUTOR (Intervenant)
     *
     * @param int    $id        ID du projet
     * @param int    $contactid ID du contact (socpeople)
     * @param string $type      Type de rôle : PROJECTLEADER ou PROJECTCONTRIBUTOR
     *
     * @return array{success: array{code: int, message: string}}
     *
     * @url POST projects/{id}/contact/{contactid}/{type}
     *
     * @throws RestException 403 Accès refusé
     * @throws RestException 304 Contact déjà lié
     * @throws RestException 404 Projet introuvable
     * @throws RestException 500 Erreur serveur
     */
    public function addContactToProject($id, $contactid, $type)
    {
        if (!DolibarrApiAccess::$user->hasRight('projet', 'creer')) {
            throw new RestException(403, 'Permission refusée');
        }

        $result = $this->project->fetch((int) $id);
        if (!$result) {
            throw new RestException(404, 'Projet introuvable');
        }

        if (!DolibarrApi::_checkAccessToResource('projet', $this->project->id)) {
            throw new RestException(403, 'Accès non autorisé pour le login '.DolibarrApiAccess::$user->login);
        }

        $result = $this->project->add_contact((int) $contactid, $type, 'external');

        if ($result < 0) {
            throw new RestException(500, 'Erreur lors de l\'ajout du contact : '.$this->project->error);
        }

        if ($result == 0) {
            throw new RestException(304, 'Ce contact est déjà lié au projet');
        }

        return array(
            'success' => array(
                'code'    => 200,
                'message' => 'Contact lié au projet avec succès',
            )
        );
    }
}
