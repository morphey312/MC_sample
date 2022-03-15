<?php

namespace App\V1\Models\Patient;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\V1\Models\Patient;

class Relative extends Pivot
{
    const SON = 'son';
    const DAUGHTER = 'daughter';
    const FATHER = 'father';
    const MOTHER = 'mother';
    const GRANDFATHER = 'grandfather';
    const GRANDMOTHER = 'grandmother';
    const GRANDSON = 'grandson';
    const GRANDDAUGHTER = 'granddaughter';
    const BROTHER = 'brother';
    const SISTER = 'sister';
    const HUSBAND = 'husband';
    const WIFE = 'wife';
    const UNCLE = 'uncle';
    const AUNT = 'aunt';
    const NEPHEW = 'nephew';
    const NIECE = 'niece';
    const GUARDIAN = 'guardian';
    const WARD = 'ward';

    const CHILDREN = [
        'male' => self::SON,
        'female' => self::DAUGHTER,
    ];

    const PARENTS = [
        'male' => self::FATHER,
        'female' => self::MOTHER,
    ];

    const GRANDPARENTS = [
        'male' => self::GRANDFATHER,
        'female' => self::GRANDMOTHER,
    ];

    const GRANDCHILDREN = [
        'male' => self::GRANDSON,
        'female' => self::GRANDDAUGHTER,
    ];

    const SIBLINGS = [
        'male' => self::BROTHER,
        'female' => self::SISTER,
    ];

    const MARRIAGE = [
        'male' => self::HUSBAND,
        'female' => self::WIFE,
    ];

    const UNCLE_AUNT = [
        'male' => self::UNCLE,
        'female' => self::AUNT,
    ];

    const NEPHEWS = [
        'male' => self::NEPHEW,
        'female' => self::NIECE,
    ];
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'patient_relatives';
    
    /**
     * @var bool
     */ 
    public $timestamps = false;

     /**
     * @var array
     */ 
    protected $casts = [
        'show_in_cabinet' => 'boolean',
        'show_in_cabinet_after_14' => 'boolean',
    ];

    /**
     * Get oposite relation value
     * 
     * @param string $relation
     * @param string $gender
     * 
     * return @mixed
     */
    public static function getOpositeRelation($relation, $gender)
    {
        if (empty($gender)) {
            $gender = 'male';
        }

        if ($relation === self::SON || $relation === self::DAUGHTER) {
            return self::PARENTS[$gender];
        }

        if ($relation === self::FATHER || $relation === self::MOTHER) {
            return self::CHILDREN[$gender];
        }

        if ($relation === self::GRANDSON || $relation === self::GRANDDAUGHTER) {
            return self::GRANDPARENTS[$gender];
        }

        if ($relation === self::GRANDFATHER || $relation === self::GRANDMOTHER) {
            return self::GRANDCHILDREN[$gender];
        }

        if ($relation === self::BROTHER || $relation === self::SISTER) {
            return self::SIBLINGS[$gender];
        }

        if ($relation === self::HUSBAND || $relation === self::WIFE) {
            return self::MARRIAGE[$gender];
        }

        if ($relation === self::UNCLE || $relation === self::AUNT) {
            return self::NEPHEWS[$gender];
        }

        if ($relation === self::NEPHEW || $relation === self::NIECE) {
            return self::UNCLE_AUNT[$gender];
        }

        if ($relation === self::GUARDIAN) {
            return self::WARD;
        }

        if ($relation === self::WARD) {
            return self::GUARDIAN;
        }

        return null;
    }

    /**
     * Get nested oposite relation value
     * 
     * @param string $relation
     * @param string $parentRelation
     * @param string $gender
     * 
     * return @mixed
     */
    public static function getDeepOpositeRelation($relation, $parentRelation, $gender)
    {
        if (empty($gender)) {
            $gender = 'male';
        }
        
        if ($parentRelation === self::WARD || $parentRelation === self::GUARDIAN) {
            return null;
        }

        if ($relation === self::BROTHER || $relation === self::SISTER) {
            return self::getOpositeRelation($parentRelation, $gender);
        }

        if ($relation === self::FATHER || $relation === self::MOTHER) {
            if ($parentRelation === self::BROTHER || $parentRelation === self::SISTER) {
                return self::PARENTS[$gender];
            }

            if ($parentRelation === self::SON || $parentRelation === self::DAUGHTER) {
                return self::GRANDPARENTS[$gender];
            }

            if ($parentRelation === self::GRANDFATHER || $parentRelation === self::GRANDMOTHER) {
                return self::CHILDREN[$gender];
            }
        }

        if ($relation === self::GRANDFATHER || $relation === self::GRANDMOTHER) {
            if ($parentRelation === self::BROTHER || $parentRelation === self::SISTER) {
                return self::GRANDPARENTS[$gender];
            }

            if ($parentRelation === self::FATHER || $parentRelation === self::MOTHER) {
                return self::PARENTS[$gender];
            }
        }

        if ($relation === self::UNCLE || $relation === self::AUNT) {
            if ($parentRelation === self::BROTHER || $parentRelation === self::SISTER) {
                return self::UNCLE_AUNT[$gender];
            }
        }

        if ($relation === self::HUSBAND || $relation === self::WIFE) {
            if ($parentRelation === self::SON || $parentRelation === self::DAUGHTER) {
                return self::PARENTS[$gender];
            }

            if ($parentRelation === self::GRANDSON || $parentRelation === self::GRANDDAUGHTER) {
                return self::GRANDPARENTS[$gender];
            }

            if ($parentRelation === self::HUSBAND || $parentRelation === self::WIFE) {
                return self::CHILDREN[$gender];
            }
        }

        if ($relation === self::SON || $relation === self::DAUGHTER) {
            if ($parentRelation === self::HUSBAND || $parentRelation === self::WIFE) {
                return self::CHILDREN[$gender];
            }

            if ($parentRelation === self::FATHER || $parentRelation === self::MOTHER) {
                return self::GRANDCHILDREN[$gender];
            }

            if ($parentRelation === self::GRANDSON || $parentRelation === self::GRANDDAUGHTER) {
                return self::PARENTS[$gender];
            }

            if ($relation === self::SON || $relation === self::DAUGHTER) {
                return self::SIBLINGS[$gender];
            }
        }

        if ($relation === self::GRANDSON || $relation === self::GRANDDAUGHTER) {
            if ($parentRelation === self::SON || $parentRelation === self::DAUGHTER) {
                return self::CHILDREN[$gender];
            }

            if ($parentRelation === self::GRANDSON || $parentRelation === self::GRANDDAUGHTER) {
                return self::SIBLINGS[$gender];
            }

            if ($parentRelation === self::HUSBAND || $parentRelation === self::WIFE) {
                return self::GRANDCHILDREN[$gender];
            }
        }
        
        return null;
    }
    
    /**
     * Related patient
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function relative()
    {
        return $this->belongsTo(Patient::class, 'relative_id');
    }
}