<?php

use CRM_Lijuapi_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Lijuapi_Form_Settings extends CRM_Core_Form {
  public function buildQuickForm() {
    // get current settings to pre-fill
    $config = CRM_Lijuapi_Config::singleton();
    $current_values = $config->getSettings();

    $this->add(
      'text',
      'api_base_url',
      E::ts('LiJu API Base URL'),
      array("class" => "huge"),
      TRUE
    );
    $this->add(
      'text',
      'username',
      E::ts('liJuAPI username'),
      array("class" => "huge"),
      TRUE
    );
    $this->add(
      'password',
      'authorization_token',
      E::ts('LiJu API Authorization Token'),
      array("class" => "huge"),
      TRUE
    );

    // Select Custom Field for Invite Links
    $this->addElement(
      'select',
      'invitelink_custom_field',
      E::ts("Invite Link Custom Field"), CRM_Lijuapi_Utils::get_contact_custom_fields(),
      ['class' => 'crm-select2'],
      TRUE
    );

    $this->add(
      'text',
      'notification_email',
      E::ts('Notification Email Address'),
      array("class" => "huge"),
      TRUE
    );

    // submit
    $this->addButtons(array(
      array(
        'type'      => 'submit',
        'name'      => E::ts('Save'),
        'isDefault' => TRUE,
      ),
    ));

    Civi::log()->log("INFO", "HELLO THERE.");
    error_log("test");

    // set default values
    $this->setDefaults($current_values);

    // export form elements
    parent::buildQuickForm();
  }

  public function postProcess() {
    $config = CRM_Lijuapi_Config::singleton();
    $values = $this->exportValues();
    $settings = $config->getSettings();
    foreach ($this->getSettingsInForm() as $name) {
      $settings[$name] = CRM_Utils_Array::value($name, $values, NULL);
    }
    $config->setSettings($settings);

    parent::postProcess();
  }


  /**
   * get the elements of the form
   * used as a filter for the values array from post Process
   * @return array
   */
  protected function getSettingsInForm() {
    return array(
      'api_base_url',
      'username',
      'authorization_token',
      'invitelink_custom_field',
      'notification_email',
    );
  }

}
