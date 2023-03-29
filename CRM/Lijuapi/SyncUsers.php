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

class CRM_Lijuapi_SyncUsers
{

  private $liju_users = [];
  private $sds_group_members = [];

  /**
   * @throws CRM_Lijuapi_Exceptions_UserSyncException
   * @throws CiviCRM_API3_Exception
   */
  public function __construct()
  {
    // get all users from LiJu API, save locally
    $result = civicrm_api3('Liju', 'getusers');
    if ($result['is_error'] != 0) {
      Civi::log()->log("ERROR", 'Error fetching Users from LiJu Database. Error Message: ' . $result['error_message']);
      throw new CRM_Lijuapi_Exceptions_UserSyncException("Error fetching Users from LiJu Database. Code: {$result['error_message']}");
    }
    // save liJu API Users in local cache
    $this->liju_users = $result['values']['liju_api_users'];

    // Get all SDS group contact_ids so we can check is_sds_member for each user
    $this->$sds_group_members = CRM_Lijuapi_Utils::get_sds_group();
  }

  /**
   * @return void
   * @throws CiviCRM_API3_Exception
   */
  public function run($group_id = NULL)
  {
    CRM_Lijuapi_Utils::log("Syncing CiviCRMUsers to LijuApi Users");
    // filter for certain LV Groups
    $lv_groups = CRM_Lijuapi_Utils::$landesverband_mapping;
    if (!empty($group_id)) {
      foreach (CRM_Lijuapi_Utils::$landesverband_mapping as $lv => $civi_group_id) {
        // we add only the filtered group ID to the LV array
        if ($group_id == $civi_group_id) {
          $lv_groups = [];
          $lv_groups[$lv] = $civi_group_id;
        }
      }
    }
    // Iterate Users from  per group!
    foreach ($lv_groups as $lv => $civi_group_id) {
      $group_name = CRM_Lijuapi_Utils::get_group_name($civi_group_id);
      CRM_Lijuapi_Utils::log("Getting users for {$group_name}");
      $result = civicrm_api3('GroupContact', 'get', [
        'sequential' => 1,
        'group_id' => $group_name,
        'options' => ['limit' => 0],
        'return' => ["contact_id"],
      ]);
      // this can be
      CRM_Lijuapi_Utils::log("Users in Group {$civi_group_id}: " . json_encode($result['values']));
      $debug_counter = 0;
      foreach ($result['values'] as $civi_group_info) {
        try {
          $contact_id = $civi_group_info['contact_id'];
          $civi_user_email = CRM_Lijuapi_Utils::get_user_primary_email($contact_id, true);
          $is_sds_member = in_array($contact_id, $this->$sds_group_members);

          $liju_user = $this->get_liju_user_record_by_id($contact_id);
          if (empty($liju_user)) {
            // create Link and save it to user if no Link is available already
            if (!CRM_Lijuapi_Utils::has_invite_link($contact_id)) {
              $result = civicrm_api3('Liju', 'createinvite', [
                'email' => $civi_user_email,
                'liju_member_id' => $contact_id,
                'verband' => $lv,
                'is_sds_member' => $is_sds_member,
              ]);
              CRM_Lijuapi_Utils::add_link_to_user($contact_id, $result['values']['invite_link']);
            } else {
              // only for debugging
              CRM_Lijuapi_Utils::log("User ($contact_id) is not available in LiJu API but already has an invite Link");
            }
            continue;
          }
          // delete invite link from user here, since we have a match!
          CRM_Lijuapi_Utils::remove_invite_link_from_user($contact_id);

          if ($liju_user['is_sds_member'] != $is_sds_member) {
            CRM_Lijuapi_Utils::log("CiviCRM User $civi_user_email($contact_id) changed SDS membership (" . ($is_sds_member ? 'entered' : 'exited') . "). Updating User Record.");
            $this->update_user_record($contact_id, null, null, null, $is_sds_member);
          }

          if ($liju_user['email'] != $civi_user_email) {
            CRM_Lijuapi_Utils::log("CiviCRM User $civi_user_email($contact_id) changed their email ('{$liju_user['email']}' in LJS Intranet) without the email_change hook noticing. Updating User Record.");
            $this->update_user_record($contact_id, null, $civi_user_email, null, null);
          }

          if ($liju_user['verband'] != $lv) {
            CRM_Lijuapi_Utils::log("CiviCRM User $civi_user_email($contact_id) changed their verband ('{$liju_user['verband']}' in LJS Intranet, now $lv) without the verband_change hook noticing. Updating User Record.");
            $this->update_user_record($contact_id, null, null, $lv, null);
          }

        } catch (Exception $e) {
          Civi::log()->log("ERROR", " Error syncing contact $contact_id. {$e->getMessage()}");
        }
      }
    }
  }


  /**
   * @param $user_emails
   * @return mixed
   * @throws CRM_Lijuapi_Exceptions_NoEmailForMemberException
   */
  private function get_primary_email($user_emails)
  {
    if (empty($user_emails)) {
      throw new CRM_Lijuapi_Exceptions_NoEmailForMemberException("User doesn't have an Email address.");
    }
    foreach ($user_emails as $email_record) {
      if ($email_record['is_primary'] == 1) {
        return $email_record['email'];
      }
    }
    // this shouldn't happen!
    throw new CRM_Lijuapi_Exceptions_NoEmailForMemberException("User doesn't have an primary Email! o_O");
  }


  /**
   * @param $contact_id
   * @return false|int|string
   */
  private function get_liju_user_record_by_id($contact_id)
  {
    return array_search($contact_id, array_column($this->liju_users, 'ljs_memberid'));
  }

  /**
   * @param $current_contact_id
   * @param $new_contact_id
   * @param $email
   * @param $lv
   * @param $is_sds_member
   * @return void
   * @throws CiviCRM_API3_Exception
   */
  private function update_user_record($current_contact_id, $new_contact_id, $email, $lv, $is_sds_member)
  {
    $result = civicrm_api3('Liju', 'updateuser', [
      'old_user_id' => $current_contact_id,
      'liju_member_id' => $new_contact_id,
      'email' => $email,
      'verband' => $lv,
      'is_sds_member' => $is_sds_member,
    ]);
    if ($result['is_error'] != 0) {
      Civi::log()->log("DEBUG", "Error occured while Updating User Record in LiJu Member database. " . $result['error_message']);
      throw new Exception("Error occured while Updating User Record in LiJu Member database. " . $result['error_message']);
    }
  }
}
