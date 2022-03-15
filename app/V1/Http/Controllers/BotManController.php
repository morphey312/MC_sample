<?php

namespace App\V1\Http\Controllers;

use App\V1\Conversations\MainConversation;
use App\V1\Conversations\ReportClinicsConversation;
use App\V1\Conversations\UnsubscribeConversation;
use App\V1\Conversations\SwapSubscribeModeConversation;


class BotManController extends Controller
{
    public function handle()
    {

        $botman = resolve('botman');
      
        $botman->hears('/start', function ($bot) {
            $bot->startConversation(new MainConversation);
        })->stopsConversation();
        
        $botman->hears('Отписаться', function ($bot) {
            $bot->startConversation(new UnsubscribeConversation);
        })->stopsConversation();

        $botman->hears('Ручной режим', function ($bot) {
            $bot->startConversation(new SwapSubscribeModeConversation);
        })->stopsConversation();

        $botman->hears('Выбрать клиники', function ($bot) {
            $bot->startConversation(new ReportClinicsConversation);
        })->stopsConversation();

        $botman->hears('Автоматический режим', function ($bot) {
            $bot->startConversation(new SwapSubscribeModeConversation);
        })->stopsConversation();

        $botman->listen();
    }
}
