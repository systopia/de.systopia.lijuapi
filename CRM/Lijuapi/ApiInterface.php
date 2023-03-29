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

class CRM_Lijuapi_ApiInterface
{

  private $guzzle_client = NULL;
  private $base_uri = NULL;
  private $header;

  /**
   * @throws CRM_Lijuapi_Exceptions_InvalidBaseUrlException
   */
  public function __construct($base_uri = NULL, $username = NULL, $token = NULL)
  {
    if (empty($base_uri) && empty($username) && empty($token)) {
      $config = CRM_Lijuapi_Config::singleton();
      $this->base_uri = $config->getSetting('api_base_url');
      if (empty($this->base_uri)) {
        throw new CRM_Lijuapi_Exceptions_InvalidBaseUrlException("Invalid Base-URL. Please configure an URL in the settings");
      }
      $auth_token = $config->getSetting('username') . ":" . $config->getSetting('authorization_token');
    } else {
      $auth_token = $token;
      $this->base_uri = $base_uri;
    }
    // encode username/password as base_64
    $auth_token = base64_encode($auth_token);
    $this->header = ['headers' =>
      [
        'Authorization' => "Basic {$auth_token}"
      ],
    ];
    $this->guzzle_client = new Client([
      // Base URI is used with relative requests
      'base_uri' => $this->base_uri,
    ]);
  }


  /**
   * @param $liju_member_id
   * @param $new_lv
   * @return void
   * @throws CRM_Lijuapi_Exceptions_UpdateLvException
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function change_lv($liju_member_id, $new_lv)
  {
    $this->header['form_params']['verband'] = $new_lv;
    $response = $this->guzzle_client->request(
      'POST',
      "api/v1/civicrm/updateuser/{$liju_member_id}",
      $this->header
    );
    $content = json_decode($response->getBody()->getContents(), TRUE);
    if ($content['success'] != TRUE) {
      throw new CRM_Lijuapi_Exceptions_UpdateLvException("Changing Landesverband for member ID {$liju_member_id} wasn't successful! Error Message: " . $content['error']);
    }
  }

  /**
   * @return array
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function get_users()
  {
    $response = $this->guzzle_client->request(
      'GET',
      '/api/v1/civicrm/getusers',
      $this->header
    );
    // TODO: check if array is passed out!
    $content = json_decode($response->getBody()->getContents(), TRUE);
    return $content;
  }

  /**
   * @param $email
   * @param $liju_member_id
   * @param $verband
   * @return mixed
   * @throws CRM_Lijuapi_Exceptions_CreateInviteLinkException
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function get_invite_link($email, $liju_member_id, $verband, $is_sds_member)
  {
    $this->header['form_params']['verband'] = $verband;
    $this->header['form_params']['mail'] = $email;
    $this->header['form_params']['ljs_memberid'] = $liju_member_id;
    $this->header['form_params']['is_sds_member'] = $is_sds_member;
    $response = $this->guzzle_client->request(
      'POST',
      "api/v1/civicrm/invite/new",
      $this->header
    );
    $content = json_decode($response->getBody()->getContents(), TRUE);
    if ($content['success'] != TRUE) {
      throw new CRM_Lijuapi_Exceptions_CreateInviteLinkException("Failed to create Invite Link for member {$liju_member_id} ({$email})! Error Message: " . $content['error']);
    }
    // TODO: parse invite link from content! Needs testing
    return $content['invite_link'];
  }


  /**
   * @param $liju_member_id
   * @param $email
   * @param $verband
   * @param $is_sds_member
   * @return void
   * @throws CRM_Lijuapi_Exceptions_UpdateUserException
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function update_liju_user($old_user_id, $liju_member_id, $email, $verband, $is_sds_member)
  {
    if (empty($email) && empty($verband) && empty($is_sds_member)) {
      Civi::log()->log("DEBUG", "[CRM_Lijuapi_ApiInterface->update_liju_user] No User Data specified to update. Nothing to do here.");
      return;
    }
    if (! empty($email))
        $this->header['form_params']['verband'] = $verband;
    if (! empty($email))
        $this->header['form_params']['mail'] = $email;
    if (! empty($is_sds_member))
        $this->header['form_params']['is_sds_member'] = $is_sds_member;

    $response = $this->guzzle_client->request(
      'POST',
      "api/v1/civicrm/updateuser/{$old_user_id}",
      $this->header
    );
    $content = json_decode($response->getBody()->getContents(), TRUE);
    if ($content['success'] != TRUE) {
      throw new CRM_Lijuapi_Exceptions_UpdateUserException("Failed to update data for member {$liju_member_id}! Email: {$email}; Verband: {$verband} Error Message: " . $content['error']);
    }
  }

}
