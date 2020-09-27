<?php

! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));

require './vendor/autoload.php';

use Symfony\Component\Console\Application;
use Rebuild\Config\ConfigFactory;
use Rebuild\Command\StartCommand;

$application = new Application();

// ... register commands / 注册命令

$config = new ConfigFactory();
$config = $config();
$commands = $config->get('commands');

foreach ($commands as $command){
    if($command === StartCommand::class){
        $application->add(new StartCommand($config));
    }else{
        $application->add(new $command);
    }
}

$application->run();