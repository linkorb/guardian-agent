<?php

namespace Guardian\Agent;

use Guardian\Agent\Model\Check;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Agent
{
    protected $name;
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    protected $port = 8080;
    
    public function getPort()
    {
        return $this->port;
    }
    
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }
    
    protected $stompAddress;
    public function getStompAddress()
    {
        return $this->stompAddress;
    }
    
    public function setStompAddress($stompAddress)
    {
        $this->stompAddress = $stompAddress;
        return $this;
    }
    
    protected $stompUsername;
    public function getStompUsername()
    {
        return $this->stompUsername;
    }
    
    public function setStompUsername($stompUsername)
    {
        $this->stompUsername = $stompUsername;
        return $this;
    }
    
    protected $stompPassword;
    public function getStompPassword()
    {
        return $this->stompPassword;
    }
    
    public function setStompPassword($stompPassword)
    {
        $this->stompPassword = $stompPassword;
        return $this;
    }
    
    
    protected $groupNames = [];
    public function addGroupName($groupName)
    {
        $this->groupNames[$groupName] = $groupName;
    }
    
    public function getGroupNames()
    {
        return $this->groupNames;
    }
    
    public function hasGroupName($groupName)
    {
        return isset($this->groupNames[$groupName]);
    }
    
    protected $lastStatus = 0;
    
    public function tick()
    {
        // NOOP
        if ($this->lastStatus < time() - 1) {
            $this->lastStatus = time();
            $this->sendStatus();
        }
        $this->scheduleNextTick();
    }
    
    public function sendMessage($message)
    {
        print_r($message);
        $this->stomp->send('/topic/monitor', json_encode($message));
    }
    
    public function sendStatus()
    {
        $message = [
            'type' => 'status',
            'from' => $this->getName(),
            'payload' => [
                'name' => $this->getName()
            ]
        ];
        $this->sendMessage($message);
    }
    
    public function runCommand($command)
    {
        echo "Executing command: " . $command . "\n";
        $process = new Process($command);
        $process->setTimeout(60);
        $process->run();
        $checkResult = NagiosUtils::parseCheckResult($process->getOutput());
        $checkResult->setStatusCode($process->getExitCode());
        $checkResult->setStamp(time());
        return $checkResult;
    }
    
    public function scheduleNextTick()
    {
        $this->loop->addTimer(
            0.1,
            function () {
                $this->tick();
            }
        );
    }
    
    public function processMessage($message)
    {
        $data = json_decode($message, true);
        if (!$data) {
            echo "Invalid JSON: " . $message . "\n";
            return;
        }
        switch ($data['type']) {
            case 'check_request':
                $requestId = $data['payload']['requestId'];
                $command = $data['payload']['command'];
                $checkResult = $this->runCommand($command);
                $message = [
                    'type' => 'check-response',
                    'from' => $this->getName(),
                    'payload' => [
                        'requestId' => $requestId,
                        'statusCode' => $checkResult->getStatusCode()
                    ]
                ];
                $this->sendMessage($message);
                
                break;
            default:
                echo "Unsupported message type: " . $data['type'];
                break;
        }
    }
    
    protected $loop;
    protected $socket;
    protected $http;
    protected $stomp;
    
    public function run()
    {
        $this->loop = \React\EventLoop\Factory::create();
        $this->socket = new \React\Socket\Server($this->loop);
        $this->http = new \React\Http\Server($this->socket, $this->loop);
        
        // Setup stomp
        $stompFactory = new \React\Stomp\Factory($this->loop);
        $this->stomp = $stompFactory->createClient(
            array(
                'host' => $this->getStompAddress(),
                'vhost' => '/',
                'login' => $this->getStompUsername(),
                'passcode' => $this->getStompPassword()
            )
        );
        
        $this->stomp->connect();
        $this->stomp->subscribe('/topic/agent:' . $this->getName(), function ($frame) {
            $this->processMessage($frame->body);
        });

        $requestHandler = new RequestHandler($this);
        $this->http->on('request', [$requestHandler, 'handle']);
        $this->socket->listen($this->getPort());
        $this->scheduleNextTick();

        $this->loop->run();
    }
}
