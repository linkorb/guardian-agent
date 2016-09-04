<?php
namespace Guardian\Agent\Command;

use RuntimeException;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Guardian\Agent\Loader\YamlLoader;

class RunCommand extends Command
{
    public function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Run agent')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = __DIR__ . '/../../guardian.yml';
        
        $loader = new YamlLoader();
        $data = $loader->loadYaml($filename);
        $agent = $loader->loadAgent($data);
        
        $output->writeln('Running Guardian Agent');
        print_r($agent);
        $agent->run();
    }
}
