<?php

namespace Pulpy\Core\Services\Maintenance;

use Doctrine\ORM\EntityManager,
    Doctrine\ORM\Tools\SchemaTool;

class ORMSchemaCreatorService {

    public function createSchema(EntityManager $em) {
        
        $metadatas = $em->getMetadataFactory()->getAllMetadata();
        
        if(!empty($metadatas)) {
            $tool = new SchemaTool($em);
            $tool->createSchema($metadatas);
        }
    }
}