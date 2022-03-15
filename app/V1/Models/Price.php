<?php

namespace App\V1\Models;

use App\V1\Exceptions\PriceException;
use App\V1\Traits\Models\HasConstraint;
use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Models\Price\Set as PriceSet;
use Illuminate\Support\Facades\Auth;
use App\V1\Jobs\WaitList\PriceNotification;
use Carbon\Carbon;

class Price extends BaseModel implements ClinicShared
{
    use HasConstraint;

    const SERVICE_TYPE_SERVICE = Service::RELATION_TYPE;
    const SERVICE_TYPE_ANALYSIS = Analysis::RELATION_TYPE;

    const BASE_PRICE = 'base';
    const CERTIFICATE_PRICE = 'certificate';

    const CURRANCY_UAH = 'uah';

    const RELATION_TYPE = 'price';

    /**
     * @var array
     */
    protected $fillable = [
        'date_from',
        'date_to',
        'cost',
        'self_cost',
        'currency',
        'set_id',
        'service_id',
        'service_type',
        'clinics',
        'external_price_id',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'date_from',
        'date_to',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'cost' => 'float',
        'self_cost' => 'float',
    ];

    /**
     * @var bool
     */
    protected $checkCollisions = true;

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'analysis_results',
        'appointment_services',
        'assigned_services',
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->checkCollisions) {
                $model->checkPriceConflicts();

                if ($model->tryMerge()) {
                    return false;
                }

                if ($model->date_to == null) {
                    $model->checkIsBackdating();
                    if (!$model->closePrevious()) {
                        return false;
                    }
                } else {
                    if ($model->trySplitMergeClosed()) {
                        return false;
                    }
                }
            }
        });

        static::saved(function ($model) {
            if ($model->service_type === static::SERVICE_TYPE_SERVICE
                && $model->price_set->type === static::BASE_PRICE
                && $model->endsInFuture()) {
                PriceNotification::dispatch($model->id, Auth::id());
            }
        });
    }

    /**
     * Related service/analysis
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function service()
    {
        return $this->morphTo();
    }

    /**
     * Related clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'price_clinics', 'price_id', 'clinic_id');
    }

    /**
     * Related appointments
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function service_appointments()
    {
        return $this->belongsToMany(Appointment::class, 'appointment_services', 'price_id', 'appointment_id');
    }

    /**
     * Related appointments
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function analysis_appointments()
    {
        return $this->belongsToMany(Appointment::class, 'analysis_results', 'price_id', 'appointment_id');
    }

    /**
     * Related analysis results
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function analysis_results()
    {
        return $this->hasMany(Analysis\Result::class, 'price_id');
    }

    /**
     * Related appointment services
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointment_services()
    {
        return $this->hasMany(Appointment\Service::class, 'price_id');
    }

    /**
     * Related assigned services
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assigned_services()
    {
        return $this->hasMany(Patient\AssignedService::class, 'price_id');
    }

    /**
     * Try to merge this price into the matching one that already exists
     */
    protected function tryMerge()
    {
        $same = $this->service->prices()
            ->where('id', '!=', $this->id)
            ->where('set_id', '=', $this->set_id)
            ->where('cost', '=', $this->cost)
            ->where('self_cost', '=', $this->self_cost)
            ->where('date_from', '=', $this->date_from)
            ->where(function($query) {
                if ($this->date_to) {
                    $query->where('date_to', '=', $this->date_to);
                } else {
                    $query->whereNull('date_to');
                }
            })->first();

        if ($same == null) {
            return false;
        }

        $same->clinics = $same->clinics->concat($this->clinics);
        $same->checkCollisions = false;
        $same->save();

        if ($this->date_to == null) {
            $this->closePrevious();
        } else {
            $this->trySplitMergeClosed();
        }

        if ($this->exists) {
            $same->reparent($this, [
                'analysis_results',
                'appointment_services',
                'assigned_services',
            ]);
            $this->delete();
        }

        return true;
    }

    /**
     * Close previous price for the same service/clinic
     *
     * @return bool
     */
    protected function closePrevious()
    {
        // Get all previous prices
        $previous = $this->service->prices()
            ->where('id', '!=', $this->id)
            ->where('set_id', '=', $this->set_id)
            ->where('date_from', '<', $this->date_from)
            ->whereNull('date_to')
            ->where(function($query) {
                $query->whereNull('date_to')
                    ->orWhere('date_to', '>=', $this->date_from);
            })
            ->whereHas('clinics', function($query) {
                $query->whereIn('id', $this->clinics->modelKeys());
            })->get();

        if ($previous->count() == 0) {
            // Nothing to close
            return true;
        }

        foreach ($previous as $price) {
            if (!$price->costWasChanged($this)) {
                // If cost was not changed comparing to this price, remove clinics that match old price
                $this->detachClinics($price->clinics->modelKeys());
                if ($this->clinics->count() == 0) {
                    // This price has no more clinics, should no more exists
                    if ($this->exists) {
                        $price->reparent($this, [
                            'analysis_results',
                            'appointment_services',
                            'assigned_services',
                        ]);
                        $this->delete();
                    }
                    return false;
                }
            } else {
                // Cost was changed, close old prices
                if ($this->canReplace($price)) {
                    // If old price can be replaced with this one, just close the old price
                    $price->closeBy($this);
                    $price->checkCollisions = false;
                    $price->save();
                } else {
                    // Else split old price in two, detaching clinics that match the new price
                    // from the first one and closing the second on, reattaching to it detached clinics
                    $convertible = $price->split($this->clinics->modelKeys());
                    $price->checkCollisions = false;
                    $price->save();
                    $convertible->closeBy($this);
                    $convertible->checkCollisions = false;
                    $convertible->save();
                }
            }
        }

        return true;
    }

    /**
     * Check if this price can replace another one
     * Case: this price includes all clinics of the other price
     *
     * @param Price $price
     *
     * @return bool
     */
    protected function canReplace($price)
    {
        return array_diff($price->clinics->modelKeys(), $this->clinics->modelKeys()) === [];
    }

    /**
     * Split price in two, clinics that are specified as the argument
     * will be detached from original and assigned to a copy
     *
     * @param array $clinicIds
     *
     * @return Price
     */
    protected function split($clinicIds)
    {
        $detachedClinics = $this->detachClinics($clinicIds);

        $splitted = new static();
        $splitted->service_id = $this->service_id;
        $splitted->service_type = $this->service_type;
        $splitted->date_from = $this->date_from;
        $splitted->cost = $this->cost;
        $splitted->self_cost = $this->self_cost;
        $splitted->currency = $this->currency;
        $splitted->set_id = $this->set_id;
        $splitted->clinics = $detachedClinics;

        return $splitted;
    }

    /**
     * Remove clinics that match the list from the price and return them
     *
     * @param array $clinicIds
     *
     * @return array
     */
    protected function detachClinics($clinicIds)
    {
        $originalClinics = [];
        $detachedClinics = [];

        foreach ($this->clinics as $clinic) {
            if (in_array($clinic->id, $clinicIds)) {
                $detachedClinics[] = $clinic;
            } else {
                $originalClinics[] = $clinic;
            }
        }

        $this->clinics = $originalClinics;

        return $detachedClinics;
    }

    /**
     * Check if price is getting saved backdating, close it if so
     */
    protected function checkIsBackdating()
    {
        $next = $this->service->prices()
            ->where('id', '!=', $this->id)
            ->where('set_id', '=', $this->set_id)
            ->where('date_from', '>', $this->date_from)
            ->whereHas('clinics', function($query) {
                $query->whereIn('id', $this->clinics->modelKeys());
            })
            ->orderBy('date_from')
            ->get();

        foreach ($next as $price) {
            if ($price->canReplace($this)) {
                $this->closeBy($price);
                break;
            } else {
                $split = $this->split($price->clinics->modelKeys());
                $split->closeBy($price);
                $split->checkCollisions = false;
                $split->save();
            }
        }
    }

    /**
     * Close price by replacing it with other price
     *
     * @param Price $replacement
     */
    protected function closeBy($replacement)
    {
        $this->date_to = $replacement->date_from->copy()->subDay();
    }

    /**
     * Check if price was changed
     *
     * @param Price $compareTo
     *
     * @return bool
     */
    protected function costWasChanged($compareTo)
    {
        return $this->cost != $compareTo->cost
            || $this->self_cost != $compareTo->self_cost
            || $this->currency != $compareTo->currency;
    }

    /**
     * Check if this price brings any conflicts, i.e. same date has different prices
     *
     * @throws PriceException
     */
    protected function checkPriceConflicts()
    {
        $conflict = $this->service->prices()
            ->where('id', '!=', $this->id)
            ->where('set_id', '=', $this->set_id)
            ->where('date_from', '=', $this->date_from)
            ->where(function($query) {
                $query->where('cost', '!=', $this->cost)
                    ->orWhere('self_cost', '!=', $this->self_cost)
                    ->orWhere(function($query) {
                        if ($this->date_to) {
                            $query->where('date_to', '!=', $this->date_to);
                        } else {
                            $query->whereNotNull('date_to');
                        }
                    });
            })
            ->whereHas('clinics', function($query) {
                $query->whereIn('id', $this->clinics->modelKeys());
            })
            ->exists();

        if ($conflict) {
            throw new PriceException('Attempt to assign different prices for the same date');
        }
    }

    /**
     * Split price from other clinics if necessary
     *
     * @return bool     returns true if price was merged
     */
    protected function trySplitMergeClosed()
    {
        $same = $this->service->prices()
            ->where('id', '!=', $this->id)
            ->where('set_id', '=', $this->set_id)
            ->where('date_from', '=', $this->date_from)
            ->whereNull('date_to')
            ->whereHas('clinics', function($query) {
                $query->whereIn('id', $this->clinics->modelKeys());
            })
            ->get();

        foreach ($same as $price) {
            if ($this->canReplace($price)) {
                $price->date_to = $this->date_to;
                $price->checkCollisions = false;
                $price->save();
                return true;
            } else {
                $price->detachClinics($this->clinics->modelKeys());
                $price->checkCollisions = false;
                $price->save();
            }
        }

        return false;
    }

    /**
     * @inherit
     */
    public function getClinicIds()
    {
        return $this->clinics->pluck('id')->all();
    }

    /**
     * Related price set
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function price_set()
    {
        return $this->belongsTo(PriceSet::class, 'set_id');
    }

    /**
     * Model date_to is null or ends in future
     *
     * @return bool
     */
    public function endsInFuture()
    {
        if ($this->date_to == null) {
            return true;
        }
        return Carbon::parse($this->date_to)
            ->greaterThanOrEqualTo(Carbon::now()->format('Y-m-d'));
    }

    /**
     * Related analysis results
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function act_prices()
    {
        return $this->hasMany(\App\V1\Models\PriceAgreementAct\Price::class, 'price_id');
    }

    /**
     * Related actual prices (active or scheduled)
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function prices_participating_in_acts()
    {
        return $this->act_prices()
            ->where(function($query) {
                $query->whereHas('price_agreement_act', function ($query) {
                    $query->where('status', '=', PriceAgreementAct::STATUS_IN_WORK);
                });
            });
    }
}
