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

  // local test mapping
  public static $landesverband_mapping = [
    'BW' => 5, // Badenwürtenberg Lokal (test)
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
   * @param $values
   * @return void
   * @throws CRM_Lijuapi_Exceptions_MissingErrorValueException
   */
  public static function set_error_case($values) {
    CRM_Lijuapi_Utils::validate_error_case($values);
    $values['is_consumed'] = FALSE;
//    $values['timestamp'] = date("YmdHis");  // don't need to set this I think, should be done automatically
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
    $fields = ['contact_id', 'email', 'email_id', 'landesverband', 'group_id'];
    foreach ($fields as $item)  {
      if (empty($values[$item])) {
        throw new CRM_Lijuapi_Exceptions_MissingErrorValueException("Missing Value for {$item}");
      }
    }
  }

  public static function add_link_to_user($contact_id, $invite_link){
    // TODO IMPLEMENT ME
  }

  public static function uniq_lv_in_liju_api() {


  }

}
