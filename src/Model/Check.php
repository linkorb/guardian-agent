<?php

namespace Guardian\Agent\Model;

class Check
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
    
    
    protected $command;
    
    public function getCommand()
    {
        return $this->command;
    }
    
    public function setCommand($command)
    {
        $this->command = $command;
        return $this;
    }
    
    protected $interval;
    
    public function getInterval()
    {
        return $this->interval;
    }
    
    public function setInterval($interval)
    {
        $this->interval = $interval;
        return $this;
    }
    
    protected $lastStamp = 0;
    public function getLastStamp()
    {
        return $this->lastStamp;
    }
    
    public function setLastStamp($lastStamp)
    {
        $this->lastStamp = $lastStamp;
        return $this;
    }
    
    public function getLastText()
    {
        return time() - $this->lastStamp . 's ago';
    }
}
