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

  public function run() {
    // Iterate Users from  per group!
    foreach (CRM_Lijuapi_Utils::$landesverband_mapping as $lv => $civi_group_id) {
      $result = civicrm_api3('GroupContact', 'get', [
        'sequential' => 1,
        'group_id' => $civi_group_id,
        'options' => ['limit' => 0],
      ]);
      try {
        foreach ($result['values'] as $civi_group_info) {
          $civi_user_emails = $this->get_user_email($civi_group_info['contact_id']);
          $user_record = $this->get_liju_user_record($civi_user_emails);
          if (empty($user_record)) {
            // create Link and save it to user
            $result = civicrm_api3('Liju', 'createinvite', [
              'email' => $this->get_primary_email($civi_user_emails),
              'liju_member_id' => $civi_group_info['contact_id'],
              'verband' => $lv,
            ]);
            CRM_Lijuapi_Utils::add_link_to_user($result['values']['invite_link'], $civi_group_info['contact_id']);
            continue;
          }
          if ($this->liju_users[$user_record['user_index']] ){
            // TODO check if same member ID, otherwise update
          }
        }
        // if mail in $this->liJu_users --> verify memberId (contact_id)

        // if !mail, but memberId --> update user (email, contact_id, LV)

        // If no User --> Create Link & save locally
      } catch (Exception $e) {
        Civi::log()->log("ERROR", " Error syncing contact {$contact_id}. {$e->getMessage()}");
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

  private function get_user_email($contact_id) {
    $result = civicrm_api3('Email', 'get', [
      'sequential' => 1,
      'contact_id' => $contact_id,
    ]);
    $return_values = [];
    foreach ($result['values'] as $email_record) {
      $return_values[$email_record['id']] = [
        'id' => $email_record['id'],
        'email' => $email_record['email'],
        'is_primary' => $email_record['is_primary'],
      ];
    }
    return $return_values;
  }

  /**
   * Search cached liju members for a match to one of the provided user emails
   *
   * @param $emails
   * @return array
   */
  private function get_liju_user_record($emails) {
    foreach ($emails as $key => $email_data) {
      $email_lookup= array_search($email_data['email'], $this->liju_users);
      if (!empty($email_lookup)) {
        return [
          'email' => $email_data['email'],
          'user_index' => $email_lookup,
        ];
      }
    }
    return [];
  }

  /**
   * @param $contact_id
   * @param $email
   * @param $lv
   * @return void
   * @throws CiviCRM_API3_Exception
   */
  private function update_user_record($contact_id, $email, $lv) {
    $result = civicrm_api3('Liju', 'updateuser', [
      'liju_member_id' => $contact_id,
      'email' => $email,
      'verband' => $lv,
    ]);
    if ($result['is_error'] != 0) {
      Civi::log()->log("DEBUG", "Error occured while Updating User Record in LiJu Member database. " . $result['error_message']);
      throw new Exception("Error occured while Updating User Record in LiJu Member database. " . $result['error_message']);
    }
  }

  private function get_user_recrod_by_id($liju_member_id) {

  }
}
