<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'ru',

    'locale_config' => [
        'ua' => [
            'name' => 'Українська',
            'short_name' => 'Укр',
            'suffix' => null,
        ],
        'ru' => [
            'name' => 'Русский',
            'short_name' => 'Рус',
            'suffix' => 'lc1',
        ],
        /* 'en' => [
            'name' => 'English',
            'short_name' => 'En',
            'suffix' => 'lc2',
        ], */
        'sk' => [
            'name' => 'Slovenský',
            'short_name' => 'Sk',
            'suffix' => 'lc3',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * Package Service Providers...
         */
        Tymon\JWTAuth\Providers\LaravelServiceProvider::class,

        /*
         * Application Service Providers...
         */
        App\V1\Providers\AppServiceProvider::class,
        App\V1\Providers\AuthServiceProvider::class,
        App\V1\Providers\BroadcastServiceProvider::class,
        App\V1\Providers\EventServiceProvider::class,
        App\V1\Providers\RouteServiceProvider::class,
        App\V1\Providers\UserServiceProvider::class,
        App\V1\Providers\HandbookServiceProvider::class,
        App\V1\Providers\ClinicServiceProvider::class,
        App\V1\Providers\EmployeeServiceProvider::class,
        App\V1\Providers\PriceAgreementActServiceProvider::class,
        App\V1\Providers\Employee\ClinicServiceProvider::class,
        App\V1\Providers\Employee\EmployeePositionServiceProvider::class,
        App\V1\Providers\SpecializationServiceProvider::class,
        App\V1\Providers\CallRequestServiceProvider::class,
        App\V1\Providers\PatientServiceProvider::class,
        App\V1\Providers\Ehealth\PatientServiceProvider::class,
        App\V1\Providers\Ehealth\Patient\AuthenticationServiceProvider::class,
        App\V1\Providers\CallRequest\PurposeServiceProvider::class,
        App\V1\Providers\AppointmentServiceProvider::class,
        App\V1\Providers\SessionLogServiceProvider::class,
        App\V1\Providers\TreatmentCourseServiceProvider::class,
        App\V1\Providers\Appointment\StatusServiceProvider::class,
        App\V1\Providers\Call\ResultServiceProvider::class,
        App\V1\Providers\CallServiceProvider::class,
        App\V1\Providers\PermissionServiceProvider::class,
        App\V1\Providers\PersonalTaskServiceProvider::class,
        App\V1\Providers\DaySheetServiceProvider::class,
        App\V1\Providers\Patient\CardServiceProvider::class,
        App\V1\Providers\VoipServiceProvider::class,
        App\V1\Providers\Call\DeleteReasonServiceProvider::class,
        App\V1\Providers\Appointment\DeleteReasonServiceProvider::class,
        App\V1\Providers\DiagnosisServiceProvider::class,
        App\V1\Providers\ServiceServiceProvider::class,
        App\V1\Providers\AnalysisServiceProvider::class,
        App\V1\Providers\PriceServiceProvider::class,
        App\V1\Providers\Service\PaymentDestinationServiceProvider::class,
        App\V1\Providers\Analysis\LaboratoryServiceProvider::class,
        App\V1\Providers\Analysis\ResultServiceProvider::class,
        App\V1\Providers\Call\CallLogServiceProvider::class,
        App\V1\Providers\Call\ProcessLogServiceProvider::class,
        App\V1\Providers\Appointment\LimitationServiceProvider::class,
        App\V1\Providers\RoleServiceProvider::class,
        App\V1\Providers\WorkspaceServiceProvider::class,
        App\V1\Providers\Workspace\ClinicServiceProvider::class,
        App\V1\Providers\Appointment\Status\ReasonServiceProvider::class,
        App\V1\Providers\Specialization\ClinicServiceProvider::class,
        App\V1\Providers\FileAttachmentServiceProvider::class,
        App\V1\Providers\Patient\InformationSourceServiceProvider::class,
        App\V1\Providers\Patient\SignalRecordServiceProvider::class,
        App\V1\Providers\Patient\Card\RecordTemplateServiceProvider::class,
        App\V1\Providers\Patient\Card\ProtocolTemplateServiceProvider::class,
        App\V1\Providers\PatientDocumentServiceProvider::class,
        App\V1\Providers\Ehealth\Patient\DocumentServiceProvider::class,
        App\V1\Providers\DiscountCardType\NumberingKindServiceProvider::class,
        App\V1\Providers\DiscountCardType\IconServiceProvider::class,
        App\V1\Providers\DiscountCardTypeServiceProvider::class,
        App\V1\Providers\ActionLogServiceProvider::class,
        App\V1\Providers\MedicineStoreServiceProvider::class,
        App\V1\Providers\MedicineFirmServiceProvider::class,
        App\V1\Providers\MedicineServiceProvider::class,
        App\V1\Providers\OneSServiceProvider::class,
        App\V1\Providers\PaymentMethodServiceProvider::class,
        App\V1\Providers\Patient\IssuedDiscountCardServiceProvider::class,
        App\V1\Providers\Patient\AssignedMedicineServiceProvider::class,
        App\V1\Providers\Patient\Card\AssignmentServiceProvider::class,
        App\V1\Providers\Patient\Card\TreatmentAssignmentServiceProvider::class,
        App\V1\Providers\Patient\AssignedServiceServiceProvider::class,
        App\V1\Providers\Patient\UploadServiceProvider::class,
        App\V1\Providers\CashierSessionLogServiceProvider::class,
        App\V1\Providers\PaymentServiceProvider::class,
        App\V1\Providers\Employee\Cashbox\CashTransferServiceProvider::class,
        App\V1\Providers\Patient\IssuedMedicineServiceProvider::class,
        App\V1\Providers\Patient\RegistrationServiceProvider::class,
        App\V1\Providers\Patient\UserServiceProvider::class,
        App\V1\Providers\Appointment\ServiceServiceProvider::class,
        App\V1\Providers\Appointment\NoteServiceProvider::class,
        App\V1\Providers\SiteEnquiryServiceProvider::class,
        App\V1\Providers\Reports\CallCenterServiceProvider::class,
        App\V1\Providers\Reports\FinanceServiceProvider::class,
        App\V1\Providers\Reports\MarketingServiceProvider::class,
        App\V1\Providers\Employee\OperatorBonusServiceProvider::class,
        App\V1\Providers\Employee\DoctorIncomePlanServiceProvider::class,
        App\V1\Providers\Employee\DocumentServiceProvider::class,
        App\V1\Providers\Employee\EducationServiceProvider::class,
        App\V1\Providers\Employee\QualificationServiceProvider::class,
        App\V1\Providers\Employee\SpecialityServiceProvider::class,
        App\V1\Providers\Employee\ScienceDegreeServiceProvider::class,
        App\V1\Providers\Clinic\BonusNormServiceProvider::class,
        App\V1\Providers\Clinic\GroupServiceProvider::class,
        App\V1\Providers\InsuranceCompanyServiceProvider::class,
        App\V1\Providers\InsuranceCompany\ClinicServiceProvider::class,
        App\V1\Providers\Patient\InsurancePolicyServiceProvider::class,
        App\V1\Providers\NotificationServiceProvider::class,
        App\V1\Providers\Notification\ChannelServiceProvider::class,
        App\V1\Providers\Notification\MailingProviderServiceProvider::class,
        App\V1\Providers\Notification\TemplateServiceProvider::class,
        App\V1\Providers\Notification\MailingTemplateServiceProvider::class,
        App\V1\Providers\LaboratoriesScheduleServiceProvider::class,
        App\V1\Providers\CountryServiceProvider::class,
        App\V1\Providers\InsuranceCompany\ActServiceProvider::class,
        App\V1\Providers\DaySheet\TimeBlockReasonServiceProvider::class,
        App\V1\Providers\ElasticsearhServiceProvider::class,
        App\V1\Providers\Patient\PrepaymentServiceProvider::class,
        App\V1\Providers\EmailLogServiceProvider::class,
        App\V1\Providers\EhealthServiceProvider::class,
        App\V1\Providers\Ehealth\CareEpisodeServiceProvider::class,
        App\V1\Providers\Ehealth\EncounterServiceProvider::class,
        App\V1\Providers\Ehealth\Encounter\ConditionServiceProvider::class,
        App\V1\Providers\Ehealth\Encounter\DiagnosticReportServiceProvider::class,
        App\V1\Providers\Ehealth\Encounter\ProcedureServiceProvider::class,
        App\V1\Providers\Ehealth\PackageRecordServiceProvider::class,
        App\V1\Providers\MspServiceProvider::class,
        App\V1\Providers\Msp\ContractServiceProvider::class,
        App\V1\Providers\LegalEntityServiceProvider::class,
        App\V1\Providers\LegalEntity\ClinicServiceProvider::class,
        App\V1\Providers\SmsAppointmentReminderServiceProvider::class,
        App\V1\Providers\WaitListRecordServiceProvider::class,
        App\V1\Providers\Analysis\TemplateServiceProvider::class,
        App\V1\Providers\Clinic\MoneyRecieverServiceProvider::class,
        App\V1\Providers\PriceServiceProvider::class,
        App\V1\Providers\VideoChatServiceProvider::class,
        App\V1\Providers\CacheValidityServiceProvider::class,
        App\V1\Providers\Patient\ClinicRouteServiceProvider::class,
        App\V1\Providers\EsputnikServiceProvider::class,
        App\V1\Providers\MailingServiceServiceProvider::class,
        App\V1\Providers\Clinic\ServiceTypeServiceProvider::class,
        App\V1\Providers\Employee\ServiceTypeServiceProvider::class,
        App\V1\Providers\DepartmentServiceProvider::class,
        App\V1\Providers\Department\RoomServiceProvider::class,
        App\V1\Providers\Department\Room\OccupationServiceProvider::class,
        App\V1\Providers\ExchangeRateServiceProvider::class,
        App\V1\Providers\Appointment\Status\DelayReasonServiceProvider::class,
        App\V1\Providers\Appointment\DelayServiceProvider::class,
        App\V1\Providers\LockLogServiceProvider::class,
        App\V1\Providers\ReasonUnblockServiceProvider::class,
        App\V1\Providers\Patient\AppointmentDocumentProvider::class,
        App\V1\Providers\HorizonServiceProvider::class,
        App\V1\Providers\PriceAgreementAct\PriceServiceProvider::class,
        App\V1\Providers\ChatServiceProvider::class,
        App\V1\Providers\Analysis\CandidateServiceProvider::class,
        App\V1\Providers\Analysis\Laboratory\ContainerServiceProvider::class,
        App\V1\Providers\Appointment\AmbulanceCallServiceProvider::class,
        App\V1\Providers\CheckboxServiceProvider::class,
        App\V1\Providers\Checkbox\ShiftServiceProvider::class,
        App\V1\Providers\MoneyRecieverCashboxServiceProvider::class,
        App\V1\Providers\CashierCheckboxCashboxServiceProvider::class,
        App\V1\Providers\Checkbox\CheckServiceProvider::class,
        App\V1\Providers\GitlabServiceProvider::class,
        App\V1\Providers\Apteka24ServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,
        'JWTAuth' => Tymon\JWTAuth\Facades\JWTAuth::class,
        'JWTFactory' => Tymon\JWTAuth\Facades\JWTFactory::class,
        'Handbook' => App\V1\Facades\Handbook::class,
        'Permissions' => App\V1\Facades\Permissions::class,
        'Voip' => App\V1\Facades\Voip::class,
        'VoipMessageQueue' => App\V1\Facades\Voip\MessageQueue::class,
        'OneSTransaction' => App\V1\Facades\OneS\Transaction::class,
        'OneSMedicineIssue' => App\V1\Facades\OneS\MedicineIssue::class,
        'OnlinePaymentService' => App\V1\Facades\OnlinePaymentService::class,
        'Messenger' => App\V1\Facades\Messenger::class,
        'MailingMessenger' => App\V1\Facades\MailingMessenger::class,
        'ElasticsearchClient' => App\V1\Facades\ElasticsearchClient::class,

    ],

];
