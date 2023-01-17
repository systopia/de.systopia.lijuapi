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

class CRM_Lijuapi_SyncUsers {

  private $liju_users = [];

  /**
   * @throws CRM_Lijuapi_Exceptions_UserSyncException
   * @throws CiviCRM_API3_Exception
   */
  public function __construct() {
    // get all users from LiJu API, save locally
    $result = civicrm_api3('Liju', 'getusers');
    if($result['is_error'] != 0) {
      Civi::log()->log("ERROR", 'Error fetching Users from LiJu Database. Error Message: ' . $result['error_message']);
      throw new CRM_Lijuapi_Exceptions_UserSyncException("Error fetching Users from LiJu Database. Code: {$result['error_message']}");
    }
    // save liJu API Users in local cache
    $this->liju_users = $result['values']['liju_api_users'];
  }

  /**
   * @return void
   * @throws CiviCRM_API3_Exception
   */
  public function run($group_id = NULL) {
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
        $civi_user_email = CRM_Lijuapi_Utils::get_user_primary_email($contact_id);
        $user_record = $this->get_liju_user_by_civi_mail($civi_user_email);
        if (empty($user_record)) {
          // create Link and save it to user if no Link is available already
          if (!CRM_Lijuapi_Utils::has_invite_link($contact_id)){
            $result = civicrm_api3('Liju', 'createinvite', [
              'email' => $civi_user_email['email'],
              'liju_member_id' => $contact_id,
              'verband' => $lv,
            ]);
            CRM_Lijuapi_Utils::add_link_to_user($contact_id, $result['values']['invite_link']);
          } else {
            // only for debugging
            CRM_Lijuapi_Utils::log("User ({$contact_id}) is not available in LiJu API but already has an invite Link");
          }
          continue;
        }
        $email = $user_record['email'];
        $user_index = $user_record['user_index'];
        $liju_id = $this->liju_users[$user_index]['ljs_memberid'];
        if ($liju_id != $contact_id){
          CRM_Lijuapi_Utils::log("We have an ID Mismatch. CiviCRM ID $contact_id} != liju_member_id {$liju_id}. Trying to update User Record.");
          // TODO: This doesn't work. We cannot at this point change the liju_member_id in the database
          $this->update_user_record($liju_id, $contact_id, $email, $lv);
          // delete invite link from user here, since we have a match!
          CRM_Lijuapi_Utils::remove_invite_link_from_user($contact_id);
          // we are done here!
          continue;
        }
        // check if that user can be looked up by member ID
        $liju_user_key = $this->get_liju_user_record($contact_id, 'ljs_memberid');
        if (!empty($liju_user_key)) {
          // !! NOTE: CiviCRM ist datenführend hier! Update the record no matter what
          // TODO: We only need to update if LV changed, which should have been done via hook.
          //       technically we don't need to do anything but remove the invite link!
//            $this->update_user_record($contact_id, $contact_id,  $email, $lv);
          CRM_Lijuapi_Utils::log("Found a matching User {$contact_id}, removing invite Link");
          CRM_Lijuapi_Utils::remove_invite_link_from_user($contact_id);
        }
        } catch (Exception $e) {
          Civi::log()->log("ERROR", " Error syncing contact {$contact_id}. {$e->getMessage()}");
          // TODO Email notification here? More/different Error handling needed?
        }
//        $debug_counter +=1;
//        if ($debug_counter > 20) {
//          CRM_Lijuapi_Utils::log("Finished debugging run");
//          return;
//        }
      }
    }
  }


  /**
   * @param $user_emails
   * @return mixed
   * @throws CRM_Lijuapi_Exceptions_NoEmailForMemberException
   */
  private function get_primary_email($user_emails) {
    if(empty($user_emails)) {
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
   * Search cached liju members for a match to one of the provided user emails
   *
   * @param $email
   * @param string $search_attribute
   * @return array
   */
  private function get_liju_user_by_civi_mail($email) {
    $lookup = $this->get_liju_user_record($email['email'], 'email');
    if (!empty($lookup)) {
      return [
        'email' => $email['email'],
        'user_index' => $lookup,
      ];
    }
    return [];
  }

  /**
   * @param $needle
   * @param $search_attribute
   * @return false|int|string
   */
  private function get_liju_user_record($needle, $search_attribute) {
    return array_search($needle, array_column($this->liju_users, $search_attribute));
  }

  /**
   * @param $contact_id
   * @param $email
   * @param $lv
   * @return void
   * @throws CiviCRM_API3_Exception
   */
  private function update_user_record($old_user_id, $contact_id, $email, $lv) {
    $result = civicrm_api3('Liju', 'updateuser', [
      'old_user_id' => $old_user_id,
      'liju_member_id' => $contact_id,
      'email' => $email,
      'verband' => $lv,
    ]);
    if ($result['is_error'] != 0) {
      Civi::log()->log("DEBUG", "Error occured while Updating User Record in LiJu Member database. " . $result['error_message']);
      throw new Exception("Error occured while Updating User Record in LiJu Member database. " . $result['error_message']);
    }
  }

  /**
   * @param $liju_member_id
   * @return void
   */
  private function get_user_recrod_by_id($liju_member_id) {
    // TODO this still needed?
  }
}
