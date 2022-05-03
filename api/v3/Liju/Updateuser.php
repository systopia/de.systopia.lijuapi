<?php
use CRM_Lijuapi_ExtensionUtil as E;

/**
 * Liju.Updateuser API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_liju_Updateuser_spec(&$spec) {
  $spec['liju_member_id']['api.required'] = 1;
  $spec['email']['api.required'] = 0;
  $spec['verband']['api.required'] = 0;
}

/**
 * Liju.Updateuser API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws CRM_Lijuapi_Exceptions_UpdateUserException
 * @throws CiviCRM_API3_Exception
 * @throws \GuzzleHttp\Exception\GuzzleException
 * @see civicrm_api3_create_success
 *
 */
function civicrm_api3_liju_Updateuser($params) {
    $api_interface = new CRM_Lijuapi_ApiInterface();
    $api_interface->update_liju_user($params['liju_member_id'], $params['email'], $params['verband']);
    return civicrm_api3_create_success(["Contact with LiJu MemberID ({$params['liju_member_id']}) updated to new LV" => $params['new_lv']]);
}
