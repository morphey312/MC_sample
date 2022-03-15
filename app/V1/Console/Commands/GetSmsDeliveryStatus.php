<?php

namespace App\V1\Console\Commands;

use App\V1\Models\SmsAppointmentReminder;
use App\V1\Services\Messenger\Message;
use App\V1\Services\SMS\Esputnik;
use App\V1\Sms\Appointment\Reminders\AppointmentReminder;
use App\V1\Contracts\Repositories\SmsAppointmentReminderRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Exception;
use Illuminate\Support\Facades\Log;
use App\V1\Facades\Client;

class GetSmsDeliveryStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:get-delivery-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Sms Delivery status';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(SmsAppointmentReminderRepository $repository)
    {
        $smsReminders = $repository->getPending();
        $grouped = $smsReminders->groupBy('template.channel.id');

        foreach ($grouped as $channelGroup) {
            $smsReminderIds = $channelGroup->pluck('vendor_id')->toArray();
            $channel = $channelGroup->first()->template->channel;
            $esputnikClient = $this->createSmsProviderClient($channel);
            $statuses = $this->getStatusForIDs($smsReminderIds, $esputnikClient);
            if (!empty($statuses)) {
                $this->setSmsReminderStatus($statuses, $repository);
            }
        }
    }

    private function getStatusForIDs($IDs, $esputnikClient)
    {
        return $esputnikClient->checkSmsStatus($IDs)['results'];
    }

    private function createSmsProviderClient($channel)
    {
        $api_key = $channel->account_password;
        $config = Client::on($channel->company)->getClientConfig('services.sms');

        if (!empty($api_key)) {
            $config = array_merge($config, [
                'api_key' => $api_key,
            ]);
        }
        
        return new Esputnik($config);
    }

    private function setSmsReminderStatus($smsStatuses, $repository)
    {
        if (!empty($smsStatuses['status'])) {
            $smsStatuses = [$smsStatuses];
        }

        foreach ($smsStatuses as $smsStatus) {
            $status = null;

            switch ($smsStatus['status']) {
                case SmsAppointmentReminder::DELIVERY_STATUS_PENDING:
                case SmsAppointmentReminder::DELIVERY_STATUS_IN_QUEUE:
                    $status = Message::STATUS_DELIVERING;
                    break;
                case SmsAppointmentReminder::DELIVERY_STATUS_NO_MONEY:
                case SmsAppointmentReminder::DELIVERY_STATUS_SMS_UNDELIVERED_STATE:
                case SmsAppointmentReminder::DELIVERY_STATUS_EXPIRED:
                case SmsAppointmentReminder::DELIVERY_STATUS_NOT_FOUND:
                case SmsAppointmentReminder::DELIVERY_STATUS_SMS_UNDELIVERED_STATE_NEW:
                    $status = Message::STATUS_DELIVERY_FAILED;
                    break;
                case SmsAppointmentReminder::DELIVERY_STATUS_DELIVERED:
                case SmsAppointmentReminder::DELIVERY_STATUS_SEND:
                    $status = Message::STATUS_DELIVERY_OK;
                    break;
            }

            $repository->updateStatus($smsStatus['requestId'], $status, $smsStatus);
        }
    }
}
