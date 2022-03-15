<?php


namespace App\V1\Services;

use App\V1\Contracts\Services\Esputnik as Contract;
use App\V1\Contracts\Services\Messenger\Message;
use App\V1\Models\Notification\MailingProvider;
use App\V1\Models\Notification\MailingTemplate;
use App\V1\Services\Esputnik\Event;
use App\V1\Services\Esputnik\Contact;
use App\V1\Services\Esputnik\Sender;
use Illuminate\Support\Facades\Log;

class Esputnik implements Contract
{
    public function newEvent(MailingProvider $provider): Event
    {
        return new Event(new Sender($provider));
    }

    public function newContact(MailingProvider $provider): Contact
    {
        return new Contact(new Sender($provider));
    }
}
