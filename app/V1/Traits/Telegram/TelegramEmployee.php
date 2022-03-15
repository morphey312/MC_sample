<?php

namespace App\V1\Traits\Telegram;

use App\V1\Models\Employee;

trait TelegramEmployee
{
    /**
     * @param string|null $phoneOrUserId
     * @return mixed
     */
    public function getEmployee($phoneOrUserId = null)
    {
        return Employee::where(function ($query) use ($phoneOrUserId) {
            $query->where('phone', '=', $phoneOrUserId)
                ->orWhere('phone', '=', '+' . $phoneOrUserId)
                ->orWhere('telegram_user_id', '=', $phoneOrUserId);
        })->with(['employee_clinics'])->whereHas('employee_clinics', function ($query) {
            $query->where('status', '=', 'working');
        })->first();
    }
}
