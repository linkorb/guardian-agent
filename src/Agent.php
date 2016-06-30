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
    
    protected $checks = [];
    public function addCheck(Check $check)
    {
        if (!$check->getName()) {
            throw new RuntimeException("Can't add a check without a name");
        }
        $this->checks[$check->getName()] = $check;
    }
    public function getChecks()
    {
        return $this->checks;
    }
    
    public function getCheck($checkName)
    {
        return $this->checks[$checkName];
    }
    
    public function tick()
    {
        foreach ($this->checks as $check) {
            $now = time();
            if ($now > ($check->getLastStamp() + $check->getInterval())) {
                $checkResult = $this->runCheck($check);
                if (!isset($this->checkResults[$check->getName()])) {
                    $this->checkResults[$check->getName()] = [];
                }
                array_unshift($this->checkResults[$check->getName()], $checkResult);
                $this->cleanupCheckResults($check->getName());
                $check->setLastStamp($now);
            }
        }
        
        $this->scheduleNextTick();
    }
    
    public function cleanupCheckResults($checkName)
    {
        $res = [];
        foreach ($this->checkResults[$checkName] as $key => $value) {
            if ($value->getStamp()> (time()-20)) {
                $res[] = $value;
            }
        }
        $this->checkResults[$checkName] = $res;
    }
    
    public function getCheckResultsByCheckName($checkName)
    {
        if (!isset($this->checkResults[$checkName])) {
            return [];
        }
        return $this->checkResults[$checkName];
    }
    
    public function runCheck(Check $check)
    {
        //echo "Executing check " . $check->getName() . "\n";
        $process = new Process($check->getCommand());
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
    
    protected $loop;
    protected $socket;
    protected $http;
    
    public function run()
    {
        $this->loop = \React\EventLoop\Factory::create();
        $this->socket = new \React\Socket\Server($this->loop);
        $this->http = new \React\Http\Server($this->socket, $this->loop);
        
        
        $requestHandler = new RequestHandler($this);
        $this->http->on('request', [$requestHandler, 'handle']);
        $this->socket->listen($this->getPort());
        $this->scheduleNextTick();

        $this->loop->run();
    }
}
