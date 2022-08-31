<?php
/*-------------------------------------------------------+
| LinksJugend API                                        |
| Copyright (C) 2022 SYSTOPIA                            |
| Author: P.Batroff (batroff -at- systopia.de)           |
| http://www.systopia.de/                                |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/


namespace Civi\Lijuapi\ActionProvider\Action;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\SpecificationBag;

use Civi\FormProcessor\API\Exception;
use CRM_Lijuapi_ExtensionUtil as E;


class GetInviteLink extends AbstractAction
{

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * TODO: Do we need default configuration parameters for this action?
   * @return SpecificationBag specs
   */
  public function getConfigurationSpecification()
  {
    return new SpecificationBag([]);
  }


  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag specs
   */
  public function getParameterSpecification()
  {
    return new SpecificationBag([
      // required fields
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), true),
      new Specification('email', 'String', E::ts('Email Address'), true),
      new Specification('group_id', 'Integer', E::ts('Landesverband Group ID'), true),
    ]);
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag specs
   */
  public function getOutputSpecification()
  {
    return new SpecificationBag([
      new Specification('liju_invite_link', 'String', E::ts('LiJu Invite Link'), false, null, null, null, false),
      new Specification('liju_member_id', 'Integer', E::ts('LiJu MemberId'), false, null, null, null, false),
      new Specification('error', 'String', E::ts('Error Message in case of failure'), false, null, null, null, false),
    ]);
  }

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output)
  {
    try {
      $contact_id = $parameters->getParameter('contact_id');
      // get Landesverband for User
//      $landesverband =  \CRM_Lijuapi_Utils::get_lv($contact_id);
      $lv_group_id = $parameters->getParameter('group_id');
      $landesverband =  \CRM_Lijuapi_Utils::get_lv_from_group_id($lv_group_id);
      $email = $parameters->getParameter('email');
      // get link for User
      $result = civicrm_api3('Liju', 'createinvite', [
        'email' => $email,
        'liju_member_id' => $contact_id,
        'verband' => $landesverband,
      ]);
      $liju_invite_link = $result['values']['invite_link'];

      // TODO add invite Link to custom field on Contact
      \CRM_Lijuapi_Utils::add_link_to_user($contact_id, $liju_invite_link);
      // This will also be needed for the Email to the user.
      // TODO who sends the confirmation Email to the user?

      // Set link as return parameter
      $output->setParameter('liju_member_id', $parameters->getParameter('contact_id'));
      $output->setParameter('error', '');
      $output->setParameter('liju_invite_link', $liju_invite_link);

    } catch (\Exception $ex) {
      $this->generate_error_report($contact_id, $email, $landesverband, $ex->getMessage());

      $output->setParameter('liju_member_id', $contact_id);
      $output->setParameter('liju_invite_link', $liju_invite_link);
      $output->setParameter('error', $ex->getMessage());
    }
  }

  /**
   * @param $contact_id
   * @param $email
   * @param $landesverband
   * @param $error_message
   * @return void
   * @throws \CRM_Lijuapi_Exceptions_MissingErrorValueException
   * @throws \CiviCRM_API3_Exception
   */
  private function generate_error_report($contact_id, $email, $landesverband, $error_message) {
    $result = civicrm_api3('Email', 'get', [
      'sequential' => 1,
      'email' => $email,
      'contact_id' => $contact_id,
    ]);
    $values['contact_id'] = $contact_id;
    $values['email'] = $email;
    $values['email_id'] = $result['id'];
    $values['landesverband'] = $landesverband;
    $values['errorcode'] = $error_message;
    \CRM_Lijuapi_Utils::set_error_case($values);

    $config = \CRM_Lijuapi_Config::singleton();
    // only send notification if configured
    if ($config->getSetting('notification_email_active')) {
      $mailer = new \CRM_Lijuapi_Mailer();
      $mailer->send_error_mail($contact_id, $email, $landesverband, $error_message);
    }
  }
}
