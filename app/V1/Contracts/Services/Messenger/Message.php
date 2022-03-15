<?php

namespace App\V1\Contracts\Services\Messenger;

use App\V1\Models\Notification\Template;

interface Message
{
    const STATUS_NO_DELIVERY = 'none';
    const STATUS_RE_DELIVERY = 're_send';
    const STATUS_PREPARED = 'prepared';
    const STATUS_DELIVERING = 'pending';
    const STATUS_DELIVERY_OK = 'ok';
    const STATUS_DELIVERY_FAILED = 'failed';
    const STATUS_OPENED = 'open';
    const STATUS_COMPLAINT = 'complaint';
    const STATUS_DELIVERY_OK_FAILED = 'ok_failed';

    /**
     * Get scenario which is related to this message
     * 
     * @return string
     */ 
    public function getScenario();
    
    /**
     * Get clinic which is related to this message
     * 
     * @return int
     */ 
    public function getClinicId();
    
    /**
     * Get recipient
     * 
     * @return mixed
     */ 
    public function getRecipient();

    /**
     * Set template to use at the moment
     * 
     * @param Template $template
     * 
     * @return Message
     */
    public function using(Template $template);

    /**
     * This function will be called after message delivery
     *
     * @param bool $success
     * @param string $address
     * @param mixed $data
     */
    public function afterDelivery($success, $address, $data = null);

    /**
     * This function will be called before message is sent
     *
     * @param bool $success
     */
    public function beforeSend($address, $template);

    /**
     * Get message html
     * 
     * @return string
     */
    public function getHtml();

    /**
     * Get message text
     * 
     * @return string
     */
    public function getPlainText();
}
