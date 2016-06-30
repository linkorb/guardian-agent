<?php

namespace Guardian\Agent\Model;

class Measurement
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
    
    protected $value;
    
    public function getValue()
    {
        return $this->value;
    }
    
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
    
    protected $unit;
    
    public function getUnit()
    {
        return $this->unit;
    }
    
    public function setUnit($unit)
    {
        $this->unit = $unit;
        return $this;
    }
    
}
