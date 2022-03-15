<?php

namespace App\V1\Conversations;

use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;

class UnsubscribeConversation extends DefaultConversation
{
    public function run()
    {
        $employee = $this->getEmployee($this->getBot()->getUser()->getId());
        if ($employee && $employee->telegram_subscribe_mode) {
            $question = Question::create('Вы действительно хотите отписаться от Автоматического режима отправки отчётов?')
                ->addButtons([
                    Button::create('Да')->value('yes'),
                    Button::create('Нет')->value('no'),
                ]);

            $this->ask($question, function (Answer $answer) use ($question, $employee) {
                if ($answer->isInteractiveMessageReply()) {
                    $this->removeLatestInlineKeyboard();

                    switch ($answer->getValue()) {
                        case 'yes':
                            $employee->telegram_subscribe_mode = null;
                            $employee->save();

                            $this->say('Вы успешно отписались от Автоматического режима отправки отчётов!', $this->menu());
                            break;
                        case 'no':
                            $this->say('Вы продолжаете получать рассылки!');
                    }
                }
            });
        }
    }
}
