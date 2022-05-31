<?php

require_once 'lijuapi.civix.php';
// phpcs:disable
use CRM_Lijuapi_ExtensionUtil as E;
// phpcs:enable

use \Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Implements hook_civicrm_container()
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_container/
 */
function lijuapi_civicrm_container(ContainerBuilder $container)
{
    $container->addCompilerPass(new \Civi\Lijuapi\ContainerSpecs());
}

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function lijuapi_civicrm_config(&$config) {
  _lijuapi_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function lijuapi_civicrm_install() {
  _lijuapi_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function lijuapi_civicrm_postInstall() {
  _lijuapi_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function lijuapi_civicrm_uninstall() {
  _lijuapi_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function lijuapi_civicrm_enable() {
  _lijuapi_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function lijuapi_civicrm_disable() {
  _lijuapi_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function lijuapi_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _lijuapi_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function lijuapi_civicrm_entityTypes(&$entityTypes) {
  _lijuapi_civix_civicrm_entityTypes($entityTypes);
}

function lijuapi_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  // only react for Email Entity update and edit
  if($objectName != "Email" && in_array($op, ['view', 'merge', 'create', 'delete'])) {
    return;
  }

  $contact_id = $objectRef->contact_id;
  $email = $objectRef->email;
  $email_id = $objectRef->id;
  // check if this contact is a member
  // TODO: What happens in case of multiple emails? Should only the primary email
  // be in the Database?
  try{
    $landesverband = CRM_Lijuapi_Utils::get_lv($contact_id);
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

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function lijuapi_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function lijuapi_civicrm_navigationMenu(&$menu) {
//  _lijuapi_civix_insert_navigation_menu($menu, 'Mailings', [
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ]);
//  _lijuapi_civix_navigationMenu($menu);
//}
