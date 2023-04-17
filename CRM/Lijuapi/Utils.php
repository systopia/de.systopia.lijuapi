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

class CRM_Lijuapi_Utils
{

// SELECT id, name, description FROM civicrm_group WHERE name LIKE "%Mitglied%";
// Abbrevation after
// https://www.datenportal.bmbf.de/portal/de/G122.html#:~:text=SN%20%3D%20Sachsen%3B,TH%20%3D%20Th%C3%BCringen.
// Mapping 2 letter abbrevation to civicrm_group_id

// DEV + PRO, mapping is the same actually
  public static $landesverband_mapping = [
    'BW' => 18, // Baden_Wuerttemberg_Mitglieder_18
    'BY' => 19, // Bayern_Mitglieder_19
    'BE' => 20, // Berlin_Mitglieder_20
    'BB' => 21, // Brandenburg_Mitglieder_21
    'HB' => 22, // Bremen_Mitglieder_22
    'HH' => 23, // Hamburg_Mitglieder_23
    'HE' => 24, // Hessen_Mitglieder_24
    'MV' => 25, // Mecklenburg_Vorpommern_Mitglied_25
    'NI' => 26, // Niedersachsen_Mitglieder_26
    'NW' => 27, // Nordrhein_Westfalen_Mitglieder_27
    'RP' => 28, // Rheinland_Pfalz_Mitglieder_28
    'SL' => 29, // Saarland_Mitglieder_29
    'SN' => 30, // Sachsen_Mitglieder_30
    'ST' => 31, // Sachsen_Anhalt_Mitglieder_31
    'SH' => 32, // Schleswig_Holstein_Mitglieder_32
    'TH' => 33, // Thueringen_Mitglieder_33
  ];

  public static $debug = True;

  private static $singleton = NULL;

  /**
   * Get utils singleton
   * @return CRM_Lijuapi_Utils|null
   */
  public static function singleton()
  {
    if (self::$singleton === NULL) {
      self::$singleton = new CRM_Lijuapi_Utils();
    }
    return self::$singleton;
  }

  public static function get_lv_mapping()
  {
    return self::$landesverband_mapping;
  }

  /**
   * @param $contact_id
   * @return mixed
   * @throws CRM_Lijuapi_Exceptions_NoLvMemberShipFoundException
   * @throws CiviCRM_API3_Exception
   */
  public static function get_lv($contact_id)
  {
    $result = civicrm_api3('GroupContact', 'get', [
      'sequential' => 1,
      'contact_id' => $contact_id,
    ]);
    foreach ($result['values'] as $group_data) {
      // there should only be one LV at a time, so we return the first match for a group ID
      // specified in the LV mapping
      if (self::is_lv_group($group_data['group_id'])) {
        return array_search($group_data['group_id'], self::$landesverband_mapping);
      }
    }
    throw new CRM_Lijuapi_Exceptions_NoLvMemberShipFoundException("No Landesverband membership found");
  }


  /**
   * @param $civi_group_id
   * @return false|int|string
   * @throws CRM_Lijuapi_Exceptions_GroupIdNotLandesverbandException
   */
  public static function get_lv_from_group_id($civi_group_id)
  {
    if (self::is_lv_group($civi_group_id)) {
      return array_search($civi_group_id, self::$landesverband_mapping);
    }
    throw new CRM_Lijuapi_Exceptions_GroupIdNotLandesverbandException("{$civi_group_id} is not a Landesverband ID");
  }


  /**
   * @param $group_id
   * @return bool
   */
  public static function is_lv_group($group_id)
  {
    if (in_array($group_id, self::$landesverband_mapping)) {
      return TRUE;
    }
    return FALSE;
  }


  /**
   * @param $landesverband
   * @return int
   */
  public static function get_lv_id($landesverband)
  {
    return self::$landesverband_mapping[$landesverband];
  }


  /**
   * @param $values
   * needed values: ['contact_id', 'email', 'email_id', 'landesverband', 'group_id', 'errorcode']
   * @return void
   * @throws CRM_Lijuapi_Exceptions_MissingErrorValueException
   */
  public static function set_error_case($values)
  {
    CRM_Lijuapi_Utils::validate_error_case($values);
    $values['is_consumed'] = FALSE;
    // Save it to database
    $item = new CRM_Lijuapi_BAO_LijuErrorHandler();
    $item->copyValues($values);
    $item->save();
  }

  /**
   * @param $values
   * @return void
   * @throws CRM_Lijuapi_Exceptions_MissingErrorValueException
   */
  public static function validate_error_case($values)
  {
    $fields = ['contact_id', 'email', 'email_id', 'landesverband', 'group_id', 'errorcode'];
    foreach ($fields as $item) {
      if (empty($values[$item])) {
        throw new CRM_Lijuapi_Exceptions_MissingErrorValueException("Missing Value for {$item}");
      }
    }
  }

  /**
   * @param $contact_id
   * @param $invite_link
   * @return void
   * @throws CRM_Lijuapi_Exceptions_SaveInviteLinkToContactException
   * @throws CiviCRM_API3_Exception
   * @throws CRM_Lijuapi_Exceptions_NoInviteLinkCustomFieldException
   */
  public static function add_link_to_user($contact_id, $invite_link)
  {
    CRM_Lijuapi_Utils::log("Adding invite Link to user {$contact_id}");
    $custom_field = self::get_custom_invite_field();
    $result = civicrm_api3('Contact', 'create', [
      'id' => $contact_id,
      $custom_field => $invite_link,
    ]);
    if ($result['is_error'] != 0) {
      throw new CRM_Lijuapi_Exceptions_SaveInviteLinkToContactException("Couldn't save Invite Link {$invite_link} to Contact {$contact_id}");
    }
  }


  /**
   * @param $contact_id
   * @return void
   * @throws CRM_Lijuapi_Exceptions_NoInviteLinkCustomFieldException
   * @throws CRM_Lijuapi_Exceptions_RemoveInviteLinkException
   * @throws CiviCRM_API3_Exception
   */
  public static function remove_invite_link_from_user($contact_id)
  {
    $custom_field = self::get_custom_invite_field();
    $result = civicrm_api3('Contact', 'create', [
      'id' => $contact_id,
      $custom_field => "",
    ]);
    if ($result['is_error'] != 0) {
      throw new CRM_Lijuapi_Exceptions_RemoveInviteLinkException("Failed to remove invite Link from User {$contact_id}. Error Message: {$result['error_messsage']}");
    }
  }


  /**
   * @return string
   * @throws CRM_Lijuapi_Exceptions_NoInviteLinkCustomFieldException
   */
  public static function get_custom_invite_field()
  {
    $config = CRM_Lijuapi_Config::singleton();
    $custom_field_id = $config->getSetting('invitelink_custom_field');
    if (empty($custom_field_id)) {
      throw new CRM_Lijuapi_Exceptions_NoInviteLinkCustomFieldException("No invite Custom Field configured. Please configure that in the Extension Settings");
    }
    return "custom_" . $custom_field_id;
  }


  public static function has_invite_link($contact_id)
  {
    $custom_field = self::get_custom_invite_field();
    $result = civicrm_api3('Contact', 'get', [
      'sequential' => 1,
      'return' => [$custom_field],
      'id' => $contact_id,
    ]);
    foreach ($result['values'] as $value) {
      if (empty($value[$custom_field])) {
        return False;
      }
    }
    return True;
  }


  /**
   * @return array
   * @throws CiviCRM_API3_Exception
   */
  public static function get_contact_custom_fields()
  {
    // get all custom fields
    $result = civicrm_api3('CustomField', 'get', [
      'sequential' => 1,
      'return' => ["id", "label"],
      'options' => ['limit' => 0],
    ]);
    $custom_fields = [];
    foreach ($result['values'] as $fields) {
      $custom_fields[$fields['id']] = $fields['label'];
    }
    return $custom_fields;
  }

  /**
   * @param $contact_id
   * @param $contact_email
   * @param $landesverband
   * @param $error_message
   * @return void
   * @throws CiviCRM_API3_Exception
   *
   * // TODO: When are we sending? Directly when error occurs, or via Cron from databse error_table
   */
  public static function notify_error($error_message, $contact_email, $landesverband, $contact_id = NULL)
  {
    $config = CRM_Lijuapi_Config::singleton();
    if (!$config->getSetting('notification_email_active')) {
      return;
    }
    if (empty($contact_id)) {
      $contact_id = CRM_Lijuapi_Utils::get_user_id($contact_email);
    }
    $mailer = new CRM_Lijuapi_Mailer();
    $mailer->send_error_mail($contact_email, $landesverband, $error_message, $contact_id);
  }


  /**
   * @param $email
   * @return mixed|void|null
   * @throws CiviCRM_API3_Exception
   */
  public static function get_user_id($email)
  {
    $result = civicrm_api3('Email', 'get', [
      'sequential' => 1,
      'email' => $email,
      'is_primary' => 1,
    ]);
    if ($result['count'] != 1) {
      return NULL;
    }
    foreach ($result['values'] as $value) {
      return $value['contact_id'];
    }
  }


  /**
   * @param $contact_id
   * @return mixed
   * @throws CRM_Lijuapi_Exceptions_NoEmailForMemberException
   * @throws CiviCRM_API3_Exception
   */
  public static function get_primary_email($contact_id)
  {
    $result = civicrm_api3('Email', 'get', [
      'sequential' => 1,
      'is_primary' => 1,
      'contact_id' => $contact_id,
    ]);
    if ($result['count'] != 1) {
      throw new CRM_Lijuapi_Exceptions_NoEmailForMemberException("Member ({$contact_id}) doesn't have an email address");
    }
    return $result['values']['0']['email'];
  }


  /**
   * @param $op
   * @param $objectName
   * @param $objectId
   * @param $objectRef
   * @return void
   * @throws CRM_Lijuapi_Exceptions_MissingErrorValueException
   */
  public static function email_hook($op, $objectName, $objectId, &$objectRef)
  {
    $contact_id = $objectRef->contact_id;
    $email = $objectRef->email;
    $email_id = $objectRef->id;

    try {
      CRM_Lijuapi_Utils::log("Email hook editing {$contact_id}, setting email to {$email} ({$email_id})");
      // check if this contact is a member and if this contact doesn't have an invite link!
      // if invite link is available, then we cannot update the user (yet), since it isn't created yet.
      if (self::has_invite_link($contact_id)) {
        // nothing to do here
        CRM_Lijuapi_Utils::log("User {$contact_id} still has an invite link, Email update wont be executed since no user is in Liju Membership Database yet.");
        return;
      }
      $result = civicrm_api3('Liju', 'updateuser', [
          'old_user_id' => $contact_id,
          'liju_member_id' => null,
          'email' => $email,
          'verband' => null,
          'is_sds_member' => null,
      ]);
      if ($result['is_error'] != 0)
        Civi::log()->log("DEBUG", "Error occured while Updating User Record in LiJu Member database. " . $result['error_message']);

    } catch (CRM_Lijuapi_Exceptions_NoLvMemberShipFoundException $e) {
      // contact isn't a member, nothing to do here.
      CRM_Lijuapi_Utils::log("User {$contact_id} isn't a member. No update Executed.");
      return;
    } catch (CRM_Lijuapi_Exceptions_UpdateUserException $e) {
      // Log error, then put information in civicrm_lijuapi_errorhandler
      Civi::log()->log("ERROR", "[UpdateUserException] Failed to communicate with LiJuApi. Error Message: " . $e->getMessage());
      $values = [
        'contact_id' => $contact_id,
        'email' => $email,
        'email_id' => $email_id,
        'landesverband' => $landesverband,
        'group_id' => CRM_Lijuapi_Utils::get_lv_id($landesverband),
        'errorcode' => $e->getMessage()
      ];
      CRM_Lijuapi_Utils::set_error_case($values);
      // TODO: Send fail Email?
    } catch (Exception $e) {
      // Log error! Something weird happened here!
      Civi::log()->log("ERROR", "Unknown Exception in Update Email.{$op} while communicating to LijuAPI. Error Message: " . $e->getMessage());
    }
  }


  /**
   * @param $op
   * @param $objectName
   * @param $objectId
   * @param $objectRef
   * @return void
   * @throws CiviCRM_API3_Exception
   *
   * In Form Processor create invite link should be *before* adding the user to a group.
   * Otherwise this will fail 100%, since no user is available *yet*
   */
  public static function change_lv_hook($op, $objectName, $objectId, &$objectRef)
  {
    try {
      if (!self::is_lv_group($objectId)) {
        // nothing to do here
        return;
      }
      $contact_id = $objectRef['0'];    // seems weird to access it like this
      CRM_Lijuapi_Utils::log("Changing LV for user {$contact_id}");
      if (self::has_invite_link($contact_id)) {
        // nothing to do here. If invite link is still active then we don't need to update LV
        // Sync job needs to be activated for this to be acurate!
        // otherwise we need to accept a fail from change_lv
        CRM_Lijuapi_Utils::log("User {$contact_id} still has an invite Link, user will not be updated in Liju Database");
        return;
      }
      $landesverband = self::get_lv_from_group_id($objectId);

      $result = civicrm_api3('Liju', 'updateuser', [
          'old_user_id' => $contact_id,
          'liju_member_id' => null,
          'email' => null,
          'verband' => $landesverband,
          'is_sds_member' => null,
      ]);
      if ($result['is_error'] != 0)
        Civi::log()->log("DEBUG", "Error occured while Updating User Record in LiJu Member database. " . $result['error_message']);

    } catch (CRM_Lijuapi_Exceptions_NoLvMemberShipFoundException $e) {
      // nothing to do here.
      return;
    } catch (CRM_Lijuapi_Exceptions_NoEmailForMemberException $e) {
      Civi::log()->log("ERROR", "Failed to update Membership Group. " . $e->getMessage());
    } catch (CRM_Lijuapi_Exceptions_UpdateLvException $e) {
      Civi::log()->log("ERROR", "LiJU API error for updating Landesverband. " . $e->getMessage());
    }
  }

  public static function get_group_name($civi_group_id)
  {
    $result = civicrm_api3('Group', 'get', [
      'id' => $civi_group_id,
    ]);
    if ($result['count'] != 1) {
      throw new API_Exception("Group Not found, invalid group ID {$civi_group_id}");
    }
    foreach ($result['values'] as $value) {
      return $value['name'];
    }
  }

  /**
   * @param $message
   * @param $loglevel
   * @return void
   *
   */
  public static function log($message, $loglevel = "DEBUG")
  {
    if (self::$debug) {
      Civi::log()->log($loglevel, "[de.systopia.lijuapi] " . $message);
    }
  }

  /**
   * @param $contact_id
   * @return array
   * @throws CiviCRM_API3_Exception|CRM_Lijuapi_Exceptions_CiviCRMUserEmailNotAvailableException
   */
  public static function get_user_primary_email($contact_id, $email_only = FALSE)
  {
    $result = civicrm_api3('Email', 'get', [
      'sequential' => 1,
      'contact_id' => $contact_id,
      'is_primary' => 1
    ]);
    if ($result['count'] != 1) {
      throw new CRM_Lijuapi_Exceptions_CiviCRMUserEmailNotAvailableException("Found {$result['count']} Email Results for User {$contact_id}");
    }
    $return_values = [];
    foreach ($result['values'] as $email_result) {
      if ($email_only) {
        return $email_result['email'];
      }
      $return_values = [
        'email' => $email_result['email'],
        'id' => $email_result['id'],
      ];
    }
    return $return_values;
  }

  /**
   * @return array
   */
  public static function get_sds_group()
  {
    $result = civicrm_api3('GroupContact', 'get', [
          'group_id'   => self::$sds_group_id,
          'status'     => 'Added',
          'sequential' => 1,
          'return'     => ['contact_id', 'status'],
          'options'    => ['limit' => 0],
        ]);
    return array_column($result['values'], 'contact_id');
  }
}
