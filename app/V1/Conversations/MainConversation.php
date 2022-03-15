<?php

namespace App\V1\Conversations;

use App\V1\Repositories\Notification\TemplateRepository;
use BotMan\BotMan\Messages\Attachments\Contact;
use BotMan\Drivers\Telegram\Extensions\Keyboard;
use BotMan\Drivers\Telegram\Extensions\KeyboardButton;

class MainConversation extends DefaultConversation
{
    protected $templatesRepository;

    public function __construct()
    {
        $this->templatesRepository = new TemplateRepository();
    }

    /**
     * Start the conversation
     */
    public function run()
    {
        $this->askPhoneNumber();
    }

    public function unidentifiedUser()
    {
        $this->say('Извините, мы не идентифицировали Вас!', $this->deleteKeyboard());
    }

    public function askPhoneNumber()
    {
        $employeeByUserId = $this->getEmployee($this->getBot()->getUser()->getId());
        if ($employeeByUserId) {
            $template = $this->templatesRepository->getUsableTemplatesByEmployeePositions(
                $employeeByUserId->employee_clinics->map(function ($item) {
                    return $item->position_id;
                })->toArray()
            )->first();

            if ($template) {
                $this->askMode();
            } else {
                $this->unidentifiedUser();
            }
        } else {
            $replyContactKeyboard = Keyboard::create(Keyboard::TYPE_KEYBOARD)
                ->resizeKeyboard()
                ->oneTimeKeyboard()
                ->addRow(KeyboardButton::create('Поделиться 📱 для аутентификации')->requestContact())
                ->toArray();

            $this->askForContact('Поделитесь вашим номером телефона', function (Contact $contact) {
                $phone = $contact->getPhoneNumber();
                $employee = $this->getEmployee($phone);

                if ($employee) {
                    $employee->telegram = 1;
                    $employee->telegram_user_id = $contact->getUserId();

                    $template = $this->templatesRepository->getUsableTemplatesByEmployeePositions(
                        $employee->employee_clinics->map(function ($item) {
                            return $item->position_id;
                        })->toArray()
                    )->first();

                    if ($template) {
                        $this->say('Мы Вас успешно идентифицировали!', $this->menu());
                        $this->askMode();
                    } else {
                        $this->unidentifiedUser();
                    }

                    $employee->save();
                } else {
                    $this->unidentifiedUser();
                }
            },  null, $replyContactKeyboard);
        }
    }
}
