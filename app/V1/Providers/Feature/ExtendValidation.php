<?php

namespace App\V1\Providers\Feature;

use Illuminate\Support\Facades\Validator;
use App\V1\Contracts\Repositories\BaseRepository;
use App\V1\Contracts\Repositories\DaySheetRepository;
use App\V1\Contracts\Repositories\AppointmentRepository;
use InvalidArgumentException;
use ReflectionClass;
use App;
use Illuminate\Support\Arr;
use App\V1\Models\Appointment;
use Illuminate\Support\Facades\DB;

class ExtendValidation
{
    const PHONE_NUMBER_RE = '/^[+]?([0-9]{10,14}|[0-9]{3,5})$/';
    const PASSWORD_MIN_LEN = 8;
    const DIGITS_RE = '/[0-9]/';
    const UPPERCASE_RE = '/[A-Z]/';
    const LOWERCASE_RE = '/[a-z]/';
    const REPOSITORY_NAMESPACE = 'App\V1\Contracts\Repositories';

    /**
     * Boot feature
     *
     * @param \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application $app
     */
    public static function boot($app)
    {
        Validator::extend('phoneNumber', function ($attribute, $value, $parameters, $validator) {
            return preg_match(self::PHONE_NUMBER_RE, $value);
        }, 'Укажите номер телефона в корректном формате');

        Validator::extend('password', function ($attribute, $value, $parameters, $validator) {
            return strlen($value) >= self::PASSWORD_MIN_LEN
                && preg_match(self::DIGITS_RE, $value)
                && preg_match(self::UPPERCASE_RE, $value)
                && preg_match(self::LOWERCASE_RE, $value);
        }, 'Данный пароль недостаточно надежен');

        Validator::extend('accessible', function ($attribute, $value, $parameters, $validator) {
            if (count($parameters) < 1) {
                throw new InvalidArgumentException('Validation rule accessible requires at least one parameter');
            }

            $class = new ReflectionClass(self::REPOSITORY_NAMESPACE . '\\' . $parameters[0]);
            if (!$class->implementsInterface(BaseRepository::class)) {
                throw new InvalidArgumentException('Validation rule accessible can not be applied to ' . $class->getName());
            }

            $repo = App::make($class->getName());
            $count = $repo->countById($value);
            $expected = is_array($value) ? count(array_unique($value)) : 1;

            return $count >= $expected;
        }, 'Нет доступа к выбранным записям');

        Validator::extend('accessibleKeys', function ($attribute, $value, $parameters, $validator) {
            if (count($parameters) < 1) {
                throw new InvalidArgumentException('Validation rule accessible requires at least one parameter');
            }

            $class = new ReflectionClass(self::REPOSITORY_NAMESPACE . '\\' . $parameters[0]);
            if (!$class->implementsInterface(BaseRepository::class)) {
                throw new InvalidArgumentException('Validation rule accessible can not be applied to ' . $class->getName());
            }

            $repo = App::make($class->getName());
            $value = array_keys($value);
            $count = $repo->countById($value);
            $expected = is_array($value) ? count(array_unique($value)) : 1;

            return $count >= $expected;
        }, 'Нет доступа к выбранным записям');

        Validator::extend('uniqueAccessible', function($attribute, $value, $parameters, $validator) {
            if (count($parameters) < 2) {
                throw new InvalidArgumentException('Validation rule uniqueAccessible requires at least two parameters');
            }

            $class = new ReflectionClass(self::REPOSITORY_NAMESPACE . '\\' . $parameters[0]);
            if (!$class->implementsInterface(BaseRepository::class)) {
                throw new InvalidArgumentException('Validation rule accessible can not be applied to ' . $class->getName());
            }

            $repo = App::make($class->getName());
            $values = [[$parameters[1], '=', $value]];

            if (count($parameters) >= 3 && !empty($parameters[2]) && $parameters[2] !== 'NULL') {
                $idKey = count($parameters) >= 4 ? $parameters[3] : 'id';
                $values[] = [$idKey, '!=', $parameters[2]];
            }

            $filters = [];
            foreach (array_chunk(array_slice($parameters, 4), 2) as $couple) {
                if (count($couple) === 2) {
                    $filters[$couple[0]] = $couple[1];
                }
            }

            return $repo->countByValues($values, $repo->filter($filters)) === 0;
        }, 'Данная запись уже существует');

        Validator::extend('hasRelation', function($attribute, $value, $parameters, $validator) {
            if (count($parameters) < 3) {
                throw new InvalidArgumentException('Validation rule accessible requires three parameters');
            }

            $class = new ReflectionClass(self::REPOSITORY_NAMESPACE . '\\' . $parameters[0]);
            if (!$class->implementsInterface(BaseRepository::class)) {
                throw new InvalidArgumentException('Validation rule accessible can not be applied to ' . $class->getName());
            }

            $repo = App::make($class->getName());
            $model = $repo->find($value);
            $count = $model->{$parameters[1]}()->where('id', '=', $parameters[2])->count();

            return $count > 0;
        });

        Validator::extend('constraintPrice', function($attribute, $value, $parameters, $validator) {
            if (count($parameters) !== 3) {
                throw new InvalidArgumentException('Validation rule constraintPrice requires three parameters');
            }

            if (empty($parameters[1])) {
                return true;
            }

            $class = new ReflectionClass(self::REPOSITORY_NAMESPACE . '\\' . $parameters[0]);
            if (!$class->implementsInterface(BaseRepository::class)) {
                throw new InvalidArgumentException('Validation rule accessible can not be applied to ' . $class->getName());
            }

            $repo = App::make($class->getName());
            $service = $repo->find($parameters[1], false);

            if ($service === null) {
                return true;
            }

            return array_diff(
                $service->actual_prices->pluck('clinics')->flatten()->pluck('id')->toArray(),
                Arr::pluck((array) $value, $parameters[2])
            ) === [];
        });

        Validator::extend('appointmentPeriod', function($attribute, $value, $parameters, $validator) {
            $filter = [
                'date' => $parameters[1],
                'start' => static::getTimeString($value),
                'end' => static::getTimeString($parameters[2]),
                'clinic' => $parameters[3],
                'doctor_id' =>  $parameters[4],
                'doctor_type' => $parameters[5],
                'workspace_id' => $parameters[6],
            ];

            $repo = App::make(AppointmentRepository::class);
            return !$repo->crossAppointmentExists($parameters[0], $filter);
        });

        Validator::extend('doctorHasDaySheet', function($attribute, $value, $parameters, $validator) {
            $filter = [
                'date' => $parameters[0],
                'start' => static::getTimeString($value),
                'end' => static::getTimeString($parameters[1]),
                'clinic' => $parameters[2],
                'doctor_id' =>  $parameters[3],
                'doctor_type' => $parameters[4],
                'workspace_id' => $parameters[5],
                'specialization_id' => $parameters[6],
            ];

            $repo = App::make(DaySheetRepository::class);
            return $repo->daysheetExists($filter);
        });

        Validator::extend('daySheetNotLocked', function($attribute, $value, $parameters, $validator) {
            $filter = [
                'date' => $parameters[0],
                'start' => static::getTimeString($value),
                'end' => static::getTimeString($parameters[1]),
                'clinic' => $parameters[2],
                'doctor_id' =>  $parameters[3],
                'doctor_type' => $parameters[4],
                'workspace_id' => $parameters[5],
            ];

            $repo = App::make(DaySheetRepository::class);
            return !$repo->daysheetIsLocked($filter, $parameters[6]);
        });

        Validator::extend('legalEntityInAppointment', function($attribute, $value, $parameters, $validator) {
            $filter = [
                'match_legal_entity' => $value,
                'patient' => $parameters[0],
            ];

            $class = new ReflectionClass(self::REPOSITORY_NAMESPACE . '\\AppointmentRepository');
            if (!$class->implementsInterface(BaseRepository::class)) {
                throw new InvalidArgumentException('Validation rule legalEntityInAppointment can not be applied to ' . $class->getName());
            }

            $repo = App::make($class->getName());
            $count = $repo->count($repo->filter($filter));
            return $count === 0;
        }, 'Юр. лицо присутствует в записи');

        Validator::extend('patientOnceAppointment', function($attribute, $value, $parameters, $validator) {
            $filters = [
                'patient' => $value,
                'specialization' => $parameters[0],
                'status' => Appointment::getStatusSignedUp(),
                'is_deleted' => false,
            ];

            $class = new ReflectionClass(self::REPOSITORY_NAMESPACE . '\\AppointmentRepository');
            if (!$class->implementsInterface(BaseRepository::class)) {
                throw new InvalidArgumentException('Validation rule patientOnceAppointment can not be applied to ' . $class->getName());
            }
            $repo = App::make($class->getName());
            $count = $repo->count($repo->filter($filters));
            return $count === 0;
        });

        Validator::extend('ClinicsPriority', function($attribute, $value, $parameters, $validator) {
            if ($value['priority'] === null) {
                return true;
            }

            $count = DB::table('laboratories_clinics')
                ->where('clinic_id', '=', $value['clinic_id'])
                ->where('priority', '=', $value['priority'])
                ->where('laboratory_id', '!=', $parameters[0])
                ->count();

            return $count === 0;
        });

        Validator::extend('lockNotDublicated', function($attribute, $value, $parameters, $validator) {
            $repo = App::make(DaySheetRepository::class);
            return !$repo->lockNotDublicated($value);
        });
    }

    /**
     * Get hour minutes seconds string
     *
     * @param string $value
     * @param int $length
     *
     * @return string
     */
    protected static function getTimeString($value, $length = 5)
    {
        return (strlen($value) == $length) ? $value.':00' : $value;
    }

}
