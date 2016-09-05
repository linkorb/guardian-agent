<?php

namespace Guardian\Agent\Loader;

use RuntimeException;

use Guardian\Agent\Agent;
use Guardian\Agent\Model\Check;
use Symfony\Component\Yaml\Parser as YamlParser;

class YamlLoader
{
    public function loadYaml($filename)
    {

        if (!file_exists($filename)) {
            throw new RuntimeException("File not found: $filename");
        }

        $parser = new YamlParser();
        $data = $parser->parse(file_get_contents($filename));
        if (isset($data['include'])) {
            foreach ($data['include'] as $line) {
                $filenames = glob($line);
                if (count($filenames)==0) {
                    throw new RuntimeException("Include(s) not found: " . $line);
                }
                foreach ($filenames as $filename) {
                    if (!file_exists($filename)) {
                        throw new RuntimeException("Include filename does not exist: " . $filename);
                    }
                    $includeData = $this->loadYaml($filename);
                    $data = array_merge_recursive($data, $includeData);
                }
            }
        }
        return $data;
    }

    public function loadAgent($data)
    {
        $agentData = $data['agent'];
        $agent = new Agent();
        $agent->setName($agentData['name']);
        if (isset($agentData['port'])) {
            $agent->setPort($agentData['port']);
        }
        if (isset($agentData['groups'])) {
            $groupNames = $agentData['groups'];
            foreach ($groupNames as $groupName) {
                $agent->addGroupName($groupName);
            }
        }
        
        $agent->setStompAddress($agentData['stomp']['address']);
        $agent->setStompUsername($agentData['stomp']['username']);
        $agent->setStompPassword($agentData['stomp']['password']);
        
        return $agent;
    }

    public function loadChecks($data)
    {
        $checks = [];
        foreach ($data['checks'] as $name => $checkData) {
            $check = new Check();
            $check->setName($name);
            
            if (isset($checkData['command'])) {
                $check->setCommand($checkData['command']);
            }
            if (isset($checkData['interval'])) {
                $check->setInterval($checkData['interval']);
            }
            $checks[$check->getName()] = $check;
        }
        return $checks;
    }
}
