<?php
// This file is part of a 3rd party created module for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language file.
 *
 * @package    block_credits
 * @copyright  2023 Institut français du Japon
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['actinguserid'] = 'ID utilisateur actif';
$string['addcredits'] = 'Ajouter des crédits';
$string['adjustquantity'] = 'Modifier la quantité';
$string['amount'] = 'Quantité';
$string['available'] = 'Disponible';
$string['backtocourse'] = 'Retour au cours';
$string['backtodashboard'] = 'Retour au tableau de bord';
$string['balance'] = 'Solde';
$string['cannotaudituser'] = 'Utilisateur non consultable';
$string['cannotmanageuser'] = 'Utilisateur non modifiable';
$string['changevalidity'] = 'Modifier la validité';
$string['creditedon'] = 'Crédits ajoutés le';
$string['credits'] = 'Crédits';
$string['credits:addinstance'] = 'Ajouter un bloc';
$string['credits:manage'] = 'Gérer tous les crédits';
$string['credits:myaddinstance'] = 'Ajouter le bloc à mon tableau de bord';
$string['credits:view'] = 'Voir les crédits';
$string['credits:viewall'] = 'Voir tous les crédits';
$string['creditshistory'] = 'Historique des crédits';
$string['csvfile'] = 'Fichier CSV';
$string['csvfile_help'] = 'Télécharger un fichier CSV containant les colonnes suivantes :

- userid: (requis) User ID du bénéficiaire des crédits
- amount: (requis) La quantité de crédits
- validuntil: (requis) Date de validité des crédits en format YYYY-MM-DD
- publicnote: (optionnel) Un commentaire public
- privatenote: (optionnel) Un commentaire privé';
$string['csvfieldseparator'] = 'Séparateur de champs du CSV';
$string['downloadtxs'] = 'Télécharger l\'historique des opérations';
$string['expired'] = 'Expiré';
$string['expirenow'] = 'Mettre à expiration';
$string['expiringsoon'] = 'Expiration prochaine';
$string['history'] = 'Historique';
$string['import'] = 'Importer';
$string['importcredits'] = 'Import des crédits';
$string['importingxfory'] = 'Import de {$a->amount} crédits pour {$a->name}.';
$string['importlinenskippedmissing'] = 'Ligne {$a} sautée : utilisateur invalide ou montant vide.';
$string['importresults'] = 'Résultats de l\'import';
$string['label'] = 'Commentaire';
$string['manage'] = 'Gérer les crédits';
$string['mycredits'] = 'Mes crédits';
$string['myongoingcredits'] = 'Mes crédits en cours';
$string['ncredits'] = '{$a} crédit(s)';
$string['newtotal'] = 'Nouveau total';
$string['newtotal_help'] = 'Indiquer le nouveau montant total de crédits. Cette valeur ne peut être inférieure au nombre de crédits déjà utilisés.';
$string['nocreditsavailableatm'] = 'Vous n\'avez pas assez de crédits actuellement.';
$string['nocreditstoexpire'] = 'Pas assez de crédits ({$a->available}/{$a->required}).';
$string['note'] = 'Note';
$string['notenoughtcredits'] = 'Pas assez de crédits ({$a->available}/{$a->required}).';
$string['other'] = 'Autre';
$string['pastcredits'] = 'Mes crédits terminés';
$string['pluginname'] = 'Crédits';
$string['privatenote'] = 'Note privée';
$string['publicnote'] = 'Note publique';
$string['purchase'] = 'Achat';
$string['reason'] = 'Motif';
$string['reasonexpired'] = 'Crédit(s) expiré(s).';
$string['reasonextended'] = 'Changement de date d\'expiration de crédits : {$a->from} > {$a->to}.';
$string['reasonimported'] = 'Crédits importés par l\'administrateur.';
$string['reasonother'] = 'Mise à jour de crédit';
$string['reasonpurchase'] = 'Achat de crédits, valides jusqu\'au {$a->validuntil}.';
$string['reasonrefundafterexpiry'] = 'Crédits rendus après expiration.';
$string['reasonrefunded'] = 'Crédits annulés';
$string['reasonrevived'] = 'Restauration de crédits précédemment expirés.';
$string['reasontotalchanged'] = 'Total des crédits modifié de {$a->from} à {$a->to}.';
$string['recordedon'] = 'Enregistré le';
$string['refund'] = 'Annulation';
$string['removefilter'] = 'Désactiver le filtre';
$string['soonestexpiry'] = 'Date d\'expiration la plus proche';
$string['systemuser'] = 'Système';
$string['taskexpirecredits'] = 'Procéder à l\'expiration des crédits.';
$string['total'] = 'Total';
$string['transactions'] = 'Opérations';
$string['transactionsfilteredforid'] = 'Liste des opérations pour le paquet de crédits n°{$a}';
$string['used'] = 'Utilisé';
$string['usernotenrolledincurrentcourse'] = 'Cet utilisateur n\'est pas inscrit dans le cours.';
$string['userscredits'] = 'Etat des crédits de l\'utilisateur';
$string['validuntil'] = 'Date d\'expiration';
$string['valuecannotbelessthan'] = 'Cette valeur ne peut pas être inférieure à {$a}.';
$string['viewall'] = 'Voir les crédits';
