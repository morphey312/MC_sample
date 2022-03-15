<?php

namespace App\V1\Conversations;

use App\V1\Traits\Telegram\TelegramEmployee;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\Drivers\Telegram\Extensions\Keyboard;
use BotMan\Drivers\Telegram\Extensions\KeyboardButton;
use Illuminate\Support\Collection;

class DefaultConversation extends Conversation
{
    use TelegramEmployee;

    public function run()
    {
        self::run();
    }

    public function deleteKeyboard()
    {
        return [
            'reply_markup' => json_encode(Collection::make([
                'keyboard' => [],
                'remove_keyboard' => true,
            ])->filter()),
        ];
    }

    public function removeLastMessage()
    {  
        $botMessage = $this->getBot()->getMessage()->getPayload();
        $this->bot->sendRequest('deleteMessage', array_merge([
            'chat_id' => $botMessage['chat']['id'],
            'message_id' => $botMessage['message_id'],
        ]));
    }
    public function removeLatestInlineKeyboard($payload = null)
    {
        $botMessage = $payload ?: $this->getBot()->getMessage()->getPayload();
        $this->getBot()->sendRequest('editMessageReplyMarkup', [
            'chat_id' => $botMessage['chat']['id'],
            'message_id' => $botMessage['message_id'],
            'inline_keyboard' => []
        ]);
    }

    public function menu()
    {
        $buttons = [];
        $employee = $this->getEmployee(self::getBot()->getUser()->getId());

        if ($employee && $employee->telegram_subscribe_mode) {
            
            $displayChangeClinics = false;
            if ($employee->telegram_subscribe_mode === ReportClinicsConversation::AUTO_MODE) {
                $buttons[] = KeyboardButton::create('Отписаться');
                $swappedMode = [
                    'name' => 'Ручной режим',
                    'value' => ReportClinicsConversation::MANUAL_MODE,
                ];
            } else {
                $swappedMode = [
                    'name' => 'Автоматический режим',
                    'value' => ReportClinicsConversation::AUTO_MODE,
                ];

                $displayChangeClinics = true;
            }
            $buttons[] = KeyboardButton::create($swappedMode['name']);
            if ($displayChangeClinics) {
                $buttons[] = KeyboardButton::create('Выбрать клиники');
            }
        } else {
            $buttons[] = KeyboardButton::create('Автоматический режим');
            $buttons[] = KeyboardButton::create('Ручной режим');
        }
        return Keyboard::create(Keyboard::TYPE_KEYBOARD)
            ->addRow(...$buttons)
            ->resizeKeyboard()
            ->toArray();
    }

    public function askMode()
    {       
        $chooseModeQuestion = Question::create('Какой режим вас интересует?')
            ->addButtons([
                Button::create('Автоматический режим (отчет по всем клиникам)')->value(ReportClinicsConversation::AUTO_MODE),
                Button::create('Ручной режим (отчет с ручным выбором)')->value(ReportClinicsConversation::MANUAL_MODE),
            ]);
        $this->ask($chooseModeQuestion, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                switch ($answer->getValue()) {
                    case ReportClinicsConversation::AUTO_MODE:
                        $this->askReceiveDailyReport();
                        break;
                    case ReportClinicsConversation::MANUAL_MODE:
                        $this->askReceiveDailyReport(false);
                }
            }
        });
    }

    /**
     * @param boolean $isAutoMode
     * @param boolean $fromMenu
     */
    public function askReceiveDailyReport($isAutoMode = true, $fromMenu = false)
    {
        $this->removeLatestInlineKeyboard();
        if ($isAutoMode) {
            $receiveDailyReportQuestion = Question::create('Получать ежедневный отчет по всем клиникам?')
                ->addButtons([
                    Button::create('Да')->value('yes'),
                    Button::create('Нет')->value('no'),
                ]);
            $this->ask($receiveDailyReportQuestion, function (Answer $answer) use ($fromMenu) {
                if ($answer->isInteractiveMessageReply()) {
                    switch ($answer->getValue()) {
                        case 'yes':
                            $this->removeLatestInlineKeyboard();

                            $sender = $answer->getMessage()->getSender();
                            $employee = $this->getEmployee($sender);

                            if ($employee) {
                                $employee->telegram_subscribe_mode = ReportClinicsConversation::AUTO_MODE;
                                $employee->save();
                                $this->say('Вам доступен для просмотра ежедневный отчет по всем клиникам сети ОН Клиник & Доктор ПРО. Данные будут отправляться ежедневно', $this->menu());
                            }
                            break;
                        case 'no':
                            if ($fromMenu) {
                                $this->removeLatestInlineKeyboard();
                            } else {
                                $this->askMode();
                            }
                    }
                }
            });
        } else {
            $employee = $this->getEmployee($this->getBot()->getUser()->getId());
            if ($employee) {
                $this->bot->startConversation(new ReportClinicsConversation);
            }
        }
    }
}
