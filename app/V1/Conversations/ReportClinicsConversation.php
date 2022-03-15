<?php

namespace App\V1\Conversations;

use App\V1\Models\Clinic;
use App\V1\Models\Notification\Template;
use App\V1\Models\Specialization;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\Drivers\Telegram\TelegramDriver;
use Carbon\Carbon;
use App\V1\Repositories\ElasticReport;
use BotMan\Drivers\Telegram\Extensions\Keyboard;
use BotMan\Drivers\Telegram\Extensions\KeyboardButton;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Log;
use Validator;

class ReportClinicsConversation extends DefaultConversation
{
    const AUTO_MODE = 'auto';
    const MANUAL_MODE = 'manual';
    const STORAGE_TIME = 1800; // 30 mins

    protected $key;
    protected $clinics;

    protected $reportTemplate;

    const periods = [
        'day' => 'День',
        'week' => 'Неделя',
        'month' => 'Месяц',
        'quarter' => 'Квартал',
        'forecast_month' => 'Прогноз на месяц',
        'compare_period' => 'Сравнение',
        'year' => 'Год',
        'manual' => 'Задать Вручную',
    ];

    /**
     * Start the conversation
     */
    public function run()
    {
        $this->removeLatestInlineKeyboard();
        $employee = $this->getEmployee($this->getBot()->getUser()->getId());
        
        if ($employee) {
            $clinicIds = $employee->employee_clinics->map(function ($employeeClinic) {
                return $employeeClinic->clinic_id;
            })->toArray();
            $this->key = 'telegram:storage:' . $employee->id;
            $this->clinics = $this->getClinics($clinicIds);
            $this->askManualSelect($clinicIds);
        }
    }

    public function sendReport($ids, $onlyAmount = false,
                               $withoutAmount = false, $date_start = null, $date_end = null, $type = null)
    {
        $reportTemplate = Template::where('scenario', '=', Template::SCENARIO_TELEGRAM_CHAT_BOT_NOTIFICATION)
            ->with('setting_clinics.specializations')
            ->first();

        $clinics = Clinic::select('id', 'name', 'short_name', 'currency')
            ->with(['specializations' => function ($query) {
                $query->where('service_group', '=', Specialization::SERVICE_GROUP_SURGERY);
            }])
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get();

        $report = new ElasticReport();

        $arrSummary = [
            'payments' => 0,
            'calls' => 0,
            'appointments' => 0,
            'visits' => 0,
        ];

        $summaryByClinic = [];

        $summary = ['uah' => $arrSummary, 'eur' => $arrSummary];

        $showUah = false;

        foreach ($clinics as $clinic) {
            $filters = [
                'clinics' => [$clinic->id],
                'date_end' => $date_end,
                'date_start' => $date_start,
                'is_deleted' => false,
            ];

            $payments = $report->getClinicPayments($filters,
                $clinic->currency);
            $appointments = $report->getClinicAppointments($filters);
            $visits = $report->getClinicVisits($filters);
            $calls = $report->getCallCenterCalls($filters);

            if ($clinic->currency == 'uah') {
                $showUah = true;
            }

            $summaryByClinic[$clinic->short_name]['payments'] = $clinicPayments =
                $this->getData($payments, $clinic);

            $summaryByClinic[$clinic->short_name]['appointments'] = $clinicAppointments =
                $this->getData($appointments, $clinic);

            $summaryByClinic[$clinic->short_name]['calls'] = $clinicsCalls =
                $this->getData($calls, $clinic);

            $summaryByClinic[$clinic->short_name]['visits'] = $clinicsVisits =
                $this->getData($visits, $clinic);

            $summary[$clinic->currency]['payments'] += $clinicPayments['payed_amount']['value'] ?? 0;
            $summary[$clinic->currency]['appointments'] += $clinicAppointments['calls']['value'] ?? 0;
            $summary[$clinic->currency]['calls'] += $clinicsCalls['calls']['value'] ?? 0;
            $summary[$clinic->currency]['visits'] += $clinicsVisits['calls']['value'] ?? 0;

            $clinicString = $clinic->short_name . ' ' . round($clinicPayments['payed_amount']['value'] ?? 0, 2) . ' ' . ($clinicsCalls['calls']['value'] ?? '0'). ' ' . ($clinicAppointments['calls']['value'] ?? '0') . ' ' . ($clinicsVisits['calls']['value'] ?? '0');

            $settings_clinic = $reportTemplate->setting_clinics->firstWhere('clinic_id', $clinic->id);

            if ($type === 'forecast_month') {
                $this->say($this->calculateMonthForecast(
                    $clinic,
                    $clinicPayments,
                    $clinicAppointments,
                    $clinicsCalls,
                    $clinicsVisits
                ));
            }

            if ($settings_clinic) {
                foreach ($settings_clinic->specializations as $clinic_specialization) {
                    $specializationFilter = [
                        'clinics' => [$clinic->id],
                        'date_end' => $date_end,
                        'date_start' => $date_start,
                        'specializations' => [$clinic_specialization->specialization_id],
                        'is_deleted' => false,
                    ];

                    $specializationPayments = $report->getSurgeryPayments($specializationFilter, $clinic->currency);

                    $specializationAmount =
                        array_first($specializationPayments['aggr_group']) ?
                            array_first($specializationPayments['aggr_group'])['payed_amount']['value'] : 0;

                    $specializationName = !empty($clinic_specialization->custom_name) ? $clinic_specialization->custom_name : $clinic_specialization->specialization->name;

                    $clinicString .= PHP_EOL . '   ' . $specializationName . ' ' . $specializationAmount;
                }

            }

            if ($type === null) {
                if (!$onlyAmount) {
                    $this->say($clinicString);
                }
            }

        }


        if ($withoutAmount) {
            if ($showUah) {
                $this->say($this->amountString('UAH', $summary));
            }
        }

        return $summaryByClinic;
    }

    /**
     * @param $clinic
     * @param $clinicPayments
     * @param $clinicAppointments
     * @param $clinicsCalls
     * @param $clinicsVisits
     * @return string
     */
    private function calculateMonthForecast(
        $clinic,
        $clinicPayments,
        $clinicAppointments,
        $clinicsCalls,
        $clinicsVisits
    ): string
    {
        $temp = [];
        $daysInMonth = Carbon::now()->daysInMonth;
        $daysPassed = Carbon::now()->format('d');

        $temp['payments'] = $clinicPayments['payed_amount']['value'] * $daysInMonth / $daysPassed;
        $temp['appointments'] = $clinicAppointments['calls']['value'] * $daysInMonth / $daysPassed;
        $temp['calls'] = $clinicsCalls['calls']['value'] * $daysInMonth / $daysPassed;
        $temp['visits'] = $clinicsVisits['calls']['value'] * $daysInMonth / $daysPassed;

        return $clinic->short_name . ' ' . intval($temp['payments']) . ' ' . intval($temp['calls']) . ' ' . intval($temp['appointments']) . ' ' . intval($temp['visits']);
    }

    public function askManualSelect($ids)
    {
        $this->setUserStorage(json_encode([]));

        $this->ask('Выберите клиники', function (Answer $answer) use ( $ids) {
            if ($answer->isInteractiveMessageReply()) {
                $value = $answer->getValue();

                if ($value === 'amount') {
                    $this->removeLastMessage();
                    $now = Carbon::now()->toDateString();
                    $this->sendReport($ids, true, true, $now, $now);
                }
                $this->updateManualClinicIds($this->clinics, $value);

                $this->bot->sendRequest('editMessageReplyMarkup', array_merge([
                    'chat_id' => $answer->getMessage()->getPayload()['chat']['id'],
                    'message_id' => $answer->getMessage()->getPayload()['message_id'],
                ], $this->selectManualKeyboard($this->clinics)));
            }

            $this->handlerManualSelect($this->clinics);
        }, $this->selectManualKeyboard($this->clinics));
    }

    public function askPeriod($clinicAnswer, $clinics)
    {
        $this->ask('Выберите Период', function (Answer $answer) use ($clinicAnswer, $clinics) {
            if ($answer->isInteractiveMessageReply()) {
                $this->removeLastMessage();
                $this->say('Вы выбрали период ' . self::periods[(string)$answer]);

                $now = Carbon::now();

                $date_end = $now->toDateString();

                switch ((string)$answer) {
                    case 'day':
                        $date_start = $now->toDateString();
                        $this->handleReport($clinicAnswer, $clinics, $date_start, $date_end);
                        break;
                    case 'week':
                        $date_start = $now->startOfWeek()->toDateString();
                        $this->handleReport($clinicAnswer, $clinics, $date_start, $date_end);
                        break;
                    case 'month':
                        $date_start = $now->startOfMonth()->toDateString();
                        $this->handleReport($clinicAnswer, $clinics, $date_start, $date_end);
                        break;
                    case 'quarter':
                        $date_start = $now->startOfQuarter()->toDateString();
                        $this->handleReport($clinicAnswer, $clinics, $date_start, $date_end);
                        break;
                    case 'year':
                        $date_start = $now->startOfYear()->toDateString();
                        $this->handleReport($clinicAnswer, $clinics, $date_start, $date_end);
                        break;
                    case 'forecast_month':
                        $date_start = $now->startOfMonth()->toDateString();
                        $date_end = Carbon::now()->subDay()->toDateString();

                        $this->handleReport($clinicAnswer, $clinics, $date_start, $date_end, 'forecast_month');
                        break;
                    case 'compare_period':
                        $this->askPeriodOneStart($clinicAnswer, $clinics);
                        break;
                    case 'manual':
                        $this->askDateFrom($clinicAnswer, $clinics);
                        break;
                }
            }
        }, Keyboard::create()->type(Keyboard::TYPE_INLINE)
            ->oneTimeKeyboard(true)
            ->addRow(
                KeyboardButton::create("День")->callbackData('day'),
                KeyboardButton::create("Месяц")->callbackData('month'),
                KeyboardButton::create("Задать Вручную")->callbackData('manual')
            )->addRow(
                KeyboardButton::create("Прогноз на месяц")->callbackData('forecast_month'),
                KeyboardButton::create("Сравнение периодов")->callbackData('compare_period')
            )
            ->toArray());
    }


    /**
     * @param $clinicAnswer
     * @param $clinics
     */
    protected function askDateFrom($clinicAnswer, $clinics)
    {
        $this->ask('Укажите дату От в формате ДД.ММ.ГГГГ', function (Answer $answer) use ($clinicAnswer, $clinics, &$date_start, &$date_end) {
            $date_from = $answer->getText();
            $validator = Validator::make(['date_from' => $date_from], [
                'date_from' => 'required|date_format:"d.m.Y"',
            ]);

            if ($validator->valid()) {
                $date_start = Carbon::parse($date_from)->toDateString();

                $this->askDateTo($clinicAnswer, $clinics, $date_from);

            } else {
                $this->say('Неверный формат даты, попробуйте ещё раз.');

                $this->askDateFrom($clinicAnswer, $clinics);
            }
        });
    }

    /**
     * @param $clinicAnswer
     * @param $clinics
     */
    protected function askPeriodOneStart($clinicAnswer, $clinics)
    {
        $this->ask('Укажите дату периода 1 (От) в формате ДД.ММ.ГГГГ', function (Answer $answer) use ($clinicAnswer, $clinics, &$date_start, &$date_end) {
            $date_from = $answer->getText();
            $validator = Validator::make(['date_from' => $date_from], [
                'date_from' => 'required|date_format:"d.m.Y"',
            ]);

            if ($validator->valid()) {
                $date_start = Carbon::parse($date_from)->toDateString();

                $this->askPeriodOneEnd($clinicAnswer, $clinics, $date_from);

            } else {
                $this->say('Неверный формат даты, попробуйте ещё раз.');

                $this->askPeriodOneStart($clinicAnswer, $clinics);
            }
        });
    }

    protected function askPeriodOneEnd($clinicAnswer, $clinics, $date_from)
    {
        $this->ask('Укажите дату периода 1 (По) в формате ДД.ММ.ГГГГ', function (Answer $answer) use ($clinicAnswer, $clinics, $date_from) {
            $date_to = $answer->getText();

            $validator = Validator::make([
                'date_to' => $date_to,
            ], [
                'date_to' => 'required|date|date_format:"d.m.Y"',
            ]);


            if ($validator->valid() && Carbon::parse($date_to) >= Carbon::parse($date_from)) {

                $date_start = Carbon::parse($date_from)->toDateString();
                $date_end = Carbon::parse($date_to)->toDateString();

                $this->askPeriodTwoStart($clinicAnswer, $clinics, $date_start, $date_end);

            } else {
                $this->say('Неверный формат даты, попробуйте ещё раз.');

                $this->askPeriodOneEnd($clinicAnswer, $clinics, $date_from);
            }


        });
    }


    /**
     * @param $clinicAnswer
     * @param $clinics
     */
    protected function askPeriodTwoStart($clinicAnswer, $clinics, $period_one_start, $period_one_end)
    {
        $this->ask('Укажите дату периода 2 (От) в формате ДД.ММ.ГГГГ', function (Answer $answer) use ($clinicAnswer, $clinics, &$date_start, &$date_end, $period_one_start, $period_one_end) {
            $date_from = $answer->getText();
            $validator = Validator::make(['date_from' => $date_from], [
                'date_from' => 'required|date_format:"d.m.Y"',
            ]);

            if ($validator->valid()) {
                $date_start = Carbon::parse($date_from)->toDateString();

                $this->askPeriodTwoEnd($clinicAnswer, $clinics, $period_one_start, $period_one_end, $date_from);

            } else {
                $this->say('Неверный формат даты, попробуйте ещё раз.');

                $this->askPeriodTwoStart($clinicAnswer, $clinics, $period_one_start, $period_one_end);
            }
        });
    }

    protected function askPeriodTwoEnd($clinicAnswer, $clinics, $period_one_start, $period_one_end, $date_from)
    {
        $this->ask('Укажите дату периода 2 (По) в формате ДД.ММ.ГГГГ', function (Answer $answer) use ($clinicAnswer, $clinics, $date_from, $period_one_start, $period_one_end) {
            $date_to = $answer->getText();

            $validator = Validator::make([
                'date_to' => $date_to,
            ], [
                'date_to' => 'required|date|date_format:"d.m.Y"',
            ]);


            if ($validator->valid() && Carbon::parse($date_to) >= Carbon::parse($date_from)) {


                $date_period_two_start = Carbon::parse($date_from)->toDateString();
                $date_period_two_end = Carbon::parse($date_to)->toDateString();

                $periodOne = $this->sendReport($clinics,
                    false, false, $period_one_start,
                    $period_one_end, 'compare_period');

                $periodTwo = $this->sendReport($clinics,
                    false, false, $date_period_two_start,
                    $date_period_two_end, 'compare_period');

                foreach ($periodOne as $name => $clinic) {
                    $this->say('Период 1: '.$name . ' ' . intval($clinic['payments']['payed_amount']['value']) . ' ' . intval($clinic['calls']['calls']['value']) . ' ' . intval($clinic['appointments']['calls']['value']) . ' ' . intval($clinic['visits']['calls']['value']));
                    $this->say('Период 2: '.$name . ' ' . intval($periodTwo[$name]['payments']['payed_amount']['value']) . ' ' . intval($periodTwo[$name]['calls']['calls']['value']) . ' ' . intval($periodTwo[$name]['appointments']['calls']['value']) . ' ' . intval($periodTwo[$name]['visits']['calls']['value']));
                }

            } else {
                $this->say('Неверный формат даты, попробуйте ещё раз.');

                $this->askPeriodTwoEnd($clinicAnswer, $clinics, $period_one_start, $period_one_end, $date_from);
            }


        });
    }

    protected function askDateTo($clinicAnswer, $clinics, $date_from)
    {
        $this->ask('Укажите дату По в формате ДД.ММ.ГГГГ', function (Answer $answer) use ($clinicAnswer, $clinics, $date_from) {
            $date_to = $answer->getText();

            $validator = Validator::make([
                'date_to' => $date_to,
            ], [
                'date_to' => 'required|date|date_format:"d.m.Y"',
            ]);


            if ($validator->valid() && Carbon::parse($date_to) >= Carbon::parse($date_from)) {

                $date_start = Carbon::parse($date_from)->toDateString();
                $date_end = Carbon::parse($date_to)->toDateString();

                $this->say('Даты приняты, генерирую отчёт');

                try {
                    $this->handleReport($clinicAnswer, $clinics, $date_start, $date_end);
                } catch (\Exception $e) {
                    $this->say('Произошла ошибка, попробуйте ещё раз');

                    $this->askDateFrom($clinicAnswer, $clinics);
                }
            } else {
                $this->say('Неверный формат даты, попробуйте ещё раз.');

                $this->askDateTo($clinicAnswer, $clinics, $date_from);
            }


        });
    }


    public function handlerManualSelect($clinics)
    {
        $this->bot->storeConversation($this, function (Answer $clinicAnswer) use ($clinics) {
            if ($clinicAnswer->isInteractiveMessageReply()) {
                $value = $clinicAnswer->getValue();

                $this->updateManualClinicIds($clinics, $value);
                $manualClinicIds = $this->getUserStorage();

                if ($value === 'ok') {
                    $this->removeLastMessage();
                    $this->askPeriod($clinicAnswer, $manualClinicIds);
                } else {
                    $this->handlerManualSelect($clinics);
                    $this->bot->sendRequest('editMessageReplyMarkup', array_merge([
                        'chat_id' => $clinicAnswer->getMessage()->getPayload()['chat']['id'],
                        'message_id' => $clinicAnswer->getMessage()->getPayload()['message_id'],
                    ], $this->selectManualKeyboard($clinics)));
                }
            } else {
                $this->handlerManualSelect($clinics);
            }
        }, 'Выберите клиники');
    }

    /**
     * @param $clinics
     * @return array
     */
    public function selectManualKeyboard($clinics)
    {
        $manualClinicIds = $this->getUserStorage();
        $buttons = [];
        foreach ($clinics as $value) {
            $buttons[] = KeyboardButton::create((in_array($value->id, $manualClinicIds) ? '✅ ' : ' ') . $value->short_name)->callbackData($value->id);
        }
        if (empty($manualClinicIds)) {
            $buttons[] = KeyboardButton::create('Итого')->callbackData('amount');
        }

        $keyboard = Keyboard::create(Keyboard::TYPE_INLINE);
        $buttonChunks = array_chunk($buttons, 5);

        foreach ($buttonChunks as $buttonChunk) {
            $keyboard->addRow(...$buttonChunk);
        }

        $keyboard->addRow(
            KeyboardButton::create('OK')->callbackData('ok'),
            KeyboardButton::create('Все')->callbackData('all')
        );

        return $keyboard
            ->resizeKeyboard()
            ->toArray();
    }

    public function updateManualClinicIds($clinics, $value = null)
    {
        $ids = $this->getUserStorage();
        if ($value) {
            if (in_array($value, ['ok', 'all'])) {
                if ($value === 'all') {
                    $ids = $clinics->count() === count($ids) ? [] : $clinics->map(function ($clinic) {
                        return $clinic['id'];
                    })->toArray();
                }
            } else {
                $clinic = $clinics->first(function ($clinic) use ($value) {
                    return $clinic['id'] == $value;
                });
                if ($clinic) {
                    if (in_array($clinic['id'], $ids)) {
                        $ids = array_diff($ids, [$clinic['id']]);
                    } else {
                        $ids = array_merge($ids, [$clinic['id']]);
                    }
                }
            }
        }
        $this->setUserStorage(json_encode($ids));
    }

    private function amountString($currency, $data)
    {
        return 'ИТОГО (' . $currency . ') ' . $data['uah']['payments'];
    }

    private function setUserStorage($value)
    {
        Redis::set($this->key, $value);
        Redis::expire($this->key, self::STORAGE_TIME);
    }

    private function getUserStorage()
    {
        return (array)json_decode(Redis::get($this->key));
    }

    /**
     * @param $clinicAnswer
     * @param $clinics
     * @param string $date_start
     * @param string $date_end
     * @throws \BotMan\BotMan\Exceptions\Core\BadMethodCallException
     */
    protected function handleReport($clinicAnswer, $clinics, string $date_start, string $date_end, $type = null): void
    {
        $value = $clinicAnswer->getValue();

        $this->updateManualClinicIds($clinics, $value);

        $manualClinicIds = $this->getUserStorage();
        if ($value === 'amount') {
            $this->removeLatestInlineKeyboard();
            $this->sendReport($clinics->pluck('id'),
                true, true, $date_start, $date_end);
        } else {
            if ($value === 'ok') {
                if (count($manualClinicIds) != 0) {
                    $this->removeLatestInlineKeyboard();
                    $this->removeLastMessage();
                    $this->sendReport($manualClinicIds,
                        false, false, $date_start, $date_end, $type);
                } else {
                    $this->say('Вы не выбрали ни одной клиники!');
                }
            } else {
                $this->handlerManualSelect($clinics);
            }
        }
    }

    private function getData($data, $clinic){

        if(empty($data['aggr_group'])){
            return 0;
        }

        return array_first($data['aggr_group'], function ($data_item) use ($clinic) {
            return $data_item['key'] === $clinic['id'];
        });
    }

    private function getClinics($ids) 
    {
        return Clinic::selectRaw('id, short_name')->whereIn('id', $ids)->orderBy('name')->get();
    }
}
