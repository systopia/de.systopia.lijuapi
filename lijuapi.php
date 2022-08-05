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
  if($objectName == "Email" && in_array($op, ['update', 'edit'])) {
    CRM_Lijuapi_Utils::email_hook($op, $objectName, $objectId,$objectRef);
  }
  if ($objectName == "GroupContact" && $op == "create") {
    CRM_Lijuapi_Utils::change_lv_hook($op, $objectName, $objectId,$objectRef);
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
