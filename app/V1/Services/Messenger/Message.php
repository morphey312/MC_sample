<?php

namespace App\V1\Services\Messenger;

use App\V1\Contracts\Services\Messenger\Message as MessageInterface;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\V1\Models\Notification\Template;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

abstract class Message extends Mailable implements MessageInterface
{
    use Queueable, SerializesModels;

    /**
     * @var Template
     */
    protected $template;

    /**
     * @var string
     */
    protected $viewName = 'messages.html';

    /**
     * @inheritdoc
     */
    public function using(Template $template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function beforeSend($address, $template)
    {
    }


    /**
     * @inheritdoc
     */
    public function afterDelivery($success, $address, $data = null)
    {
    }

    /**
     * @inheritdoc
     */
    public function getHtml()
    {
        $this->viewName = 'messages.html';

        return (string) $this->render();
    }

    /**
     * @inheritdoc
     */
    public function getPlainText()
    {
        $this->viewName = 'messages.plain-text';

        return html_entity_decode((string) $this->render());
    }

    /**
     * Compose message body
     *
     * @param array $data
     * @param mixed $attachments
     *
     * @return Message
     */
    protected function compose($data = [], $attachments = null, $is_secure = false)
    {
        if ($this->template === null) {
            throw new Exception("Template was not specified");
        }

        $this
            ->view($this->viewName)
            ->with($this->template->compose($data));

        if ($attachments !== null && !empty($attachments[0])) {
            $attachment = $attachments[0];
            if($is_secure) {
                $file = Storage::disk($attachment->disk)->get($attachment->path);

                $newFileName = Str::random();
                $newPath = Storage::disk('local')->path('security_analysis') . "/$newFileName.zip";

                Storage::disk('local')->makeDirectory('security_analysis');

                $zip = new ZipArchive();
                $zip->open($newPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
                $zip->addFromString($attachment->name, $file);
                $zip->setEncryptionName($attachment->name, ZipArchive::EM_AES_256, $data['password']);
                $zip->close();

                $this->attachFromStorageDisk('local', "security_analysis/$newFileName.zip", $data['analysisName'], [
                    'mime' => 'application/zip',
                ]);
            } else {
                $this->attachFromStorageDisk($attachment->disk, $attachment->path, $attachment->name, [
                    'mime' => $attachment->mime_type,
                ]);
            }
        }

        return $this;
    }

    /**
     * Compose message subject
     *
     * @param \Illuminate\Mail\Message  $message
     *
     * @return Message
     */
    protected function buildSubject($message)
    {
        if ($this->template === null) {
            throw new Exception("Template was not specified");
        }

        $message->subject($this->template->subject);
        return $this;
    }

    /**
     * Add the sender to the message.
     *
     * @param  \Illuminate\Mail\Message  $message
     *
     * @return Message
     */
    protected function buildFrom($message)
    {
        if ($this->template === null) {
            throw new Exception("Template was not specified");
        }

        $channel = $this->template->channel;

        $emailData = $this->getEmailFromField($channel);

        $message->from($emailData['from'], $emailData['name']);

        return $this;
    }

    protected function getEmailFromField($channel){
        $from = $channel->account_name;
        $name = $channel->settings['signature'];

        if (preg_match('/^(.*)<(.+@.+)>/', $name, $matches)) {
            $name = trim($matches[1]);
            $from = $matches[2];
        }

        return ['from' => $from, 'name' => $name];
    }
}
