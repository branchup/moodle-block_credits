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

$string['actinguserid'] = 'アクティングユーザーID';
$string['addcredits'] = 'クレジットを追加';
$string['adjustquantity'] = '数量を調整';
$string['amount'] = '数量';
$string['available'] = '利用可能';
$string['backtocourse'] = 'コースに戻る';
$string['backtodashboard'] = 'ダッシュボードに戻る';
$string['balance'] = '残高';
$string['cannotaudituser'] = 'ユーザーを監査できません';
$string['cannotmanageuser'] = 'ユーザーを管理できません';
$string['changevalidity'] = '有効期限を変更';
$string['creditedon'] = 'クレジット追加日';
$string['credits'] = 'クレジット';
$string['credits:addinstance'] = 'ブロックを追加';
$string['credits:manage'] = 'すべてのクレジットを管理';
$string['credits:myaddinstance'] = 'ブロックをダッシュボードに追加';
$string['credits:view'] = 'クレジットを表示';
$string['credits:viewall'] = 'すべてのクレジットを表示';
$string['creditshistory'] = 'クレジット履歴';
$string['csvfile'] = 'CSVファイル';
$string['csvfile_help'] = '以下の列を含むCSVファイルをダウンロードしてください：

- userid：（必須）クレジットの受取人のユーザーID
- amount：（必須）クレジットの数量
- validuntil：（必須）クレジットの有効期限（YYYY-MM-DD形式）
- publicnote：（オプション）パブリックコメント
- privatenote：（オプション）プライベートコメント';
$string['csvfieldseparator'] = 'CSVファイルのフィールドセパレータ';
$string['downloadtxs'] = '操作履歴をダウンロード';
$string['expired'] = '期限切れ';
$string['expirenow'] = '今すぐ期限切れにする';
$string['expiringsoon'] = 'まもなく期限切れ';
$string['history'] = '履歴';
$string['import'] = 'インポート';
$string['importcredits'] = 'クレジットのインポート';
$string['importingxfory'] = '{$a->name} への{$a->amount} クレジットをインポート中。';
$string['importlinenskippedmissing'] = '行{$a} をスキップ：無効なユーザーまたは空の金額。';
$string['importresults'] = 'インポート結果';
$string['label'] = 'コメント';
$string['manage'] = 'クレジットの管理';
$string['mycredits'] = 'マイクレジット';
$string['myongoingcredits'] = '使用可能なクレジット';
$string['ncredits'] = '{$a} クレジット';
$string['newtotal'] = '新しい合計';
$string['newtotal_help'] = '新しいクレジットの合計額を指定してください。この値は既に使用済みのクレジット数よりも低くすることはできません。';
$string['nocreditsavailableatm'] = '現在、十分なクレジットがありません。';
$string['nocreditstoexpire'] = 'クレジットが不足しています（{$a->available}/{$a->required}）。';
$string['note'] = 'ノート';
$string['notenoughtcredits'] = 'クレジットが不足しています（{$a->available}/{$a->required}）。';
$string['other'] = 'その他';
$string['pastcredits'] = '使用済み・期限切れのクレジット';
$string['pluginname'] = 'クレジット';
$string['privatenote'] = 'プライベートノート';
$string['publicnote'] = 'パブリックノート';
$string['purchase'] = '購入';
$string['reason'] = '理由';
$string['reasonexpired'] = 'クレジットが期限切れです。';
$string['reasonextended'] = 'クレジットの有効期限が変更されました：{$a->from} > {$a->to}。';
$string['reasonimported'] = '管理者によるクレジットのインポート。';
$string['reasonother'] = 'クレジットの更新';
$string['reasonpurchase'] = 'クレジットの購入、有効期限は{$a->validuntil}までです。';
$string['reasonrefundafterexpiry'] = '期限切れ後のクレジットの返金。';
$string['reasonrefunded'] = 'クレジットがキャンセルされました';
$string['reasonrevived'] = '以前に期限切れになったクレジットが復活しました。';
$string['reasontotalchanged'] = 'クレジットの合計が{$a->from}から{$a->to}に変更されました。';
$string['recordedon'] = '記録日';
$string['refund'] = '返金';
$string['removefilter'] = 'フィルターの無効化';
$string['soonestexpiry'] = '最も早い有効期限';
$string['systemuser'] = 'システムユーザー';
$string['taskexpirecredits'] = 'クレジットの期限切れ処理';
$string['total'] = '合計';
$string['transactions'] = '取引';
$string['transactionsfilteredforid'] = 'クレジットパッケージ{$a}の取引一覧';
$string['used'] = '使用済み';
$string['usernotenrolledincurrentcourse'] = 'このユーザーは現在のコースに登録されていません。';
$string['userscredits'] = 'ユーザーのクレジット状況';
$string['validuntil'] = '有効期限';
$string['valuecannotbelessthan'] = 'この値は{$a}未満にすることはできません。';
$string['viewall'] = 'すべてのクレジットを表示';

