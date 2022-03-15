<?php

namespace App\V1\Models\Patient;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Models\Patient;
use App\V1\Models\Clinic;
use App\V1\Models\DiscountCardType;
use App\V1\Models\DiscountCardType\NumberingKind;
use App\V1\Models\Appointment;
use App\V1\Traits\Models\HasConstraint;

class IssuedDiscountCard extends BaseModel implements SharedResourceInterface
{
    use SharedResource,
        HasConstraint;

    /**
     * @var string
     */ 
    protected $table = 'issued_discount_cards';

    /**
     * @var array
     */
    protected $fillable = [
        'discount_card_type_id',
        'clinic_id',
        'number',
        'issued',
        'valid_from',
        'expires',
        'comment',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'appointments',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->pickNumber();
        });
    }


    /**
     * Related patients
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */ 
    public function patients()
    {
        return $this->belongsToMany(Patient::class, 'patient_issued_cards', 'issued_card_id', 'patient_id')
            ->withPivot('disabled', 'is_owner');
    }

    /**
     * Get related card owner
     * 
     * @return Patient
     */ 
    public function card_owner()
    {
        return $this->patients()->wherePivot('is_owner', 1);
    }

    /**
     * Get card owner attribute
     * 
     * @return Patient
     */ 
    public function getOwnerAttribute()
    {
        return $this->card_owner()->first();
    }

    /**
     * Related clinic
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */ 
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Related discount card type
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */ 
    public function discount_card_type()
    {
        return $this->belongsTo(DiscountCardType::class);
    }

    /**
     * Get card type with related number kind and clinics
     * 
     * @return DiscountCardType
     */ 
    public function getCardType()
    {
        return $this->discount_card_type()
            ->with(['number_kind.clinics' => function($query) {
                $query->where('clinic_id', $this->clinic_id);
            }])->first();
    }

    /**
     * Get card type with related number kind and clinics
     * 
     * @return DiscountCardType
     */ 
    public function getCardTypeClinic($discount_type)
    {
        return $discount_type->number_kind->clinics->firstWhere('id', $this->clinic_id);
    }

    /**
     * Issue a number to the card
     */ 
    public function pickNumber()
    {
        $discount_type = $this->getCardType();
        $queryParams = [];
        $queryParams['discount_card_type_id'] = $this->discount_card_type_id;
        
        if ($discount_type->number_kind && $discount_type->number_kind->clinics) {
            $clinic = $this->getCardTypeClinic($discount_type);
            if ($clinic && $clinic->pivot->numbering_type == NumberingKind::TYPE_CLINIC) {
                $queryParams['clinic_id'] = $this->clinic_id;
            }
        }

        $number = (int) self::where($queryParams)->max('number');
        
        if ($number > 0) {
            $this->number = $number + 1;
        } else {
            $this->number = 1;
        }
    }

    /**
     * Get card number
     *
     * @return string
     */
    public function getCardNumberAttribute()
    {
        $discount_type = $this->getCardType();
        
        if ($discount_type) {
            if ($discount_type->number_kind && $discount_type->number_kind->clinics) {
                $clinic = $this->getCardTypeClinic($discount_type);
                if ($clinic) {
                    return implode('', [$clinic->pivot->prefix, $this->number, $clinic->pivot->suffix]);
                }
            }
        }

        return $this->number;
    }

    /**
     * Get active holders count
     */ 
    public function getActiveHoldersAttribute()
    {
        return $this->patients()->wherePivot('disabled', 0)->count();
    }

    /**
     * Related appointments
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */ 
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'discount_card_id');
    }

    /**
     * Get active holders count
     */ 
    public function getUsedInAppointmentsAttribute()
    {
        return $this->appointments()
            ->where('is_deleted', '=', 0)
            ->get();
    }
}
