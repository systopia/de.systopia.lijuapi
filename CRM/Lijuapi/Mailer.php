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
 * CRM_Lijuapi_Mailer
 * Mailer class implementation. Sends a pre configured template to a configured Email
 *
 * Use CRM_Lijuapi_Mailer->send_error_mail()
 */
class CRM_Lijuapi_Mailer
{

  private $email_from = 'civi-notify@linksjugend-solid.de';
  private $email_name_from = 'LiJu API Notification';
  private $subject = 'Fehler in CiviCRM LiJu API';
  private $sender_contact_id = '2';
  private $template_name = 'lijuapi_error_notification';

  private $to_email = "";

  /**
   * @throws CRM_Lijuapi_Exceptions_MailAddressConfigurationException
   */
  public function __construct()
  {
    $config = CRM_Lijuapi_Config::singleton();
    $this->to_email = $config->getSetting('notification_email');
    if (empty($this->to_email)) {
      throw new CRM_Lijuapi_Exceptions_MailAddressConfigurationException("Please configure a Recipient Mail address!");
    }
  }

  /**
   * @param $contact_id
   * @param $contact_email
   * @param $landesverband
   * @param $error_message
   * @param $group_id
   * @return void
   * @throws CiviCRM_API3_Exception
   */
  public function send_error_mail($contact_email, $landesverband, $error_message, $contact_id = NULL, $group_id = NULL)
  {
    $smarty_variables = [
      'contact_id' => $contact_id,
      'contact_email' => $contact_email,
      'contact_landesverband' => $landesverband,
      'contact_group_id' => $group_id,
      'contact_error_message' => $error_message,
      'timestamp' => date('H:i:s Y', strtotime("now")),
    ];
    $template_id = $this->get_template_id($this->template_name);

    $values = [];
    $values['to_name'] = $this->to_email;
    $values['to_email'] = $this->to_email;
    $values['id'] = $template_id;
    $values['from'] = "\"{$this->email_name_from}\" <{$this->email_from}>";
    $values['contact_id'] = $this->sender_contact_id;
    $values['template_params'] = $smarty_variables;
    $result = civicrm_api3('MessageTemplate', 'send', $values);
    if ($result['is_error'] == 1) {
      throw new CRM_Lijuapi_Exceptions_EmailSendingErrorException("Error Sending Email Template. " . $result['error_message']);
    }
  }

  /**
   * @param $template_name
   *
   * @return mixed
   * @throws \CiviCRM_API3_Exception
   */
  private function create_template($template_name)
  {
    $template_content = file_get_contents(__DIR__ . "/../../templates/mailer_template.tpl");
    $result = civicrm_api3('MessageTemplate', 'create', [
      'sequential' => 1,
      'msg_title' => $template_name,
      'msg_html' => $template_content,
      'msg_subject' => $this->subject,
    ]);
    if ($result['is_error'] == '1') {
      throw new Exception("Couldn't create message template.");
    }
    return $result['id'];
  }

  /**
   * @param $template_name
   *
   * @return mixed
   * @throws \CiviCRM_API3_Exception
   */
  private function get_template_id($template_name)
  {
    $result = civicrm_api3('MessageTemplate', 'get', array(
      'sequential' => 1,
      'msg_title' => $template_name,
    ));
    if ($result['count'] > '1' || $result['is_error'] == '1') {
      throw new CRM_Lijuapi_Exceptions_MessageTemplateNotFoundException("Error determining Email Template for {$template_name}.");
    }
    if ($result['count'] == '0') {
      return $this->create_template($template_name);
    }
    if ($result['count'] == '1') {
      return $result['id'];
    }
    throw new CRM_Lijuapi_Exceptions_MessageTemplateNotFoundException("Template not found - unclear state. Seek help");
  }
}
