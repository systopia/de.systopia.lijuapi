<?php
/*-------------------------------------------------------+
| SYSTOPIA Liju API Extension                            |
| Copyright (C) 2022 SYSTOPIA                            |
| Author: P. Batroff (batroff@systopia.de)               |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/


use CRM_Lijuapi_ExtensionUtil as E;

/**
 * Class to Create user invite links for either a given group (id)
 * Or sync all users.
 * Alternatively a number oof links can be specified as well.
 */
class CRM_Lijuapi_SyncUserInvites {

  private $groups;
  private $user_count;
  private $current_counter;

  /**
   * @param $group_id
   * @param $count
   * @throws CRM_Lijuapi_Exceptions_GroupIdNotLandesverbandException
   */
  public function __construct($group_id = NULL, $count = NULL) {
    if (!empty($group_id)) {
      $lv = CRM_Lijuapi_Utils::get_lv_from_group_id($group_id);
      $this->groups[$lv] = $group_id;
    } else {
      $this->groups = CRM_Lijuapi_Utils::get_lv_mapping();
    }
    $this->user_count = $count;
    $this->current_counter = 0;
  }

  /**
   * Runner function for API job
   * @return int
   */
  public function run() {
    try {
      foreach ($this->groups as $lv => $group_id ) {
        $this->get_links_for_group($group_id, $lv);
      }
    } catch (CRM_Lijuapi_Exceptions_CreateInviteCounterExpiredException $e) {
      // Counter expired we are done
      return $this->current_counter;
    }
    return $this->current_counter;
  }

  /**
   * @param $group_id
   * @param $lv
   * @return void
   * @throws CRM_Lijuapi_Exceptions_CreateInviteCounterExpiredException
   * @throws CRM_Lijuapi_Exceptions_NoInviteLinkCustomFieldException
   * @throws CRM_Lijuapi_Exceptions_SaveInviteLinkToContactException
   * @throws CiviCRM_API3_Exception
   */
  private function get_links_for_group($group_id, $lv) {
    $result = civicrm_api3('GroupContact', 'get', [
      'sequential' => 1,
      'return' => ["contact_id"],
      'group_id' => $group_id,
      'options' => ['limit' => 0],
    ]);
    foreach ($result['values'] as $group_contact) {
      try {
        $contact_id = $group_contact['contact_id'];
        if (CRM_Lijuapi_Utils::has_invite_link($contact_id)) {
          continue;
        }
        $email = CRM_Lijuapi_Utils::get_user_primary_email($contact_id)['email'];
        $result = civicrm_api3('Liju', 'createinvite', [
          'email' => $email,
          'liju_member_id' => $contact_id,
          'verband' => $lv,
        ]);

        CRM_Lijuapi_Utils::add_link_to_user($contact_id, $result['values']['invite_link']);
        $this->counter_limit_reached();
      } catch(CRM_Lijuapi_Exceptions_CiviCRMUserEmailNotAvailableException $e) {
        CRM_Lijuapi_Utils::log("Member ({$contact_id}) doesn't have an email address. Cannot create invite Link");
      } catch(CRM_Lijuapi_Exceptions_SaveInviteLinkToContactException $e) {
        CRM_Lijuapi_Utils::log("Failed to save Link to member ({$contact_id}). Error Message: " . $e->getMessage());
      }
    }
  }


  /**
   * @return int
   */
  public function get_current_count() {
    return $this->current_counter;
  }

  /**
   * @param $counter
   * @return void
   * @throws CRM_Lijuapi_Exceptions_CreateInviteCounterExpiredException
   */
  private function counter_limit_reached() {
    if (empty($this->user_count)) {
      return;
    }
    if ($this->current_counter > $this->user_count) {
      CRM_Lijuapi_Utils::log("Counter limit reached; Finished syncing users.");
      throw new CRM_Lijuapi_Exceptions_CreateInviteCounterExpiredException("Counter limit reached; Finished syncing users.");
    }
    $this->current_counter++;
  }

}
