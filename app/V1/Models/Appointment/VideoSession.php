<?php

namespace App\V1\Models\Appointment;

use App\V1\Models\BaseModel;
use App\V1\Models\Appointment;
use App\V1\Contracts\Services\VideoChat;
use Illuminate\Support\Facades\Auth;

class VideoSession extends BaseModel
{
    const ROOM_NAME_PREFIX = 'Appointment:';
    const STORAGE_FOLDER   =   'twilio/compositions';
    const RELATION_TYPE = 'video_session';
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'video_sessions';

    protected $fillable = ['appointment_id',
        'room_sid', 'closed', 'composition_sid', 'download_link',
        'composition_status', 'room_status', 'composition_progress',
        'composition_media_url'];

    /**
     * @var array
     */
    protected $casts = ['closed' => 'boolean', 'initiated' => 'boolean',];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->room_name = $model->generateRoomName();
        });
    }

    /**
     * Related appointment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    /**
     * Related attachments
     *
     * @return \App\V1\Repositories\Relations\FileAttachment
     */
    public function attachments()
    {
        return $this->fileAttachment('attachments');
    }

    /**
     * Generate room name
     *
     * @return string
     */
    public function generateRoomName()
    {
        return self::ROOM_NAME_PREFIX . $this->appointment_id;
    }

    /**
     * Prepare a room
     *
     * @return bool
     */
    public function prepareRoom()
    {
        $api = resolve(VideoChat::class);
        $roomStatusResponse = $api->checkRoomStatus($this->room_name);

        if ($roomStatusResponse) {
            if (empty($this->room_sid)) {
                $this->room_sid = $roomStatusResponse->sid;
                $this->save();
            }

            return $roomStatusResponse;
        } else {
            $response = $api->createRoom($this->room_name);

            if ($response) {
                $this->initiated = true;
                $this->room_sid = $response->sid;
                $this->save();

                return $response;
            }
        }


        return false;
    }

    public function getRecordings($sid = null)
    {
        $api = resolve(VideoChat::class);

        $sid = empty($sid) ? $this->room_sid : $sid;
        return $api->getRecordingsByRoomSid($sid);
    }

    public function requestComposition($room_sid = null)
    {
        $api = resolve(VideoChat::class);

        $sid = empty($room_sid) ? $this->room_sid : $room_sid;
        return $api->requestVideoComposition($sid);
    }

    public function getComposition($composition_sid)
    {
        $api = resolve(VideoChat::class);

        return $api->getCompositionByCompositionSid($composition_sid);
    }

    public function requestRoomParticipantLogs($room_sid = null)
    {
        $api = resolve(VideoChat::class);

        $sid = empty($room_sid) ? $this->room_sid : $room_sid;

        return $api->getParticipantsLogsByRoomSid($sid);
    }

    /**
     * Grant access to the room
     *
     * @param string $identity
     *
     * @return string
     */
    protected function grantAccess($identity)
    {
        $api = resolve(VideoChat::class);

        return $api->grantAccess($this->room_name, $identity);
    }

    /**
     * Grant access to the room to patient
     *
     * @return string
     */
    public function grantAccessToPatient()
    {
        if ($this->prepareRoom()) {
            return $this->grantAccess($this->appointment->patient->full_name);
        }

        return null;
    }

    /**
     * Grant access to the room to doctor
     *
     * @return string
     */
    public function grantAccessToDoctor()
    {
        if ($this->prepareRoom()) {
            return $this->grantAccess(Auth::user()->userable->full_name);
        }

        return null;
    }
}
