<?php

namespace App\V1\Console\Commands;

use Illuminate\Console\Command;
use App\V1\Contracts\Services\VoipService;
use ReflectionClass;
use PAMI\Client\Impl\ClientImpl;
use PAMI\Message\Action;
use App\V1\Services\Voip\PAMI\Actions\ParkinglotsAction;
use App\V1\Services\Voip\PAMI\Actions\ConfbridgeListAction;
use App\V1\Services\Voip\PAMI\Actions\ConfbridgeListRoomsAction;
use App\V1\Services\Voip\PAMI\Actions\ParkAction;

class AMICommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asterisk:ami';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Communicate with Asterisk';
    
    /**
     * @var \ReflectionClass
     */ 
    protected $reflection;
    
    /**
     * @var ClientImpl
     */ 
    protected $client;
    
    /**
     * @var string
     */ 
    protected $waitingEvent = null;
    
    /**
     * @var array
     */ 
    protected $interestingEvents = [];
    
    /**
     * @var PAMI\Message\Response\ResponseMessage
     */ 
    protected $lastResponse = null;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('>> Hello!');
        $this->info('>> Connecting...');
        
        $this->reflection = new ReflectionClass($this);
        $this->client = new ClientImpl(config('voip.client'));
        //$this->client->setLogger(new Logger\ConsoleLogger($this));
        $this->client->open();
        $this->client->registerEventListener(function($event) {
            $this->processEvent($event);
        });
        $commands = $this->getCommandsList();
        
        $this->info('>> Connected!');
        
        while (true) {
            $command = $this->anticipate('Command', $commands);
            
            if (strtolower($command) == 'quit') {
                break;
            } elseif (in_array(strtolower($command), $commands)) {
                $this->callCommand($command);
            } else {
                $this->info('>> Sorry, did not get you.');
            }
        }
        
        $this->info('>> Disconnecting...');
        $this->client->close();
        
        $this->info('>> Bye!');
    }
    
    /**
     * @return array
     */ 
    protected function getCommandsList()
    {
        $commands = ['quit'];
        foreach ($this->reflection->getMethods() as $method) {
            $name = $method->getName();
            if (substr($name, 0, 7) == 'command') {
                $commands[] = strtolower(substr($name, 7));
            }
        }
        return $commands;
    }
    
    /**
     * @param string $command
     */ 
    protected function callCommand($command)
    {
        $method = $this->reflection->getMethod('command' . $command);
        $params = $method->getParameters();
        $args = [];
        foreach ($params as $param) {
            $name = $param->getName();
            if (strval($param->getType()) == 'bool') {
                $default = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : false;
                $args[] = $this->choice($name, ['true', 'false'], $default ? 'false' : 'true') === 'true';
            } else {
                $default = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : '';
                $args[] = $this->ask($name . ($default ? " [$default]" : '')) ?: $default;
            }
        }
        $method->invokeArgs($this, $args);
    }
    
    /**
     * Process incoming events
     * 
     * @param \PAMI\Message\Event\EventMessage $event
     */ 
    public function processEvent($event)
    {
        $name = $event->getName();
        if ($this->waitingEvent == $name || in_array($name, $this->interestingEvents)) {
            $this->info('Event occured:');
            $this->line($event->getRawContent());
            $this->info('<<<<<<<<<<<<<<<<<');
            if ($this->waitingEvent == $name) {
                $this->waitingEvent = null;
            }
        } else {
            $this->info('Event occured:' . $name . ' not interesting...');
        }
    }
    
    /**
     * Wait for event 
     * 
     * @param string $event
     * @param array $interestingEvents
     */ 
    protected function waitForEvent($event, array $interestingEvents = [])
    {
        $this->info('Waiting for event "' . $event . '"...');
        $this->waitingEvent = $event;
        $this->interestingEvents = $interestingEvents;
        if ($this->lastResponse) {
            foreach ($this->lastResponse->getEvents() as $event) {
                $this->processEvent($event);
            }
            $this->lastResponse = null;
        }
        while ($this->waitingEvent != null) {
            sleep(1);
            $this->client->process();
        }
        $this->interestingEvents = [];
    }
    
    /**
     * Send action and show response
     * 
     * @param Action\ActionMessage $action
     */ 
    protected function sendAction($action)
    {
        $this->info('Sending action...');
        $this->line($action->serialize());
        $this->info('>>>>>>>>>>>>>>>>>');
        $response = $this->client->send($action);
        if ($response->isSuccess()) {
            $this->info('Response given:');
        } else {
            $this->error('Response given:');
        }
        $this->line($response->getRawContent());
        $this->info('<<<<<<<<<<<<<<<<<');
        $this->lastResponse = $response;
    }
    
    /**
     * Queue summary
     */ 
    public function commandQsummary($queue = false)
    {
        $action = new Action\QueueSummaryAction($queue);
        $this->sendAction($action);
        $this->waitForEvent('QueueSummaryComplete', ['QueueSummary']);
    }
    
    /**
     * Queue status
     */ 
    public function commandQstat($queue = false, $member = false)
    {
        $action = new Action\QueueStatusAction($queue, $member);
        $this->sendAction($action);
        $this->waitForEvent('QueueStatusComplete', ['QueueParams', 'QueueMember']);
    }
    
    /**
     * Pause member
     */ 
    public function commandMempause($interface, $queue = false, $reason = false)
    {
        $action = new Action\QueuePauseAction($interface, $queue, $reason);
        $this->sendAction($action);
    }
    
    /**
     * UnPause member
     */ 
    public function commandMemunpause($interface, $queue = false, $reason = false)
    {
        $action = new Action\QueueUnpauseAction($interface, $queue, $reason);
        $this->sendAction($action);
    }
    
    /**
     * List of parking lots
     */ 
    public function commandParkinglots()
    {
        $action = new ParkinglotsAction();
        $this->sendAction($action);
        $this->waitForEvent('ParkinglotsComplete', ['Parkinglot']);
    }
    
    /**
     * Park the channel
     */ 
    public function commandPark($channel, $lot)
    {
        $action = new ParkAction($channel, $lot);
        $this->sendAction($action);
    }
    
    /**
     * Parked calls
     */ 
    public function commandParkedcalls($lot)
    {
        $action = new Action\ParkedCallsAction($lot);
        $this->sendAction($action);
        $this->waitForEvent('ParkedCallsComplete', ['ParkedCall']);
    }
    
    /**
     * List of bridges
     */ 
    public function commandConfbridgelist($conference)
    {
        $action = new ConfbridgeListAction($conference);
        $this->sendAction($action);
        $this->waitForEvent('ConfbridgeListComplete', ['ConfbridgeList']);
    }
    
    /**
     * List of rooms
     */ 
    public function commandConfbridgerooms()
    {
        $action = new ConfbridgeListRoomsAction();
        $this->sendAction($action);
        $this->waitForEvent('ConfbridgeListRoomsComplete', ['ConfbridgeListRooms']);
    }
    
    /**
     * Redirect channel(s)
     */ 
    public function commandRedirect($context, $extension, $channel1, $channel2)
    {
        $action = new Action\RedirectAction($channel1, $extension, $context, 1);
        if ($channel2) {
            $action->setExtraChannel($channel2);
            $action->setExtraExtension($extension);
            $action->setExtraContext($context);
            $action->setExtraPriority(1);
        }
        $this->sendAction($action);
    }
}
