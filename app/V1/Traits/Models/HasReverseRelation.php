<?php

namespace App\V1\Traits\Models;

trait HasReverseRelation
{
    /**
     * @var array
     */
    protected $reverseRelations = [];

    /**
     * Get all reverse relations
     * 
     * @return array
     */
    public function getReverseRelations()
    {
        return $this->reverseRelations;
    }

    /**
     * Set value for reverse relation
     * 
     * @param string $name
     * @param mixed $value
     * 
     * @return $this
     */
    public function setReverseRelation($name, $value)
    {
        $this->reverseRelations[$name] = $value;
        return $this;
    }

    /**
     * Get value for reverse relation
     * 
     * @param string $name
     * 
     * @return mixed
     */
    public function getReverseRelation($name)
    {
        return $this->reverseRelations[$name];
    }

    /**
     * Unset reverse relation
     * 
     * @param string $name
     * 
     * @return $this
     */
    public function unsetReverseRelation($name)
    {
        unset($this->reverseRelations[$name]);
        return $this;
    }

    /**
     * Check if reverse relation was set
     * 
     * @param string $name
     * 
     * @return bool
     */
    public function reverseRelationSet($name)
    {
        return array_key_exists($name, $this->reverseRelations);
    }

    /**
     * Get a relationship.
     *
     * @param  string  $key
     * 
     * @return mixed
     */
    public function getRelationValue($key)
    {
        if ($this->reverseRelationSet($key)) {
            return $this->reverseRelations[$key];
        }

        return parent::getRelationValue($key);
    }
}