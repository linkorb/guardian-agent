<?php

namespace Guardian\Agent\Model;

class CheckResult
{
    protected $statusCode;
    
    public function getStatusCode()
    {
        return $this->statusCode;
    }
    
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }
    
    
    protected $message;
    
    public function getMessage()
    {
        return $this->message;
    }
    
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }
    
    protected $measurements;
    
    public function addMeasurement(Measurement $measurement)
    {
        $this->measurements[] = $measurement;
    }
    
    public function getMeasurements()
    {
        return $this->measurements;
    }
    
    protected $stamp;
    
    public function getStamp()
    {
        return $this->stamp;
    }
    
    public function setStamp($stamp)
    {
        $this->stamp = $stamp;
        return $this;
    }
}
