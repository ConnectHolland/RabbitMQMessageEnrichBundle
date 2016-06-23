<?php

namespace ConnectHolland\RabbitMQMessageEnrichBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Controller to get doctrine entities for enriching messages.
 *
 * @author Ron Rademaker
 */
class DoctrineEnrichController implements EnrichControllerInterface
{
    /**
     * Repository to retrieve objects from.
     *
     * @var ObjectRepository
     */
    private $repo;

    /**
     * Creates a new DoctrineEnrichController to fetch $entity objects.
     *
     * @param Registry $doctrine
     * @param string $entity
     */
    public function __construct(Registry $doctrine, $entity)
    {
        $this->repo = $doctrine->getRepository($entity);
    }

    /**
     * Retrieve the object with $id.
     *
     * @param type $id
     *
     * @return object
     */
    public function getObject($id)
    {
        return $this->repo->find($id);
    }

}
