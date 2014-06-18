<?php

namespace Mozza\Core\Services;

use Silex\Application,
    Silex\Provider\DoctrineServiceProvider;

use Habitat\Habitat;

class EnvironmentService {

    protected $databasedsn;

    public function __construct(DatabaseUrlResolverService $databaseurlresolver, ConfigLoaderService $configloader, CultureService $culture) {

        $this->culture = $culture;

        if(($databaseurl = Habitat::getenv('DATABASE_URL')) === FALSE) {
            throw new \UnexpectedValueException('DATABASE_URL is not set in environment.');
        }

        $this->databasedsn = $databaseurlresolver->resolve($databaseurl);

        if(($configurl = Habitat::getenv('CONFIG_URL')) === FALSE) {
            throw new \UnexpectedValueException('CONFIG_URL is not set in environment.');
        }

        $this->configarray = $configloader->load($configurl);

        #var_dump($this->databasedsn);
        #debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    }

    public function initialize(Application $app) {
        #
        # Culture
        #

        date_default_timezone_set($this->culture->getTimezone()->getName());
        setlocale(LC_ALL, $this->culture->getLocale());

        #
        # Database connection
        #
        $app->register(new DoctrineServiceProvider, array(
            'db.options' => $this->databasedsn,
        ));
    }
}