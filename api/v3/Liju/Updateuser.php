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
}

/**
 * Liju.Updateuser API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws API_Exception
 */
function civicrm_api3_liju_Updateuser($params) {
  try {

  } catch (Exception $e) {
    throw new API_Exception('Error Message','12345');
  }
}
