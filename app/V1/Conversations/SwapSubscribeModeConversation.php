<?php

namespace App\V1\Conversations;

class SwapSubscribeModeConversation extends DefaultConversation
{
    public function run()
    {
        $isAutoMode = true;
        if ($this->getBot()->getMessage()->getText() === 'Ручной режим') {
            $isAutoMode = false;
        }

        $this->askReceiveDailyReport($isAutoMode, true);
    }
}
