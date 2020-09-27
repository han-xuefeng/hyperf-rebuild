<?php


namespace Rebuild\Config;

use Symfony\Component\Finder\Finder;

class ConfigFactory
{
    public function __invoke()
    {
        $configPath = BASE_PATH . '/config';
        $config = $this->readConfig($configPath . '/config.php');
        $autoloadConfig = $this->readPaths([BASE_PATH . '/config/autoload']);
//        $merged = array_merge_recursive(ProviderConfig::load(), $config, ...$autoloadConfig);
        $merged = array_merge_recursive($config,$autoloadConfig);
        return new Config($merged);
    }

    public function readConfig(string $configPath): array
    {
        $config = [];
        if(file_exists($configPath) && is_readable($configPath)){
            $config = require $configPath;
        }

        return is_array($config) ? $config : [];
    }

    private function readPaths(array $paths)
    {
        $configs = [];
        $finder = new Finder();
        $finder->files()->in($paths)->name('*.php');
        foreach ($finder as $file) {
            $configs[$file->getBasename('.php')] = require $file->getRealPath();
        }
        return $configs;
    }
}