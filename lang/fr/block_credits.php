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
$string['alreadyexpired'] = 'Déjà expiré';
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
$string['credits:receivemanagernotifications'] = 'Recevoir les notifications de manager.';
$string['credits:view'] = 'Voir les crédits';
$string['credits:viewall'] = 'Voir tous les crédits';
$string['creditshistory'] = 'Historique des crédits';
$string['csvfieldseparator'] = 'Séparateur de champs du CSV';
$string['csvfile'] = 'Fichier CSV';
$string['csvfile_help'] = 'Télécharger un fichier CSV containant les colonnes suivantes :

- userid: (requis) User ID du bénéficiaire des crédits
- amount: (requis) La quantité de crédits
- validuntil: (requis) Date de validité des crédits en format YYYY-MM-DD
- publicnote: (optionnel) Un commentaire public
- privatenote: (optionnel) Un commentaire privé';
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
$string['messageexpirednotice'] = 'Nous vous informons que {$a->credits} de vos crédits ont expiré.

Veuilez consulter [la plateforme d\'apprentissage]({$a->wwwroot}) pour voir le détail de vos crédits.';
$string['messageexpirednoticesubject'] = '{$a->credits} credits ont expiré.';
$string['messageexpiredrefund'] = 'Vous recevez cet email car certains crédits n\'ont pas pu être remboursés, ou ont été remboursés mais expirent bientôt.

- Utilisateur : {$a->fullname}
- ID de l\'opération : {$a->opid}
- Raison du remboursement : {$a->reason}
- Quantité remboursée (expiration prochaine) : {$a->expiringsoon}
- Quantité non remboursée (expirée) : {$a->expired}

Pour gérer les crédits de cette personne, veuillez accéder à [cette page]({$a->url}).';
$string['messageexpiredrefundsubject'] = 'Crédits expirés (ou en cours d\'expiration) après restitution';
$string['messageexpirynotice'] = 'Cet email est un rappel pour vous informer que certains de vos crédits vont prochainement arriver à expiration.

- Crédits : {$a->credits}
- Date d\'expiration : {$a->expirydate}

Veuillez utiliser vos crédits avant leur expiration pour éviter toute perte. Vous pouvez le faire sur [votre plateforme d\'apprentissage]({$a->wwwroot}).';
$string['messageexpirynoticesubject'] = '{$a->credits} crédits expirent dans moins de {$a->days} jours.';
$string['messageprovider:expiredrefund'] = 'Crédits expirés ou en cours d\'expiration qui sont restitués.';
$string['messageprovider:expirynotice'] = 'Notifications d\'expiration';
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
$string['reasonexpirynoticesent'] = 'Notification d\'expiration prochaine envoyée ({$a->credits} crédits le {$a->expiry}).';
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
$string['tasksendexpirynotices'] = 'Envoyer les notifications d\'expiration';
$string['total'] = 'Total';
$string['transactions'] = 'Opérations';
$string['transactionsfiltered'] = 'Les transactions sont filtrées.';
$string['transactionsfilteredforid'] = 'Liste des opérations pour le paquet de crédits n°{$a}';
$string['used'] = 'Utilisé';
$string['usernotenrolledincurrentcourse'] = 'Cet utilisateur n\'est pas inscrit dans le cours.';
$string['userscredits'] = 'Etat des crédits de l\'utilisateur';
$string['validuntil'] = 'Date d\'expiration';
$string['validuntilchangenote'] = 'Veuillez noter que modifier la validité réinitialisera les notifications d\'expiration déjà envoyées.';
$string['valuecannotbelessthan'] = 'Cette valeur ne peut pas être inférieure à {$a}.';
$string['viewall'] = 'Voir les crédits';
