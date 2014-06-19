<?php

namespace Mozza\Core\Provider;

use Silex\Application,
    Silex\ServiceProviderInterface;

use Igorw\Silex\ConfigServiceProvider,
    Igorw\Silex\ChainConfigDriver,
    Igorw\Silex\PhpConfigDriver,
    Igorw\Silex\YamlConfigDriver,
    Igorw\Silex\JsonConfigDriver,
    Igorw\Silex\TomlConfigDriver;

class FileBasedConfigServiceProvider implements ServiceProviderInterface {
    
    private $filename;
    private $replacements = array();
    private $driver;

    public function __construct($filename, array $replacements = array(), $prefix = null)
    {
        $this->filename = $filename;
        $this->prefix = $prefix;

        if ($replacements) {
            foreach ($replacements as $key => $value) {
                $this->replacements['%'.$key.'%'] = $value;
            }
        }

        $this->driver = new ChainConfigDriver(array(
            new PhpConfigDriver(),
            new YamlConfigDriver(),
            new JsonConfigDriver(),
            new TomlConfigDriver(),
        ));
    }

    public function getAsArray() {
        $res = array();

        $config = $this->readConfig();
        if($this->prefix) {
            $config = array($this->prefix => $config);
        }

        foreach ($config as $name => $value)
            if ('%' === substr($name, 0, 1))
                $this->replacements[$name] = (string) $value;

        $this->merge($res, $config);

        return $res;
    }

    public function register(Application $app)
    {
        $config = $this->readConfig();
        if($this->prefix) {
            $config = array($this->prefix => $config);
        }

        foreach ($config as $name => $value)
            if ('%' === substr($name, 0, 1))
                $this->replacements[$name] = (string) $value;

        $this->merge($app, $config);
    }

    public function boot(Application $app)
    {
    }

    protected function merge(&$app, array $config)
    {
        foreach ($config as $name => $value) {
            if (isset($app[$name]) && is_array($value)) {
                $app[$name] = $this->mergeRecursively($app[$name], $value);
            } else {
                $app[$name] = $this->doReplacements($value);
            }
        }
    }

    protected function mergeRecursively(array $currentValue, array $newValue)
    {
        foreach ($newValue as $name => $value) {
            if (is_array($value) && isset($currentValue[$name])) {
                $currentValue[$name] = $this->mergeRecursively($currentValue[$name], $value);
            } else {
                $currentValue[$name] = $this->doReplacements($value);
            }
        }

        return $currentValue;
    }

    protected function doReplacements($value)
    {
        if (!$this->replacements) {
            return $value;
        }

        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->doReplacements($v);
            }

            return $value;
        }

        if (is_string($value)) {
            return strtr($value, $this->replacements);
        }

        return $value;
    }

    protected function readConfig()
    {
        if (!$this->filename) {
            throw new \RuntimeException('A valid configuration file must be passed before reading the config.');
        }

        if (!file_exists($this->filename)) {
            throw new \InvalidArgumentException(
                sprintf("The config file '%s' does not exist.", $this->filename));
        }

        if ($this->driver->supports($this->filename)) {
            return $this->driver->load($this->filename);
        }

        throw new \InvalidArgumentException(
                sprintf("The config file '%s' appears to have an invalid format.", $this->filename));
    }
}