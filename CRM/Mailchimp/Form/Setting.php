<?php

class CRM_Mailchimp_Form_Setting extends CRM_Core_Form {

  const 
    MC_SETTING_GROUP = 'MailChimp Preferences';

  /**
   * Function to actually build the form
   *
   * @return None
   * @access public
   */
  public function buildQuickForm() {
    // Add the API Key Element
    $this->addElement('text', 'api_key', ts('API Key'), array(
      'size' => 48,
    ));

    // Create the Submit Button.
    $buttons = array(
      array(
        'type' => 'submit',
        'name' => ts('Save & Test'),
      ),
    );

    // Add the Buttons.
    $this->addButtons($buttons);
  }

  public function setDefaultValues() {
    $defaults = $details = array();

    $apiKey = CRM_Core_BAO_Setting::getItem(self::MC_SETTING_GROUP,
      'api_key', NULL, FALSE
    );
    $defaults['api_key'] = $apiKey;

    return $defaults;
  }

  /**
   * Function to process the form
   *
   * @access public
   *
   * @return None
   */
  public function postProcess() {
    // Store the submitted values in an array.
    $params = $this->controller->exportValues($this->_name);

    // Save the API Key.
    if (CRM_Utils_Array::value('api_key', $params)) {
      CRM_Core_BAO_Setting::setItem($params['api_key'],
        self::MC_SETTING_GROUP,
        'api_key'
      );
      try {
        $mcClient = new Mailchimp($params['api_key']);
        $mcHelper = new Mailchimp_Helper($mcClient);
        $details  = $mcHelper->accountDetails();
      } catch (Mailchimp_Invalid_ApiKey $e) {
        CRM_Core_Session::setStatus($e->getMessage());
        return FALSE;
      } catch (Mailchimp_HttpError $e) {
        CRM_Core_Session::setStatus($e->getMessage());
        return FALSE;
      }

      $message = "Following is the account information received from API callback:<br/>
        <table>
        <tr><td>Company:</td><td>{$details['contact']['company']}</td></tr>
        <tr><td>First Name:</td><td>{$details['contact']['fname']}</td></tr>
        <tr><td>Last Name:</td><td>{$details['contact']['lname']}</td></tr>
        </table>";
      CRM_Core_Session::setStatus($message);
    }
  }
}