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
 * File.
 *
 * @module     block_credits/modals
 * @copyright  2023 Institut français du Japon
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalForm from 'core_form/modalform';
import {get_string as getString} from 'core/str';

export const registerAddCreditButton = (selector) => {
  document.querySelector(selector).addEventListener('click', (e) => {
    const userId = e.currentTarget.dataset.userid;
    openAddCreditModal(userId);
  });
};

const openAddCreditModal = (userId) => {
  const modal = new ModalForm({
    formClass: 'block_credits\\form\\credit_user_dynamic_form',
    modalConfig: {scrollable: false, title: getString('addcredits', 'block_credits')},
    args: {userid: userId, lockuser: userId > 0},
  });
  modal.addEventListener(modal.events.FORM_SUBMITTED, function(e) {
    if (e.detail.redirecturl) {
      window.location.href = e.detail.redirecturl;
    } else {
      window.location.reload();
    }
  });
  modal.show();
};
