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

/**
 * Delegate click.
 *
 * @param {String} regionSelector
 * @param {String} selector
 * @param {Function} callback
 */
function delegateClick(regionSelector, selector, callback) {
  document.querySelector(regionSelector).addEventListener('click', (e) => {
    const target = e.target.closest(selector);
    if (!target) {
      return;
    }
    e.preventDefault();
    callback(target);
  });
}

export const registerAddCreditButton = (selector) => {
  document.querySelector(selector).addEventListener('click', (e) => {
    e.preventDefault();
    const userId = e.currentTarget.dataset.userid;
    const pageCtxId = e.currentTarget.dataset.pagectxid;
    openAddCreditModal(userId, pageCtxId);
  });
};

export const delegateExtendValidityButton = (regionSelector, selector) => {
  delegateClick(regionSelector, selector, (target) => {
    const creditId = target.dataset.creditid;
    const pageCtxId = target.dataset.pagectxid;
    openExtendValidityModal(creditId, pageCtxId);
  });
};

export const delegateExpireNowButton = (regionSelector, selector) => {
  delegateClick(regionSelector, selector, (target) => {
    const creditId = target.dataset.creditid;
    const pageCtxId = target.dataset.pagectxid;
    openExpireNowModal(creditId, pageCtxId);
  });
};

const openAddCreditModal = (userId, pageCtxId) => {
  const modal = new ModalForm({
    formClass: 'block_credits\\form\\credit_user_dynamic_form',
    modalConfig: {scrollable: false, title: getString('addcredits', 'block_credits')},
    args: {userid: userId, lockuser: userId > 0, pagectxid: pageCtxId},
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

const openExtendValidityModal = (creditId, pageCtxId) => {
  const modal = new ModalForm({
    formClass: 'block_credits\\form\\extend_validity_dynamic_form',
    modalConfig: {scrollable: false, title: getString('extendvalidity', 'block_credits')},
    args: {creditid: creditId, pagectxid: pageCtxId},
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

const openExpireNowModal = (creditId, pageCtxId) => {
  const modal = new ModalForm({
    formClass: 'block_credits\\form\\expire_now_dynamic_form',
    modalConfig: {scrollable: false, title: getString('expirenow', 'block_credits')},
    args: {creditid: creditId, pagectxid: pageCtxId},
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
