<?php

namespace Guardian\Agent;

use Guardian\Agent\Model\CheckResult;
use Guardian\Agent\Model\Measurement;

class NagiosUtils
{
    /**
     * Parse nagios output strings
     *
     * For example:
     *
     * DISK CRITICAL - free space: / 2879 MB (1% inode=1%); /dev 0 MB (0% inode=0%);| /=235103MB;238132;238032;0;238232 /dev=0MB;-100;-200;0;0
     *
     */
    public static function parseCheckResult($string)
    {
        $part = explode('|', $string);
        $perfdata = $part[1];
        $message = $part[0];
        
        $checkResult = new CheckResult();
        $checkResult->setMessage($message);
        
        if ($perfdata) {
            $measurementParts = explode(" ", $perfdata);
            foreach ($measurementParts as $m) {
                $part = explode("=", $m);
                $name = trim($part[0]);
                if ($name && (count($part)==2)) {
                    $values = explode(";", $part[1]);
                    $measurement = new Measurement();
                    $measurement->setName($name);
                    
                    $unit = '';
                    $value = '';
                    foreach (str_split($values[0]) as $char) {
                        if (ctype_alpha($char)) {
                            $unit .= $char;
                        } else {
                            $value .= $char;
                        }
                    }
                    $measurement->setValue($value);
                    $measurement->setUnit($unit);
                    
                    $checkResult->addMeasurement($measurement);
                }
            }
        }
        return $checkResult;
    }
}
