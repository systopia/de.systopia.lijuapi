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

use GuzzleHttp\Client;

class CRM_Lijuapi_ApiInterface {

  private $guzzle_client = NULL;
  private $base_uri = NULL;

  /**
   * @throws Exception
   */
  public function __construct($base_uri = NULL) {
    if (empty($base_uri)) {
      $config = CRM_Lijuapi_Config::singleton();
      $this->base_uri = $config->getSetting('api_base_url');
      if (empty($this->base_uri)) {
        throw new Exception("Invalid Base-URL. Please configure an URL in the settings");
      }
    }
    $this->guzzle_client = new Client([
      // Base URI is used with relative requests
      'base_uri' => $this->base_uri,
    ]);
    echo "hello";
  }


  public function get_invite_link($email) {

  }


  public function get_users() {
    $response = $this->guzzle_client->get($this->base_uri . '/api/v1/civicrm/getusers');
    print_r($response);
    return $response;
  }

  public function update_liju_user($liju_member_id) {

  }

  private function execute_api_all () {

  }

}
