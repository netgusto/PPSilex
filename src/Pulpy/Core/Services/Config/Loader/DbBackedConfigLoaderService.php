<?php

namespace Pulpy\Core\Services\Config\Loader;

use Doctrine\ORM\EntityManager;

class DbBackedConfigLoaderService extends AbstractConfigLoaderService {

    protected $em;
    protected $parameters;

    public function __construct(EntityManager $em, $parameters) {
        $this->em = $em;
        $this->parameters = $this->prepareParameters($parameters);
    }

    public function load($configname) {
        
        $configEntity = $this->em->getRepository('Pulpy\Core\Entity\HierarchicalConfig')->findOneByName($configname);
        
        if(!$configEntity) {
            return null;
        }

        $config = $configEntity->getConfig();
        if(is_null($config)) {
            return null;
        }

        $keys = array_keys($config);
        foreach($keys as $key) {
            $config[$key] = $this->doReplacements($config[$key]);
        }

        return $config;
    }
}