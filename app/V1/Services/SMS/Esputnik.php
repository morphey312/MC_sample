<?php

namespace App\V1\Services\SMS;

use App\V1\Contracts\Services\Sms;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use App\V1\Traits\Services\EsputnikClient;

class Esputnik implements Sms
{
    use EsputnikClient;

    const GET = 'GET';
    const POST = 'POST';

    /**
     * @var string
     */
    protected $sender;

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct($config)
    {
        if(!empty($config['sender_name'])){
            $this->sender = $config['sender_name'];
        }

        $this->setupClient($config);
    }

    /**
     * @inheritdoc
     */
    public function sendMessage($number, $text)
    {
        Log::debug(sprintf('Sending SMS to %s: %s', $number, $text));

        if ($this->debugMode) {
            Log::debug(sprintf('SMS successfully sent to %s [DEBUG MODE]: %s', $number, $text));
            return true;
        } else {
            $response = $this->request(self::POST, 'message/sms', [
                'from' => $this->sender,
                'text' => $text,
                'phoneNumbers' => [
                    $number,
                ],
            ]);

            if ($response !== null) {
                Log::debug(sprintf('SMS successfully sent to %s: %s', $number, $text), [
                    'response' => $response,
                ]);
                return $response;
            }

            return false;
        }
    }

    public function checkSmsStatus(array $IDs)
    {
        Log::debug(sprintf('Checking SMS status in Esputnik'));

        $response = $this->request(self::GET, 'message/status', [
            'ids' => implode(',', $IDs)
        ]);

        if ($response !== null) {
            Log::debug(sprintf('Checked Statuses for sent sms'), [
                'response' => $response,
            ]);
            return $response;
        }

        return false;
    }

}
