<?php

use CRM_Lijuapi_ExtensionUtil as E;

/**
 * Liju.Syncuserinvites API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_liju_Syncuserinvites_spec(&$spec)
{
  $spec['group_id']['api.required'] = 0;
  $spec['count']['api.required'] = 0;
}

/**
 * Liju.Syncuserinvites API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws API_Exception
 * @see civicrm_api3_create_success
 *
 */
function civicrm_api3_liju_Syncuserinvites($params)
{
  $group_id = NULL;
  $counter = NULL;
  if (isset($params['group_id'])) {
    $group_id = $params['group_id'];
  }
  if (isset($params['count'])) {
    $counter = $params['count'];
  }
  try {
    $sync_user_invites = new CRM_Lijuapi_SyncUserInvites($group_id, $counter);
    $user_invite_count = $sync_user_invites->run();
  } catch (CRM_Lijuapi_Exceptions_GroupIdNotLandesverbandException $e) {
    throw new API_Exception("Invalid Group ID {$group_id}. Given Group is not a LandesVerband.");
  } catch (CRM_Lijuapi_Exceptions_NoInviteLinkCustomFieldException $e) {
    throw new API_Exception("No Custom Field Available for Invite Links. Please create and configure a valid field.");
  } catch (Exception $e) {
    $user_invite_count = $sync_user_invites->get_current_count();
    throw new API_Exception("Error Occured: {$e->getMessage()}. Created {$user_invite_count} invite Links");
  }
  return civicrm_api3_create_success("Link Creation Sync finished. Created {$user_invite_count} invite Links");
}
