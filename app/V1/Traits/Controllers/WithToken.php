<?php

namespace App\V1\Traits\Controllers;

use App\V1\Contracts\Services\EhealthService;
use Illuminate\Http\Request;

trait WithToken
{
    /**
     * Do something with user token
     *
     * @param EhealthService $ehealth
     * @param Request $request
     * @param Closure $callback
     *
     * @return mixed
     */
    protected function withToken($ehealth, $request, $callback)
    {
        $user = $request->user();
        $ehealth_user = $user->ehealth_user;

        if ($ehealth_user === null) {
            return $this->respondError('Not authorized', [], 403);
        }

        try {
            $token = $ehealth->getToken($ehealth_user);
        } catch (Exception $e) {
            return $this->respondError('Not authorized', [], 403);
        }

        try {
            return $callback($token);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage(), [], $e->getCode());
        }
    }
}
