<?php


namespace App\V1\Contracts\Services\Esputnik;


interface Contact
{
    /**
     * Add contact with single array
     *
     * @param $data
     * @return $this
     */
    public function addContact($data): self;

    /**
     * Set empty contact data for new contact data fill chain
     *
     * @return $this
     */
    public function newContact(): self;

    /**
     * Set first name of new contact
     *
     * @param string $value
     * @param bool $excludeFromContactFields
     * @return $this
     */
    public function setFirstName($value, $excludeFromContactFields = false): self;

    /**
     * Add new channel to new contact data
     *
     * @param string $type
     * @param mixed $value
     * @return $this
     */
    public function addChannel($type, $value): self;

    /**
     * Set contact field value
     *
     * field can be nested using dot notation
     *
     * @param string $field
     * @param mixed $value
     * @return $this
     */
    public function setField($field, $value): self;

    /**
     * Add custom field to new contact
     *
     * @param integer $field
     * @param mixed $value
     * @return $this
     */
    public function addCustomField($field, $value): self;

    /**
     * Store new contact to request
     *
     * @return $this
     */
    public function storeNewContact(): self;

    /**
     * Set option
     *
     * @param string $option
     * @param mixed $value
     * @param bool $add
     * @return Contact
     */
    public function setOption($option, $value, $add = false): Contact;
}
