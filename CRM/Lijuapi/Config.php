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
 * Configurations
 */
class CRM_Lijuapi_Config {

  private static $singleton = NULL;
  private static $settings  = NULL;


  protected $jobs = NULL;
  /**
   * get the config instance
   */
  public static function singleton() {
    if (self::$singleton === NULL) {
      self::$singleton = new CRM_Lijuapi_Config();
    }
    return self::$singleton;
  }

  /**
   * Get a single setting
   *
   * @param $name          string setting name
   * @param $default_value mixed  default value
   * @return mixed setting
   */
  public function getSetting($name, $default_value = NULL) {
    $settings = self::getSettings();
    return CRM_Utils_Array::value($name, $settings, $default_value);
  }

  /**
   * get Mailingtools settings
   *
   * @return array
   */
  public function getSettings() {
    if (self::$settings === NULL) {
      self::$settings = CRM_Core_BAO_Setting::getItem('de.systopia.Lijuapi', 'Liju_Api');
    }

    return self::$settings;
  }

  /**
   * set Mailingtools settings
   *
   * @param $settings array
   */
  public function setSettings($settings) {
    self::$settings = $settings;
    CRM_Core_BAO_Setting::setItem($settings, 'de.systopia.Lijuapi', 'Liju_Api');
  }
}
