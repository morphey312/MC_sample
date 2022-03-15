<?php

namespace App\V1\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Auth;

class ApiController extends Controller
{
    /**
     * Send successful response
     *
     * @param array $data
     * @param int $code
     * @param array $headers
     *
     * @return JsonResponse
     */
    protected function respondSuccess($data = [], $code = 200, $headers = [])
    {
        return new JsonResponse($data, $code, $headers);
    }

    /**
     * Send unsuccessful response
     *
     * @param string $error
     * @param array $data
     * @param int $code
     * @param array $headers
     *
     * @return JsonResponse
     */
    protected function respondError($error, $data = [], $code = 400, $headers = [])
    {
        return new JsonResponse([
            'error' => $error,
        ] + $data, $code, $headers);
    }

    /**
     * Respont that object was successfully created
     *
     * @param mixed $object
     *
     * @return JsonResponse
     */
    protected function respondCreated($object)
    {
        return $this->respondSuccess($object, 201);
    }

    /**
     * Respont that object was successfully updated
     *
     * @param mixed $object
     *
     * @return JsonResponse
     */
    protected function respondUpdated($object)
    {
        return $this->respondSuccess($object);
    }

    /**
     * Respont that object was successfully deleted
     *
     * @param array $data
     *
     * @return JsonResponse
     */
    protected function respondDeleted($data = [])
    {
        return $this->respondSuccess($data);
    }

    /**
     * Dispatch deferred response
     *
     * @param Request $request
     * @param string $jobClass
     *
     * @return JsonResponse
     */
    protected function respondPromise(Request $request, $jobClass)
    {
        $uid = uniqid();
        $data = $request->all();
        $user = $request->user();
        $jobClass::dispatch($uid, $user->id, $data)->onQueue('promises');
        return $this->respondSuccess([
            'promiseId' => $uid,
        ]);
    }
}
