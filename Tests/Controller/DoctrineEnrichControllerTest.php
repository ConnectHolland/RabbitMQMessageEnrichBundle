<?php

namespace ConnectHolland\RabbitMQMessageEnrichBundle\Controller\Test;

use ConnectHolland\RabbitMQMessageEnrichBundle\Controller\DoctrineEnrichController;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectRepository;
use Mockery;
use PHPUnit_Framework_TestCase;

/**
 * Unit test for the enrich controller.
 *
 * @author Ron Rademaker
 */
class DoctrineEnrichControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests getting something.
     */
    public function testGetObject()
    {
        $registryMock = Mockery::mock(Registry::class);
        $repoMock = Mockery::mock(ObjectRepository::class);
        $repoMock->shouldReceive('find')->andReturn('foobar');
        $registryMock->shouldReceive('getRepository')->andReturn($repoMock);

        $controller = new DoctrineEnrichController($registryMock, 'FooBarBundle:FooBar');

        $this->assertEquals('foobar', $controller->getObject('hello world'));
    }
}
