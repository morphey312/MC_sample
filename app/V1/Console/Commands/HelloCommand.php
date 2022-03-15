<?php

namespace App\V1\Console\Commands;

use Illuminate\Console\Command;
use App\V1\Events\Broadcast\Notification;

class HelloCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'say:hello {--via=App.General : Channel to use} {message : Message to be sent}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Broadcast a message';
    
    protected $channels = [
        'App.General' => Notification::TYPE_GENERAL,
        'App.Operators' => Notification::TYPE_OPERATORS,
        'App.User' => Notification::TYPE_USER,
    ];
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $message = $this->argument('message');
        $channel = $this->option('via');
        $target = null;
        
        if (strpos($channel, ':') !== false) {
            list($channel, $target) = explode(':', $channel);
        }
        
        if (isset($this->channels[$channel])) {
            $channel = $this->channels[$channel];
        }
        
        broadcast(new Notification($message, $channel, $target));
    }
}
