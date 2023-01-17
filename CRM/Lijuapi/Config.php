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

  /**
   * Mapping groups_id => Group Name for country memberships
   * DERECATED - this isn't used. CRM_Lijuapi_Utils::landesverband_mapping is instead utilized
   * @var string[]
   */
  public static $LV_group_mapping = [
    "160	" => "Baden-Wuerttemberg Mitglieder",
    "161" => "Bayern Mitglieder",
    "162" => "Berlin Mitglieder",
    "163" => "Brandenburg Mitglieder",
    "164" => "Bremen Mitglieder",
    "165" => "Hamburg Mitglieder",
    "166" => "Hessen Mitglieder",
    "167" => "Mecklenburg-Vorpommern Mitglieder",
    "168" => "Niedersachsen Mitglieder",
    "169" => "Nordrhein-Westfalen Mitglieder",
    "170" => "Rheinland-Pfalz Mitglieder",
    "171" => "Saarland Mitglieder",
    "172" => "Sachsen Mitglieder",
    "173" => "Sachsen-Anhalt Mitglieder",
    "174" => "Schleswig-Holstein Mitglieder",
    "175" => "Thueringen Mitglieder",
  ];


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
