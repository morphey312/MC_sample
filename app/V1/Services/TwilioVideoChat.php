<?php

namespace App\V1\Services;

use App\V1\Contracts\Services\VideoChat;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VideoGrant;
use Exception;

class TwilioVideoChat implements VideoChat
{
    /**
     * @var string
     */
    protected $accountId;

    /**
     * @var string
     */
    protected $authToken;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $apiSecret;

    /**
     * @var Client
     */
    protected $client = null;

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->accountId = $config['account_id'];
        $this->authToken = $config['auth_token'];
        $this->apiKey = $config['api_key'];
        $this->apiSecret = $config['api_secret'];
    }

    /**
     * Get REST client
     *
     * @return Client
     */
    protected function getClient()
    {
        if ($this->client === null) {
            $this->client = new Client($this->accountId, $this->authToken);
        }

        return $this->client;
    }

    /**
     * @inheritdoc
     */
    public function checkRoomStatus($name)
    {
        $client = $this->getClient();

        try {
            return $client->video->v1->rooms($name)->fetch();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function createRoom($name)
    {
        $client = $this->getClient();

        try {
            return $client->video->v1->rooms->create([
                    'type' => 'group-small',
                    'uniqueName' => $name,
                    'StatusCallback' => route('twilio-webhooks-room-status'),
                    'StatusCallbackMethod' => 'POST',
                ]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function grantAccess($room, $identity)
    {
        try {
            $token = new AccessToken($this->accountId, $this->apiKey, $this->apiSecret, 3600, $identity);

            $videoGrant = new VideoGrant();
            $videoGrant->setRoom($room);
            $token->addGrant($videoGrant);
            return $token->toJWT();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get Recordings by room sid
     *
     * @return false|Client|\Twilio\Rest\Video\V1\RecordingInstance[]
     */
    public function getRecordingsByRoomSid(string $room_sid)
    {
        $client = $this->getClient();

        try {
            return $client->video->v1->recordings->read(["groupingSid" => [$room_sid]], 20);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get Recordings by room sid
     *
     * @return \Twilio\Rest\Video\V1\Room\ParticipantInstance[]
     */
    public function getParticipantsLogsByRoomSid(string $room_sid)
    {
        $client = $this->getClient();

        try {
            return $client->video->v1->rooms($room_sid)->participants->read();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Request room video composition
     *
     * @return false|\Twilio\Rest\Video\V1\CompositionInstance
     */
    public function requestVideoComposition(string $room_sid)
    {
        $client = $this->getClient();

        try {
            return $client->video->compositions->create($room_sid, [
                'audioSources' => '*',
                'videoLayout' => array('grid' => array('video_sources' => array('*'))),
                'StatusCallback' => route('twilio-webhooks-composition-status'),
                'StatusCallbackMethod' => 'POST',
                'format' => 'mp4'
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get room video composition by composition ID
     *
     * @return false|\Twilio\Rest\Video\V1\CompositionInstance
     */
    public function getCompositionByCompositionSid($composition_sid)
    {
        $client = $this->getClient();

        try {
            return $client->video->compositions($composition_sid)->fetch();
        } catch (Exception $e) {
            return false;
        }

    }

    /**
     * Get room video composition by composition ID
     *
     * @return false|\Twilio\Rest\Video\V1\CompositionInstance
     */
}
