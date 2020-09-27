<?php


namespace Rebuild\Command;

use Rebuild\Server\ServerFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StartCommand extends Command
{
    private $config;

    public function __construct($config)
    {
        parent::__construct();
        $this->config = $config;
    }

    protected function configure()
    {
        $this->setName('start')->setDescription('启动服务');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configs = $this->config->get('server');
        $serverFactory = new ServerFactory();
        $serverFactory->configure($configs);
        $server = $serverFactory -> getServer();
        $server->start();
        return 1;
    }
}