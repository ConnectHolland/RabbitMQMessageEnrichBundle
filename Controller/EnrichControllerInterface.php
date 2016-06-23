<?php

namespace ConnectHolland\RabbitMQMessageEnrichBundle\Controller;

/**
 * Defined the interface for a controller able to provide objects to enrich rabbit messages with.
 *
 * @author Ron Rademaker
 */
interface EnrichControllerInterface
{
    /**
     * Get the object by id.
     *
     * @param string $id
     */
    public function getObject($id);
}
