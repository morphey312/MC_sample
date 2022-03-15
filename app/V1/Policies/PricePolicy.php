<?php

namespace App\V1\Policies;

use Illuminate\Database\Eloquent\Model;
use App\V1\Models\User;
use App\V1\Models\Price;
use App\V1\Contracts\Repositories\Query\Filter;

class PricePolicy extends ClinicSharedPolicy
{
    const INSURANCE_MODULE = 'insurance-prices';

    /**
     * @var array
     */
    protected $providedBy = [
        'service-prices.create' => [
            'service-prices.upload',
        ],
        'service-prices.update' => [
            'service-prices.upload'
        ],
        'analysis-prices.create' => [
            'analysis-prices.upload'
        ],
        'analysis-prices.update' => [
            'analysis-prices.upload'
        ],
    ];

    /**
     * @var  string
     */
    protected $module;

    /**
     * Change module by entity type
     *
     * @param string $type
     */
    protected function setModule($type)
    {
        if ($type === Price::SERVICE_TYPE_ANALYSIS) {
            $this->module = 'analysis-prices';
        } elseif ($type === Price::SERVICE_TYPE_SERVICE) {
            $this->module = 'service-prices';
        } elseif ($type === static::INSURANCE_MODULE) {
            $this->module = 'insurance-prices';
        } else {
            $this->module = 'none';
        }
    }

    /**
     * Check if user can list entities
     *
     * @param User $user
     * @param Filter $filter
     *
     * @return bool
     */
    public function list_service(User $user, Filter $filter = null)
    {
        $this->setModule(Price::SERVICE_TYPE_SERVICE);

        return parent::list($user, $filter);
    }

    /**
     * Check if user can list entities
     *
     * @param User $user
     * @param Filter $filter
     *
     * @return bool
     */
    public function list_analysis(User $user, Filter $filter = null)
    {
        $this->setModule(Price::SERVICE_TYPE_ANALYSIS);

        return parent::list($user, $filter);
    }

   /**
     * @inherit
     */
    public function get(User $user, Model $model)
    {
        $this->setModule($model->service_type);

        return parent::get($user, $model);
    }

    /**
     * Check if user can create an entity
     *
     * @param User $user
     *
     * @return bool
     */
    public function create_service(User $user)
    {
        $this->setModule(Price::SERVICE_TYPE_SERVICE);

        return parent::create($user);
    }

    /**
     * Check if user can create an entity
     *
     * @param User $user
     *
     * @return bool
     */
    public function create_analysis(User $user)
    {
        $this->setModule(Price::SERVICE_TYPE_ANALYSIS);

        return parent::create($user);
    }

    /**
     * @inherit
     */
    public function update(User $user, Model $model)
    {
        $this->setModule($model->service_type);

        return parent::update($user, $model);
    }

    /**
     * @inherit
     */
    public function delete(User $user, Model $model)
    {
        $this->setModule($model->service_type);

        return parent::delete($user, $model);
    }

    /**
     * Check if user can list entities
     *
     * @param User $user
     *
     * @return bool
     */
    public function list_insurance(User $user, $filter)
    {
        $this->setModule(static::INSURANCE_MODULE);

        return parent::list($user, $filter);
    }

    /**
     * Check if user can create an entity
     *
     * @param User $user
     *
     * @return bool
     */
    public function create_insurance(User $user)
    {
        $this->setModule(static::INSURANCE_MODULE);

        return parent::create($user);
    }

    /**
     * Check if user can update an entity
     *
     * @param User $user
     *
     * @return bool
     */
    public function update_insurance(User $user, Model $model)
    {
        $this->setModule(static::INSURANCE_MODULE);

        return parent::update($user, $model);
    }
}
