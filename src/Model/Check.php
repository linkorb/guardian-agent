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
}
