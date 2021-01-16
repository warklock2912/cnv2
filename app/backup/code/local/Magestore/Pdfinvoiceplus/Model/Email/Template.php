<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Template model
 *
 * Example:
 *
 * // Loading of template
 * $emailTemplate  = Mage::getModel('core/email_template')
 *    ->load(Mage::getStoreConfig('path_to_email_template_id_config'));
 * $variables = array(
 *    'someObject' => Mage::getSingleton('some_model')
 *    'someString' => 'Some string value'
 * );
 * $emailTemplate->send('some@domain.com', 'Name Of User', $variables);
 *
 * @method Mage_Core_Model_Resource_Email_Template _getResource()
 * @method Mage_Core_Model_Resource_Email_Template getResource()
 * @method string getTemplateCode()
 * @method Mage_Core_Model_Email_Template setTemplateCode(string $value)
 * @method string getTemplateText()
 * @method Mage_Core_Model_Email_Template setTemplateText(string $value)
 * @method string getTemplateStyles()
 * @method Mage_Core_Model_Email_Template setTemplateStyles(string $value)
 * @method int getTemplateType()
 * @method Mage_Core_Model_Email_Template setTemplateType(int $value)
 * @method string getTemplateSubject()
 * @method Mage_Core_Model_Email_Template setTemplateSubject(string $value)
 * @method string getTemplateSenderName()
 * @method Mage_Core_Model_Email_Template setTemplateSenderName(string $value)
 * @method string getTemplateSenderEmail()
 * @method Mage_Core_Model_Email_Template setTemplateSenderEmail(string $value)
 * @method string getAddedAt()
 * @method Mage_Core_Model_Email_Template setAddedAt(string $value)
 * @method string getModifiedAt()
 * @method Mage_Core_Model_Email_Template setModifiedAt(string $value)
 * @method string getOrigTemplateCode()
 * @method Mage_Core_Model_Email_Template setOrigTemplateCode(string $value)
 * @method string getOrigTemplateVariables()
 * @method Mage_Core_Model_Email_Template setOrigTemplateVariables(string $value)
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magestore_Pdfinvoiceplus_Model_Email_Template extends Aschroder_SMTPPro_Model_Email_Template
{
    protected $_filedata = array(); //(by dw)    
    /**
     * Send mail to recipient
     *
     * @param   array|string       $email        E-mail(s)
     * @param   array|string|null  $name         receiver name(s)
     * @param   array              $variables    template variables
     * @return  boolean
     **/
  public function send($email, $name = null, array $variables = array())
  {

    $_helper = Mage::helper('smtppro');
    // If it's not enabled, just return the parent result.
    if (!$_helper->isEnabled()) {
      $_helper->log('SMTP Pro is not enabled, fall back to parent class');
      return parent::send($email, $name, $variables);
    }


    // As per parent class - except addition of before and after send events

    if (!$this->isValidForSend()) {
      $_helper->log('Email is not valid for sending, this is a core error that often means there\'s a problem with your email templates.');
      Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
      return false;
    }

    $emails = array_values((array)$email);
    $names = is_array($name) ? $name : (array)$name;
    $names = array_values($names);
    foreach ($emails as $key => $email) {
      if (!isset($names[$key])) {
        $names[$key] = substr($email, 0, strpos($email, '@'));
      }
    }

    $variables['email'] = reset($emails);
    $variables['name'] = reset($names);

    $this->setUseAbsoluteLinks(true);
    $text = $this->getProcessedTemplate($variables, true);
    $subject = $this->getProcessedTemplateSubject($variables);

    $setReturnPath = Mage::getStoreConfig(self::XML_PATH_SENDING_SET_RETURN_PATH);
    switch ($setReturnPath) {
      case 1:
        $returnPathEmail = $this->getSenderEmail();
        break;
      case 2:
        $returnPathEmail = Mage::getStoreConfig(self::XML_PATH_SENDING_RETURN_PATH_EMAIL);
        break;
      default:
        $returnPathEmail = null;
        break;
    }

    // Use the queue IFF it's not bypassed and it's been set.
    if (!$_helper->isQueueBypassed() &&
      $this->hasQueue() && $this->getQueue() instanceof Mage_Core_Model_Email_Queue) {

      /** @var $emailQueue Mage_Core_Model_Email_Queue */
      $emailQueue = $this->getQueue();
      $emailQueue->setMessageBody($text);
      $emailQueue->setMessageParameters(array(
        'subject'           => $subject,
        'return_path_email' => $returnPathEmail,
        'is_plain'          => $this->isPlain(),
        'from_email'        => $this->getSenderEmail(),
        'from_name'         => $this->getSenderName(),
        'reply_to'          => $this->getMail()->getReplyTo(),
        'return_to'         => $this->getMail()->getReturnPath(),
      ))
        ->addRecipients($emails, $names, Mage_Core_Model_Email_Queue::EMAIL_TYPE_TO)
        ->addRecipients($this->_bccEmails, array(), Mage_Core_Model_Email_Queue::EMAIL_TYPE_BCC);
      $emailQueue->addMessageToQueue();

      $_helper->log('Email not sent immediately, queued for sending.');

      return true;
    }

    ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
    ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));

    $mail = $this->getMail();

    if ($returnPathEmail !== null) {
      $mailTransport = new Zend_Mail_Transport_Sendmail("-f".$returnPathEmail);
      Zend_Mail::setDefaultTransport($mailTransport);
    }

    foreach ($emails as $key => $email) {
      $mail->addTo($email, '=?utf-8?B?' . base64_encode($names[$key]) . '?=');
    }

    if($this->isPlain()) {
      $mail->setBodyText($text);
    } else {
      $mail->setBodyHTML($text);
    }

    $mail->setSubject('=?utf-8?B?' . base64_encode($subject) . '?=');
    $mail->setFrom($this->getSenderEmail(), $this->getSenderName());
//(by dw)
    //start
    $atInfo = $this->getEmAttachments();
    if(!empty($atInfo))
    {
      $_file = $mail->createAttachment($atInfo['fileContents']);
      $_file->type = 'application/pdf';
      $_file->filename = $atInfo['fileName'];
    }
    //end
    try {

      $transport = new Varien_Object();
      Mage::dispatchEvent('aschroder_smtppro_template_before_send', array(
        'mail' => $mail,
        'template' => $this,
        'variables' => $variables,
        'transport' => $transport
      ));

      if ($transport->getTransport()) { // if set by an observer, use it
        $mail->send($transport->getTransport());
      } else {
        $mail->send();
      }

      foreach ($emails as $key => $email) {
        Mage::dispatchEvent('aschroder_smtppro_after_send', array(
          'to' => $email,
          'template' => $this->getTemplateId(),
          'subject' => $subject,
          'html' => !$this->isPlain(),
          'email_body' => $text));
      }

      $this->_mail = null;
    }
    catch (Exception $e) {
      $this->_mail = null;
      Mage::logException($e);
      return false;
    }

    return true;
  }
    //(by dw)
    //start
    public function setEmAttachments($attachments)
    {   
    	$this->setOrderAttachments($attachments);
    }
    
    public function getEmAttachments()
    {
        return $this->getOrderAttachments(); 
    }
    
    public function setOrderAttachments($attachments)
    {   
    	$this->_filedata = $attachments;        
        return $this;
    }

    public function getOrderAttachments()
    {    	
        return $this->_filedata;
    }
    //end
    
}
