<?php

namespace Mozza\Core\Services;

use Doctrine\ORM\EntityManager;

use Mozza\Core\Entity\SystemStatus;

class SystemStatusService {

    protected $em;
    protected $systemstatus;

    public function __construct(EntityManager $em) {
        $this->em = $em;

        # Initialize system status if needed
        $results = $this->em->getRepository('Mozza\Core\Entity\SystemStatus')->findAll();

        if(!empty($results)) {
            $this->systemstatus = $results[0];
            return;
        }

        # We have to create the system status line
        $systemstatus = new SystemStatus();
        $this->em->persist($systemstatus);
        $this->em->flush($systemstatus);

        # Initialize system status if needed
        $results = $this->em->getRepository('Mozza\Core\Entity\SystemStatus')->findAll();
        $this->systemstatus = $results[0];
    }

    public function getPostCacheLastUpdate() {
        return $this->systemstatus->getPostcachelastupdate();
    }

    public function setPostCacheLastUpdate(\DateTime $postlastcacheupdate) {
        $this->systemstatus->setPostcachelastupdate($postlastcacheupdate);
        $this->em->persist($this->systemstatus);
        $this->em->flush();
    }
}