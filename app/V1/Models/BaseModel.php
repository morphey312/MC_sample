<?php

namespace App\V1\Models;

use Illuminate\Database\Eloquent\Model;
use Masterfri\SmartRelations\SmartRelations;
use App\V1\Repositories\Query\Builder\EloquentBuilder;
use App\V1\Repositories\Query\Builder\QueryBuilder;
use App\V1\Repositories\Relations\FileAttachment as FileAttachmentRelation;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\V1\Repositories\Relations\ComplexHasMany;
use App\V1\Repositories\Relations\ComplexBelongsToMany;
use App\V1\Repositories\Relations\BelongsToBidirect;
use App\V1\Repositories\Relations\HasOneBidirect;
use App\V1\Repositories\Relations\HasManyBidirect;
use App\V1\Traits\Models\TranslateAttribute;

class BaseModel extends Model
{
    use SmartRelations;
    use TranslateAttribute;
    
    /**
     * Extract value of attribute of related model
     * 
     * @param string $key
     * @param mixed $default
     * 
     * @return mixed
     */ 
    public function extract($key, $default = null)
    {
        return object_get($this, $key, $default);
    }
    
    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * 
     * @return EloquentBuilder
     */
    public function newEloquentBuilder($query)
    {
        return new EloquentBuilder($query);
    }
    
    /**
     * Get a new query builder instance for the connection.
     *
     * @return QueryBuilder
     */
    protected function newBaseQueryBuilder()
    {
        $connection = $this->getConnection();

        return new QueryBuilder(
            $connection, $connection->getQueryGrammar(), $connection->getPostProcessor()
        );
    }
    
    /**
     * Get class name without general prefix
     * 
     * @return string
     */ 
    public function getClassBaseName()
    {
        return str_replace('App\V1\Models\\', '', get_class($this));
    }
    
    /**
     * Create new file attachment relation
     * 
     * @param string $attribute
     * 
     * @return FileAttachmentRelation
     */ 
    public function fileAttachment($attribute)
    {
        $instance = $this->newRelatedInstance(FileAttachment::class);
        return new FileAttachmentRelation($instance->newQuery(), $this, $attribute);
    }

    /**
     * Create new complex has many relation
     * 
     * @param string $related
     * @param array $criterias
     * @param array $conditions
     * 
     * @return ComplexHasMany
     */
    public function hasManyComplex($related, array $criterias, array $conditions = [])
    {
        $instance = $this->newRelatedInstance($related);
        return new ComplexHasMany($instance->newQuery(), $this, $criterias, $conditions);
    }

    /**
     * Create new complex has many relation
     * 
     * @param string $related
     * @param string $table
     * @param string $relatedPivotKey
     * @param string $relatedKey
     * @param array $criterias
     * 
     * @return ComplexBelongsToMany
     */
    public function belongsToManyComplex($related, $table, $relatedPivotKey, $relatedKey, array $criterias, array $conditions = [])
    {
        $instance = $this->newRelatedInstance($related);
        return new ComplexBelongsToMany($instance->newQuery(), $this, $criterias, $table, $relatedPivotKey, $relatedKey, $conditions);
    }

    /**
     * Create new bidirectional belongs to relation
     * 
     * @param string $related
     * @param string $reverseRelation
     * @param string $foreignKey
     * @param string $ownerKey
     * @param string $relation
     * 
     * @return BelongsToBidirect
     */
    public function belongsToBidirect($related, $reverseRelation, $foreignKey = null, $ownerKey = null, $relation = null)
    {
        if (is_null($relation)) {
            $relation = $this->guessBelongsToRelation();
        }

        $instance = $this->newRelatedInstance($related);
        
        if (is_null($foreignKey)) {
            $foreignKey = Str::snake($relation).'_'.$instance->getKeyName();
        }

        $ownerKey = $ownerKey ?: $instance->getKeyName();
        
        return new BelongsToBidirect($instance->newQuery(), $this, $foreignKey, $ownerKey, $relation, $reverseRelation);
    }

    /**
     * Create new bidirectional has one relation
     *
     * @param  string  $related
     * @param  string  $reverseRelation
     * @param  string  $foreignKey
     * @param  string  $localKey
     * @return HasOneBidirect
     */
    public function hasOneBidirect($related, $reverseRelation, $foreignKey = null, $localKey = null)
    {
        $instance = $this->newRelatedInstance($related);
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $localKey = $localKey ?: $this->getKeyName();

        return new HasOneBidirect($instance->newQuery(), $this, $instance->getTable().'.'.$foreignKey, $localKey, $reverseRelation);
    }

    /**
     * Create new bidirectional has many relation
     *
     * @param  string  $related
     * @param  string  $reverseRelation
     * @param  string  $foreignKey
     * @param  string  $localKey
     * @return HasManyBidirect
     */
    public function hasManyBidirect($related, $reverseRelation, $foreignKey = null, $localKey = null)
    {
        $instance = $this->newRelatedInstance($related);
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $localKey = $localKey ?: $this->getKeyName();

        return new HasManyBidirect($instance->newQuery(), $this, $instance->getTable().'.'.$foreignKey, $localKey, $reverseRelation);
    }
    
    /**
     * Reparent relations from another model
     * 
     * @param Model $source
     * @param string|array $relations
     * @param bool $quick
     */ 
    public function reparent(Model $source, $relations, $quick = true)
    {
        if (!($source instanceof static)) {
            throw new \Exception('Can not reparent relations of model of different classes');
        }
        
        $relations = is_array($relations) ? $relations : [$relations];
        
        foreach ($relations as $relationName) {
            if (!method_exists($source, $relationName)) {
                throw new \Exception('Relation ' . $relationName . ' does not exist on ' . get_class($source));
            }
            
            $relation = $source->$relationName();
            if ($relation instanceof HasOneOrMany) {
                $this->reparentHasOneOrMany($relation, $quick);
            } else {
                throw new \Exception('Reparent for ' . get_class($relation) . ' yet not implemented');
            }
        }
    }
    
    /**
     * Change parent of related models
     * 
     * @param HasOneOrMany $relation
     * @param bool $quick
     */ 
    protected function reparentHasOneOrMany($relation, $quick)
    {
        $fk = $relation->getForeignKeyName();
        
        if ($quick) {
            $relation->update([
                $fk => $this->getKey(),
            ]);
        } else {
            foreach ($relation->cursor() as $model) {
                $model->setAttribute($fk, $this->getKey());
                $model->save();
            }
        }
    }
    
    /**
     * Get data for broadcast
     * 
     * @return array
     */ 
    public function getBroadcastPayload()
    {
        return [
            'id' => $this->id,
        ];
    }

    /**
     * Get loggable attribute
     * 
     * @param string $key 
     * 
     * @return mixed
     */ 
    public function getLoggableAttribute($key)
    {
        return $this->getAttribute($key);
    }

    /*
     * Watching lazy loading 
     * This intends to be a helper to detect unwanted lazy loading in resources
     * Uncomment this method if you want to optimize your resource
     *//*/
    protected function getRelationshipFromMethod($method)
    {
        $traces = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10), 4);
        $trace = implode('', array_map(function($t) {
            return "\n" . sprintf('  form %s::%s at %s:%d', 
                $t['class']??'', $t['function']??'{main}', $t['file']??'{main}', $t['line']??'0');
        }, $traces));
        \Log::debug(sprintf('Lazy loading relation %s::%s %s', get_class($this), $method, $trace));
        return parent::getRelationshipFromMethod($method);
    }
    //*/
}