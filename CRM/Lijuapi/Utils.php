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


  public static function get_lv($contact_id) {

  }

  /**
   * @param $values
   * @return void
   * @throws CRM_Lijuapi_Exceptions_MissingErrorValueException
   */
  public static function set_error_case($values) {
    CRM_Lijuapi_Utils::validate_error_case($values);
    $values['is_consumed'] = FALSE;
    $values['timestamp'] = date("YmdHis");
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

  public function uniq_lv_in_liju_api() {
    //    grep verband /tmp/lv | cut -d "\"" -f 4 | sort | uniq
    //BB
    //BE
    //BV
    //BW
    //BY
    //HB
    //HE
    //HH
    //MV
    //NI
    //NW
    //RP
    //SDS
    //SH
    //SL
    //SN
    //ST
    //SYSTEM
    //TH
  }
}
