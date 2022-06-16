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
          $contact_id = $civi_group_info['contact_id'];
          $civi_user_email = $this->get_user_email($contact_id);
          $user_record = $this->get_liju_user_by_civi_mail($civi_user_email);
          if (empty($user_record)) {
            // create Link and save it to user
            // TODO commented out for debugging
            $result = civicrm_api3('Liju', 'createinvite', [
              'email' => $civi_user_email['email'],
              'liju_member_id' => $contact_id,
              'verband' => $lv,
            ]);
            // TODO implement add_link_to_user!
            CRM_Lijuapi_Utils::add_link_to_user($contact_id, $result['values']['invite_link']);
            continue;
          }
          $email = $user_record['email'];
          $user_index = $user_record['user_index'];
          $liju_id = $this->liju_users[$user_index]['ljs_memberid'];
          if ($liju_id != $contact_id){
            $this->update_user_record($liju_id, $contact_id, $email, $lv);
            // delete invite link from user here, since we have a match!
            CRM_Lijuapi_Utils::remove_invite_link_from_user($contact_id);
            // we are done here!
            continue;
          }
          // check if that user can be looked up by member ID
          $liju_user_key = $this->get_liju_user_record($contact_id, 'ljs_memberid');
          if (!empty($liju_user_key)) {
            // TODO Verify this!
            // !! NOTE: CiviCRM ist datenführend hier! Update the record no matter what
            $this->update_user_record($contact_id, $contact_id,  $email, $lv);
            CRM_Lijuapi_Utils::remove_invite_link_from_user($contact_id);
          }

        }
      } catch (Exception $e) {
        Civi::log()->log("ERROR", " Error syncing contact {$contact_id}. {$e->getMessage()}");
        // TODO Email notification here? More/different Error handling needed?
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
   * @param $contact_id
   * @return array
   * @throws CiviCRM_API3_Exception
   */
  private function get_user_email($contact_id) {
    $result = civicrm_api3('Email', 'get', [
      'sequential' => 1,
      'contact_id' => $contact_id,
      'is_primary' => 1
    ]);
    if ($result['count'] != 1 ){
      return [];
    }
    $return_values = [];
    foreach ($result['values'] as $email_result) {
      $return_values = [
        'email' => $email_result['email'],
        'id' => $email_result['id'],
      ];
    }
    return $return_values;
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
