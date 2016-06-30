<?php

namespace Guardian\Agent;

use LightnCandy\LightnCandy;

class RequestHandler
{
    
    public $agent;
    
    public function __construct(Agent $agent)
    {
        $this->agent = $agent;
    }
    
    public function handle($request, $response)
    {
        $q = $request->getQuery();
        $controller='index';
        
        if (isset($q['controller'])) {
            $controller = $q['controller'];
        }
        $controllerName = $controller . 'Controller';
        $this->$controllerName($request, $response);
    }
    
    private function indexController($request, $response)
    {
        $response->writeHead(200, array('Content-Type' => 'text/html'));
        $html = $this->render('index.html.hbs', []);
        $response->end($html);
    }
    
    private function checkController($request, $response)
    {
        $response->writeHead(200, array('Content-Type' => 'text/html'));
        $checkName = $request->getQuery()['checkName'];
        $check = $this->agent->getCheck($checkName);
        $checkResults = $this->agent->getCheckResultsByCheckName($checkName);
        $html = $this->render('check.html.hbs', ['check' => $check, 'checkResults' => $checkResults]);
        $response->end($html);
    }
    
    private function render($templateName, $data, $wrap = true)
    {
        $data['agent'] = $this->agent;
        $template = file_get_contents(__DIR__ . '/../templates/' . $templateName);

        $php = LightnCandy::compile($template, array(
            "flags" => LightnCandy::FLAG_ERROR_EXCEPTION|LightnCandy::FLAG_METHOD
        ));
            
        $renderer = LightnCandy::prepare($php);
        try {
            $html = $renderer($data);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        if ($wrap) {
            $html = $this->render('layout.html.hbs', ['content' => $html], false);
        }
        return $html;
    }
    
    
    public function wrapLayout($content)
    {
        $template = file_get_contents(__DIR__ . '/../templates/layout.html.hbs');
        
        $php = LightnCandy::compile($template, array(
            "flags" => LightnCandy::FLAG_ERROR_EXCEPTION|LightnCandy::FLAG_METHOD
        ));
            
        $renderer = LightnCandy::prepare($php);
        $variables = ['agent' => $this->agent, 'content' => $content];
        try {
            $html = $renderer($variables);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $html;
    }
}
