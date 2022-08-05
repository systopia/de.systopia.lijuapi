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

class CRM_Lijuapi_Utils {

  // SELECT id, name, description FROM civicrm_group WHERE name LIKE "%Mitglied%";
//| 160 | Baden_Wuerttemberg_Mitglieder_160   | Baden-Württemberg Mitglieder                         |
//| 161 | Bayern_Mitglieder_161               | Bayern Mitglieder                                    |
//| 162 | Berlin_Mitglieder_162               | Berlin Mitglieder                                    |
//| 163 | Brandenburg_Mitglieder_163          | Brandenburg Mitglieder                               |
//| 164 | Bremen_Mitglieder_164               | Bremen Mitglieder                                    |
//| 165 | Hamburg_Mitglieder_165              | Hamburg Mitglieder                                   |
//| 166 | Hessen_Mitglieder_166               | Hessen Mitglieder                                    |
//| 167 | Mecklenburg_Vorpommern_Mitglied_167 | Mecklenburg-Vorpommern Mitglieder                    |
//| 168 | Niedersachsen_Mitglieder_168        | Niedersachsen Mitglieder                             |
//| 169 | Nordrhein_Westfalen_Mitglieder_169  | Nordrhein-Westfalen Mitglieder                       |
//| 170 | Rheinland_Pfalz_Mitglieder_170      | Rheinland-Pfalz Mitglieder                           |
//| 171 | Saarland_Mitglieder_171             | Saarland Mitglieder                                  |
//| 172 | Sachsen_Mitglieder_172              | Sachsen Mitglieder                                   |
//| 173 | Sachsen_Anhalt_Mitglieder_173       | Sachsen-Anhalt Mitglieder                            |
//| 174 | Schleswig_Holstein_Mitglieder_174   | Schleswig-Holstein Mitglieder                        |
//| 175 | Thueringen_Mitglieder_175           | Thüringen Mitglieder                                 |
// Abbrevation after
// https://www.datenportal.bmbf.de/portal/de/G122.html#:~:text=SN%20%3D%20Sachsen%3B,TH%20%3D%20Th%C3%BCringen.
// Mapping 2 letter abbrevation to civicrm_group_id
//  public static $landesverband_mapping = [
//    'BW' => 160, // Baden_Wuerttemberg_Mitglieder_160
//    'BY' => 161, // Bayern_Mitglieder_161
//    'BE' => 162, // Berlin_Mitglieder_162
//    'BB' => 163, // Brandenburg_Mitglieder_163
//    'HB' => 164, // Bremen_Mitglieder_164
//    'HH' => 165, // Hamburg_Mitglieder_165
//    'HE' => 166, // Hessen_Mitglieder_166
//    'MV' => 167, // Mecklenburg_Vorpommern_Mitglied_167
//    'NI' => 168, // Niedersachsen_Mitglieder_168
//    'NW' => 169, // Nordrhein_Westfalen_Mitglieder_169
//    'RP' => 170, // Rheinland_Pfalz_Mitglieder_170
//    'SL' => 171, // Saarland_Mitglieder_171
//    'SN' => 172, // Sachsen_Mitglieder_172
//    'ST' => 173, // Sachsen_Anhalt_Mitglieder_173
//    'SH' => 174, // Schleswig_Holstein_Mitglieder_174
//    'TH' => 175, // Thueringen_Mitglieder_175
//  ];

// DEV
//  public static $landesverband_mapping = [
//    'BW' => 18, // Baden_Wuerttemberg_Mitglieder_18
//    'BY' => 19, // Bayern_Mitglieder_19
//    'BE' => 20, // Berlin_Mitglieder_20
//    'BB' => 21, // Brandenburg_Mitglieder_21
//    'HB' => 22, // Bremen_Mitglieder_22
//    'HH' => 23, // Hamburg_Mitglieder_23
//    'HE' => 24, // Hessen_Mitglieder_24
//    'MV' => 25, // Mecklenburg_Vorpommern_Mitglied_25
//    'NI' => 26, // Niedersachsen_Mitglieder_26
//    'NW' => 27, // Nordrhein_Westfalen_Mitglieder_27
//    'RP' => 28, // Rheinland_Pfalz_Mitglieder_28
//    'SL' => 29, // Saarland_Mitglieder_29
//    'SN' => 30, // Sachsen_Mitglieder_30
//    'ST' => 31, // Sachsen_Anhalt_Mitglieder_31
//    'SH' => 32, // Schleswig_Holstein_Mitglieder_32
//    'TH' => 33, // Thueringen_Mitglieder_33
//  ];


  // local test mapping
  public static $landesverband_mapping = [
    'BW' => 5, // Badenwürtenberg Lokal (test)
    'NW' => 6, // Nordrhein Westfalen Lokal (test)
  ];

  private static $singleton = NULL;

  /**
   * Get utils singleton
   * @return CRM_Lijuapi_Utils|null
   */
  public static function singleton() {
    if (self::$singleton === NULL) {
      self::$singleton = new CRM_Lijuapi_Utils();
    }
    return self::$singleton;
  }


  /**
   * @param $contact_id
   * @return mixed
   * @throws CRM_Lijuapi_Exceptions_NoLvMemberShipFoundException
   * @throws CiviCRM_API3_Exception
   */
  public static function get_lv($contact_id) {
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
  public static function get_lv_from_group_id($civi_group_id) {
    if (self::is_lv_group($civi_group_id)) {
      return array_search($civi_group_id, self::$landesverband_mapping);
    }
    throw new CRM_Lijuapi_Exceptions_GroupIdNotLandesverbandException("{$civi_group_id} is not a Landesverband ID");
  }


  /**
   * @param $group_id
   * @return bool
   */
  public static function is_lv_group($group_id) {
    if(in_array($group_id, self::$landesverband_mapping)){
      return TRUE;
    }
    return FALSE;
  }


  /**
   * @param $landesverband
   * @return int
   */
    public static function get_lv_id($landesverband) {
    return self::$landesverband_mapping[$landesverband];
  }


  /**
   * @param $values
   * needed values: ['contact_id', 'email', 'email_id', 'landesverband', 'group_id', 'errorcode']
   * @return void
   * @throws CRM_Lijuapi_Exceptions_MissingErrorValueException
   */
  public static function set_error_case($values) {
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
  public static function validate_error_case($values) {
    $fields = ['contact_id', 'email', 'email_id', 'landesverband', 'group_id', 'errorcode'];
    foreach ($fields as $item)  {
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
  public static function add_link_to_user($contact_id, $invite_link){
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
  public static function remove_invite_link_from_user($contact_id) {
    $custom_field = self::get_custom_invite_field();
    $result = civicrm_api3('Contact', 'create', [
      'id' => 202,
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
  public static function get_custom_invite_field() {
    $config = CRM_Lijuapi_Config::singleton();
    $custom_field_id = $config->getSetting('invitelink_custom_field');
    if (empty($custom_field_id)) {
      throw new CRM_Lijuapi_Exceptions_NoInviteLinkCustomFieldException("No invite Custom Field configured. Please configure that in the Extension Settings");
    }
    return "custom_" . $custom_field_id;
  }



  public static function has_invite_link($contact_id) {
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
  public static function get_contact_custom_fields() {
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
  public static function notify_error($contact_id, $contact_email, $landesverband, $error_message) {
    $config = CRM_Lijuapi_Config::singleton();
    if(!$config->getSetting('notification_email_active')) {
      return;
    }
    $mailer = new CRM_Lijuapi_Mailer();
    $mailer->send_error_mail($contact_id, $contact_email, $landesverband, $error_message);
  }


  /**
   * @param $contact_id
   * @return mixed
   * @throws CRM_Lijuapi_Exceptions_NoEmailForMemberException
   * @throws CiviCRM_API3_Exception
   */
  public static function get_primary_email($contact_id) {
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
  public static function email_hook($op, $objectName, $objectId, &$objectRef) {
    $contact_id = $objectRef->contact_id;
    $email = $objectRef->email;
    $email_id = $objectRef->id;

    try{
      $landesverband = CRM_Lijuapi_Utils::get_lv($contact_id);
      // check if this contact is a member and if this contact doesn't have an invite link!
      // if invite link is available, then we cannot update the user (yet), since it isn't created yet.
      if (self::has_invite_link($contact_id)) {
        // nothing to do here
        return;
      }
      $result = civicrm_api3('Liju', 'changelv', [
        'email' => $email,
        'liju_member_id' => $contact_id,
        'new_lv' => $landesverband,
      ]);
    } catch( CRM_Lijuapi_Exceptions_NoLvMemberShipFoundException $e) {
      // contact isn't a member, nothing to do here.
      return;
    } catch (CRM_Lijuapi_Exceptions_UpdateUserException $e) {
      // Log error, then put information in civicrm_lijuapi_errorhandler
      Civi::log()->log("ERROR", "[UpdateUserException] Failed to communicate with LiJuApi. Error Message: " . $e->getMessage());
      $values = [
        'contact_id'    => $contact_id,
        'email'         => $email,
        'email_id'      => $email_id,
        'landesverband' => $landesverband,
        'group_id'      => CRM_Lijuapi_Utils::get_lv_id($landesverband),
        'errorcode'     => $e->getMessage()
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
  public static function change_lv_hook($op, $objectName, $objectId, &$objectRef) {
    try {
      if (!self::is_lv_group($objectId)) {
        // nothing to do here
        return;
      }

      $contact_id = $objectRef['0'];    // seems weird to access it like this
      if (self::has_invite_link($contact_id)) {
        // nothing to do here. If invite link is still active then we don't need to update LV
        // Sync job needs to be activated for this to be acurate!
        // otherwise we need to accept a fail from change_lv
        return;
      }
      $landesverband = self::get_lv_from_group_id($objectId);
      $email = self::get_primary_email($contact_id);

      $result = civicrm_api3('Liju', 'changelv', [
        'email' => $email,
        'liju_member_id' => $contact_id,
        'new_lv' => $landesverband,
      ]);
    } catch( CRM_Lijuapi_Exceptions_NoLvMemberShipFoundException $e) {
      // nothing to do here.
      return;
    } catch (CRM_Lijuapi_Exceptions_NoEmailForMemberException $e) {
      Civi::log()->log("ERROR", "Failed to update Membership Group. " . $e->getMessage());
    } catch (CRM_Lijuapi_Exceptions_UpdateLvException $e) {
      Civi::log()->log("ERROR", "LiJU API error for updating Landesverband. " . $e->getMessage());
    }
  }

}
