<?php

namespace App\V1\Http\Controllers;

use App\V1\Facades\Permissions;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\ValidationException;
use App\V1\Http\Resources\AccountResource;
use App\V1\Http\Requests\LoginRequest;
use App\V1\Http\Requests\Patient\LoginRequest as PatientLoginRequest;
use App\V1\Http\Requests\Patient\StaffLoginRequest;
use App\V1\Contracts\Repositories\Patient\UserRepository as PatientUserRepository;
use App\V1\Http\Requests\Patient\ChangePasswordRequest as PatientChangePasswordRequest;
use App\V1\Http\Requests\Patient\RecoverPasswordRequest as PatientRecoverPasswordRequest;
use App\V1\Http\Resources\Patient\UserResource as PatientUserResource;
use Auth;
use App\V1\Traits\PhoneNumber;

class AuthController extends ApiController
{
    use ThrottlesLogins;
    use PhoneNumber;

    private $maxAttempts = 5;

    /**
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        $credentials = $request->credentials();

        if($token = Auth::attempt($credentials)) {
            $this->clearLoginAttempts($request);
            return $this->authenticated($token);
        }else {
            $this->incrementLoginAttempts($request);
        }

        return $this->sendFailedLoginResponse($request);

    }

    /**
     * @param PatientLoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function patientLogin(PatientLoginRequest $request)
    {
        $credentials = $request->credentials();
        $credentials['phone'] = $this->normalizePhoneNumber($credentials['phone']);

        Auth::shouldUse('api_patient');

        if (Auth::validate($credentials)) {
            $user = Auth::getLastAttempted();
            return new PatientUserResource($user);
        }

        throw ValidationException::withMessages([
            'password' => trans('auth.failed'),
        ]);
    }

    /**
     * @param PatientChangePasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function patientChangePassword(PatientChangePasswordRequest $request)
    {
        $credentials = $request->credentials();

        Auth::shouldUse('api_patient');

        if (Auth::validate($credentials)) {
            $user = Auth::getLastAttempted();
            $user->password = $request->newPassword();
            $user->save();
            return $this->respondSuccess();
        }

        throw ValidationException::withMessages([
            'password_old' => trans('auth.failed'),
        ]);
    }

    /**
     * Login into a certain patient account
     *
     * @param StaffLoginRequest $request
     * @param PatientUserRepository $users
     *
     * @return PatientUserResource
     */
    public function staffLogin(StaffLoginRequest $request, PatientUserRepository $users)
    {
        $credentials = $request->credentials();

        if (Auth::validate($credentials)) {
            $staff = Auth::getLastAttempted();

            if ($staff->isEmployee() &&
                $staff->isWorking() &&
                Permissions::has($staff, 'patient-cabinet.staff-access'))
            {
                $user = $users->getByPhoneNumber($request->phoneNumber());

                if ($user !== null) {
                    return new PatientUserResource($user);
                }
            }
        }

        throw ValidationException::withMessages([
            'password' => trans('auth.failed'),
        ]);
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'login';
    }

    /**
     * Verify patient registered
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patientVerifyPhone(Request $request)
    {
        $credentials = $request->only(['phone']);

        Auth::shouldUse('api_patient');

        $userProvider = Auth::getProvider();
        $user = $userProvider->retrieveByCredentials($credentials);

        if ($user !== null) {
            return $this->respondSuccess([
                'id' => $user->id,
            ]);
        }

        throw ValidationException::withMessages([
            'phone' => trans('auth.phone_not_registered'),
        ]);
    }

    /**
     * Verify patient registered
     *
     * @param PatientRecoverPasswordRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patientRecoverPassword(PatientRecoverPasswordRequest $request)
    {
        $credentials = $request->credentials();

        Auth::shouldUse('api_patient');

        $userProvider = Auth::getProvider();
        $user = $userProvider->retrieveByCredentials($credentials);

        if ($user !== null) {
            $user->password = $request->newPassword();
            $user->save();
            return new PatientUserResource($user);
        }

        return $this->respondError('Can not find user');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondSuccess();
    }

    /**
     * Log out an authenticated user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Auth::logout();

        return $this->respondSuccess();
    }

    /**
     * Respond that user was successfully authenticated
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function authenticated($token)
    {
        $user = Auth::user();
        $user->loadMissing('userable');

        if ($user->isEmployee() && !$user->isWorking()) {
            Auth::logout();
            throw ValidationException::withMessages([
                $this->username() => trans('auth.failed'),
            ]);
        }
        return $this->respondSuccess(new AccountResource($user))
                    ->header('Authorization', 'Bearer ' . $token);
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => trans('auth.failed'),
        ]);
    }

    /**
     * Redirect the user after determining they are locked out.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        throw ValidationException::withMessages([
            $this->username() => [Lang::transChoice('auth.throttle',$seconds, ['seconds' => $seconds])],
        ])->status(429);
    }
}
