<?php

namespace App\V1\Traits\Relations;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasReverseRelation
{
    /**
     * @var string
     */
    protected $reverseRelation;

    /**
     * Make reverse assosiation
     * 
     * @param \Illuminate\Database\Eloquent\Model $target
     * @param \Illuminate\Database\Eloquent\Model $parent
     */
    public function makeReverseRelation($target, $parent = null)
    {
        if ($parent === null) {
            $parent = $this->parent;
        }

        $relation = $target->{$this->reverseRelation}();

        if (($relation instanceof BelongsTo) || ($relation instanceof MorphTo)) {
            $target->setReverseRelation($this->reverseRelation, $parent);
        } elseif (($relation instanceof HasOne) || ($relation instanceof MorphOne)) {
            $target->setReverseRelation($this->reverseRelation, $parent);
        } elseif (($relation instanceof HasMany) || ($relation instanceof MorphMany)) {
            if ($target->reverseRelationSet($this->reverseRelation)) {
                $collection = $target->getReverseRelation($this->reverseRelation);
                $collection->push($parent);
            } else {
                $collection = $parent->newCollection();
                $collection->push($parent);
            }
            $target->setReverseRelation($this->reverseRelation, $collection);
        }
    }
}