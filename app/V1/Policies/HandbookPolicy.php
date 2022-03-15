<?php

namespace App\V1\Policies;

use Illuminate\Database\Eloquent\Model;
use App\V1\Models\User;
use App\V1\Models\Handbook;
use Permissions;

class HandbookPolicy extends BasePolicy
{
    /**
     * @inherit
     */
    public function listCategory(User $user, $category)
    {
        return Permissions::has($user, $category . '.' . self::ACTION_ACCESS);
    }

    /**
     * @inherit
     */
    public function getCategory(User $user, Model $model, $category)
    {
        return Permissions::has($user, $category . '.' . self::ACTION_ACCESS)
            && $this->isAccessible($model, $user);
    }

    /**
     * @inherit
     */
    public function createCategory(User $user, $category)
    {
        return Permissions::has($user, $category . '.' . self::ACTION_CREATE);
    }

    /**
     * @inherit
     */
    public function updateCategory(User $user, Model $model, $category)
    {
        return Permissions::has($user, $category . '.' . self::ACTION_UPDATE)
            && $this->isOwnedBy($model, $user);
    }

    /**
     * @inherit
     */
    public function deleteCategory(User $user, Model $model, $category)
    {
        return Permissions::has($user, $category . '.' . self::ACTION_DELETE)
            && $this->isOwnedBy($model, $user);
    }

    /**
     * Check if user can list entities
     *
     * @param  User $user
     *
     * @return  bool
     */
    public function list_black_mark_reason(User $user)
    {
        return $this->listCategory($user, 'black-mark-reasons');
    }

    /**
     * Check if user can create an entity
     *
     * @param  User $user
     *
     * @return  bool
     */
    public function create_black_mark_reason(User $user)
    {
        return $this->createCategory($user, 'black-mark-reasons');
    }

    /**
     * Check if user can update the particular entity
     *
     * @param  User $user
     * @param  Handbook $model
     *
     * @return  bool
     */
    public function update_black_mark_reason(User $user, Handbook $model)
    {
        return $this->updateCategory($user, $model, 'black-mark-reasons');
    }

    /**
     * Check if user can delete the particular entity
     *
     * @param  User $user
     * @param  Handbook $model
     *
     * @return  bool
     */
    public function delete_black_mark_reason(User $user, Handbook $model)
    {
        return $this->deleteCategory($user, $model, 'black-mark-reasons');
    }

    /**
     * Check if user can list entities
     *
     * @param  User $user
     *
     * @return  bool
     */
    public function list_skk_reason(User $user)
    {
        return $this->listCategory($user, 'skk-reasons');
    }

    /**
     * Check if user can create an entity
     *
     * @param  User $user
     *
     * @return  bool
     */
    public function create_skk_reason(User $user)
    {
        return $this->createCategory($user, 'skk-reasons');
    }

    /**
     * Check if user can update the particular entity
     *
     * @param  User $user
     * @param  Handbook $model
     *
     * @return  bool
     */
    public function update_skk_reason(User $user, Handbook $model)
    {
        return $this->updateCategory($user, $model, 'skk-reasons');
    }

    /**
     * Check if user can delete the particular entity
     *
     * @param  User $user
     * @param  Handbook $model
     *
     * @return  bool
     */
    public function delete_skk_reason(User $user, Handbook $model)
    {
        return $this->deleteCategory($user, $model, 'skk-reasons');
    }

    /**
     * Check if user can list entities
     *
     * @param  User $user
     *
     * @return  bool
     */
    public function list_reason_impossibility_of_call_processing(User $user)
    {
        return $this->listCategory($user, 'call-unprocessibility-reasons');
    }

    /**
     * Check if user can create an entity
     *
     * @param  User $user
     *
     * @return  bool
     */
    public function create_reason_impossibility_of_call_processing(User $user)
    {
        return $this->createCategory($user, 'call-unprocessibility-reasons');
    }

    /**
     * Check if user can update the particular entity
     *
     * @param  User $user
     * @param  Handbook $model
     *
     * @return  bool
     */
    public function update_reason_impossibility_of_call_processing(User $user, Handbook $model)
    {
        return $this->updateCategory($user, $model, 'call-unprocessibility-reasons');
    }

    /**
     * Check if user can delete the particular entity
     *
     * @param  User $user
     * @param  Handbook $model
     *
     * @return  bool
     */
    public function delete_reason_impossibility_of_call_processing(User $user, Handbook $model)
    {
        return $this->deleteCategory($user, $model, 'call-unprocessibility-reasons');
    }

    /**
     * Check if user can list entities
     *
     * @param  User $user
     *
     * @return  bool
     */
    public function list_city(User $user)
    {
        return $this->listCategory($user, 'cities');
    }

    /**
     * Check if user can create an entity
     *
     * @param  User $user
     *
     * @return  bool
     */
    public function create_city(User $user)
    {
        return $this->createCategory($user, 'cities');
    }

    /**
     * Check if user can update the particular entity
     *
     * @param  User $user
     * @param  Handbook $model
     *
     * @return  bool
     */
    public function update_city(User $user, Handbook $model)
    {
        return $this->updateCategory($user, $model, 'cities');
    }

    /**
     * Check if user can delete the particular entity
     *
     * @param  User $user
     * @param  Handbook $model
     *
     * @return  bool
     */
    public function delete_city(User $user, Handbook $model)
    {
        return $this->deleteCategory($user, $model, 'cities');
    }

    /**
     * Check if user can list entities
     *
     * @param  User $user
     *
     * @return  bool
     */
    public function list_currency(User $user)
    {
        return $this->listCategory($user, 'currencies');
    }

    /**
     * Check if user can create an entity
     *
     * @param  User $user
     *
     * @return  bool
     */
    public function create_currency(User $user)
    {
        return $this->createCategory($user, 'currencies');
    }

    /**
     * Check if user can update the particular entity
     *
     * @param  User $user
     * @param  Handbook $model
     *
     * @return  bool
     */
    public function update_currency(User $user, Handbook $model)
    {
        return $this->updateCategory($user, $model, 'currencies');
    }

    /**
     * Check if user can delete the particular entity
     *
     * @param  User $user
     * @param  Handbook $model
     *
     * @return  bool
     */
    public function delete_currency(User $user, Handbook $model)
    {
        return $this->deleteCategory($user, $model, 'currencies');
    }

    /**
     * Check if user can list entities
     *
     * @param  User $user
     *
     * @return  bool
     */
    public function list_reason_refusing_treatment(User $user)
    {
        return $this->listCategory($user, 'reason-refusing-treatments');
    }

    /**
     * Check if user can create an entity
     *
     * @param  User $user
     *
     * @return  bool
     */
    public function create_reason_refusing_treatment(User $user)
    {
        return $this->createCategory($user, 'reason-refusing-treatments');
    }

    /**
     * Check if user can update the particular entity
     *
     * @param  User $user
     * @param  Handbook $model
     *
     * @return  bool
     */
    public function update_reason_refusing_treatment(User $user, Handbook $model)
    {
        return $this->updateCategory($user, $model, 'reason-refusing-treatments');
    }

    /**
     * Check if user can delete the particular entity
     *
     * @param  User $user
     * @param  Handbook $model
     *
     * @return  bool
     */
    public function delete_reason_refusing_treatment(User $user, Handbook $model)
    {
        return $this->deleteCategory($user, $model, 'reason-refusing-treatments');
    }

    /**
     * Check if user can list entities
     *
     * @param  User $user
     *
     * @return  bool
     */
    public function list_wait_list_record_cancel_reason(User $user)
    {
        return $this->listCategory($user, 'wait-list-record-cancel-reasons');
    }

    /**
     * Check if user can create an entity
     *
     * @param  User $user
     *
     * @return  bool
     */
    public function create_wait_list_record_cancel_reason(User $user)
    {
        return $this->createCategory($user, 'wait-list-record-cancel-reasons');
    }

    /**
     * Check if user can update the particular entity
     *
     * @param  User $user
     * @param  Handbook $model
     *
     * @return  bool
     */
    public function update_wait_list_record_cancel_reason(User $user, Handbook $model)
    {
        return $this->updateCategory($user, $model, 'wait-list-record-cancel-reasons');
    }

    /**
     * Check if user can delete the particular entity
     *
     * @param  User $user
     * @param  Handbook $model
     *
     * @return  bool
     */
    public function delete_wait_list_record_cancel_reason(User $user, Handbook $model)
    {
        return $this->deleteCategory($user, $model, 'wait-list-record-cancel-reasons');
    }

    /**
     * Check if user can list entities
     *
     * @param  User $user
     *
     * @return  bool
     */
    public function list_media_type(User $user)
    {
        return $this->listCategory($user, 'media-types');
    }

    /**
     * Check if user can create an entity
     *
     * @param  User $user
     *
     * @return  bool
     */
    public function create_media_type(User $user)
    {
        return $this->createCategory($user, 'media-types');
    }

    /**
     * Check if user can update the particular entity
     *
     * @param  User $user
     * @param  Handbook $model
     *
     * @return  bool
     */
    public function update_media_type(User $user, Handbook $model)
    {
        return $this->updateCategory($user, $model, 'media-types');
    }

    /**
     * Check if user can delete the particular entity
     *
     * @param  User $user
     * @param  Handbook $model
     *
     * @return  bool
     */
    public function delete_media_type(User $user, Handbook $model)
    {
        return $this->deleteCategory($user, $model, 'media-types');
    }
}
