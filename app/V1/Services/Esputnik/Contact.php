<?php


namespace App\V1\Services\Esputnik;


use App\V1\Contracts\Services\Esputnik\Contact as Contract;
use App\V1\Traits\Esputnik\SendsRequest;
use App\V1\Contracts\Services\Esputnik\Sender;
use Illuminate\Support\Arr;

class Contact implements Contract
{
    use SendsRequest;

    const ENDPOINT = 'contacts';
    const METHOD = 'post';

    protected $sender;
    protected $newContact = [];

    protected $requestData = [
        'contacts' => [],
        'dedupeOn' => 'sms',
        'contactFields' => [],
        'customFieldsIDs' => [],
        'groupNames' => [],
        'restoreDeleted' => false
    ];

    public function __construct(Sender $sender)
    {
        $this->sender = $sender;
        $this->requestData['contacts'] = collect();
    }

    /**
     * @inheritDoc
     */
    public function addContact($data): Contract
    {
        $this->requestData['contacts'][] = $data;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function newContact(): Contract
    {
        $this->newContact = [];

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setFirstName($value, $excludeFromContactFields = false): Contract
    {
        Arr::set($this->newContact, 'firstName', $value);
        if(!$excludeFromContactFields)
        $this->requestData['contactFields'][] = 'firstName';

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addChannel($type, $value): Contract
    {
        $channels = collect($this->newContact['channels'] ?? null);

        if($channels->where('type', $type)->isEmpty()) {
            $channels->push([
               'type' => $type,
               'value' => $value
            ]);

            $this->newContact['channels'] = $channels->toArray();
            $this->requestData['contactFields'][] = $type;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setField($field, $value): Contract
    {
        Arr::set($this->newContact, $field, $value);

        if(strpos($field, '.')) {
            $field = array_reverse(explode('.', $field))[0];
        }

        $this->requestData['contactFields'][] = $field;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addCustomField($field, $value): Contract
    {
        $data = [
            'id' => $field,
            'value' => $value
        ];

        $this->newContact['fields'][] = $data;
        $this->requestData['customFieldsIDs'][] = $field;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function storeNewContact(): Contract
    {
        $this->requestData['contacts']->push($this->newContact);
        $this->newContact = [];

        $this->requestData['customFieldsIDs'] = array_unique($this->requestData['customFieldsIDs'], SORT_NUMERIC);
        $this->requestData['contactFields'] = array_unique($this->requestData['contactFields'], SORT_STRING);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setOption($option, $value, $add = false): Contract
    {
        if(Arr::has($this->requestData, $option) || $add) {
            $this->requestData[$option] = $value;
        }

        return $this;
    }
}
