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

$string['actinguserid'] = 'Acting user';
$string['addcredits'] = 'Add credits';
$string['adjustquantity'] = 'Adjust quantity';
$string['alreadyexpired'] = 'Already expired';
$string['amount'] = 'Amount';
$string['available'] = 'Available';
$string['balance'] = 'Balance';
$string['backtocourse'] = 'Back to course';
$string['backtodashboard'] = 'Back to dashboard';
$string['cannotaudituser'] = 'Cannot audit user';
$string['cannotmanageuser'] = 'Cannot manage user';
$string['changevalidity'] = 'Change validity';
$string['creditedon'] = 'Credited on';
$string['credits:addinstance'] = 'Add a new block';
$string['credits:manage'] = 'Manage anybody\'s credits';
$string['credits:myaddinstance'] = 'Add the block to my dashboard';
$string['credits:receivemanagernotifications'] = 'Receive manager notifications.';
$string['credits:view'] = 'View credits';
$string['credits:viewall'] = 'View anybody\'s credits';
$string['credits'] = 'Credits';
$string['creditshistory'] = 'Credits history';
$string['csvfile'] = 'CSV file';
$string['csvfile_help'] = 'Upload a CSV file containing the following columns:

- userid: (required) The user ID to credits coins to
- amount: (required) The amount of coins to credit
- validuntil: (required) The date until which the credits can be used, in format YYYY-MM-DD
- publicnote: (optional) A public note
- privatenote: (optional) A private note';
$string['csvfieldseparator'] = 'CSV field separator';
$string['downloadtxs'] = 'Download transactions';
$string['expired'] = 'Expired';
$string['expirenow'] = 'Expire now';
$string['expiringsoon'] = 'Expiring soon';
$string['import'] = 'Import';
$string['importcredits'] = 'Import credits';
$string['importingxfory'] = 'Importing {$a->amount} credits for {$a->name}.';
$string['importlinenskippedmissing'] = 'Line {$a} skipped: invalid user, or amount.';
$string['importresults'] = 'Import results';
$string['history'] = 'History';
$string['label'] = 'Label';
$string['manage'] = 'Manage';
$string['messageexpiredrefund'] = 'You are receiving this email because some credits could not be refunded, or have been refunded but are expiring soon.

- User: {$a->fullname}
- Operation ID: {$a->opid}
- Refund reason: {$a->reason}
- Quantity refunded (expiring soon): {$a->expiringsoon}
- Quantity non-refunded (expired): {$a->expired}

To manage this person\'s credits, navigate to [this page]({$a->url}).';
$string['messageexpiredrefundsubject'] = 'Expired (or expiring) credits after refund';
$string['messageprovider:expiredrefund'] = 'Expired or expiring credits being refunded.';
$string['mycredits'] = 'My credits';
$string['myongoingcredits'] = 'My ongoing credits';
$string['ncredits'] = '{$a} credit(s)';
$string['newtotal'] = 'New total';
$string['newtotal_help'] = 'Specify the new total amount of credits. This value cannot fall below the number of credits already consumed.';
$string['note'] = 'Note';
$string['nocreditstoexpire'] = 'Not enough credits ({$a->available}/{$a->required}).';
$string['nocreditsavailableatm'] = 'You currently do not have any available credits.';
$string['notenoughtcredits'] = 'Not enough credits ({$a->available}/{$a->required}).';
$string['other'] = 'Other';
$string['pastcredits'] = 'Past credits';
$string['pluginname'] = 'Credits';
$string['privatenote'] = 'Private note';
$string['publicnote'] = 'Public note';
$string['purchase'] = 'Purchase';
$string['reason'] = 'Reason';
$string['reasonexpired'] = 'Credits expired.';
$string['reasonextended'] = 'Credit validity changed from {$a->from} to {$a->to}.';
$string['reasonimported'] = 'Credits imported by administrator.';
$string['reasonother'] = 'Credit update.';
$string['reasonpurchase'] = 'Credits purchased, valid until {$a->validuntil}.';
$string['reasonrefundafterexpiry'] = 'Credits returned after their expiry.';
$string['reasonrefunded'] = 'Purchase refunded.';
$string['reasonrevived'] = 'Restore previously expired credits.';
$string['reasontotalchanged'] = 'Total credits changed from {$a->from} to {$a->to}.';
$string['recordedon'] = 'Recorded on';
$string['refund'] = 'Refund';
$string['removefilter'] = 'Remove filter';
$string['soonestexpiry'] = 'Soonest expiry';
$string['systemuser'] = 'System';
$string['taskexpirecredits'] = 'Process expired credits';
$string['total'] = 'Total';
$string['transactions'] = 'Transactions';
$string['transactionsfiltered'] = 'The transactions are filtered.';
$string['transactionsfilteredforid'] = 'Transactions filtered for CID #{$a}';
$string['used'] = 'Used';
$string['usernotenrolledincurrentcourse'] = 'The user is not enrolled in current course.';
$string['userscredits'] = 'Users\' credits';
$string['valuecannotbelessthan'] = 'The value cannot be less than {$a}.';
$string['validuntil'] = 'Valid until';
$string['viewall'] = 'View all';
