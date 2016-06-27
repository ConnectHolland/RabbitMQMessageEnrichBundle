<?php

namespace ConnectHolland\RabbitMQMessageEnrichBundle\Consumer\Test;

use ConnectHolland\RabbitMQMessageEnrichBundle\Consumer\EnrichMessageConsumer;
use ConnectHolland\RabbitMQMessageEnrichBundle\Controller\EnrichControllerInterface;
use JMS\Serializer\Serializer;
use Mockery;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit_Framework_TestCase;
use stdClass;

/**
 * Unit test for the message enrich consumer.
 *
 * @author Ron Rademaker
 */
class EnrichMessageConsumerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test enriching a message.
     *
     * @dataProvider provideEnrichTestData
     *
     * @param array $incoming
     * @param bool $shouldEnrich
     */
    public function testEnrichMessage(array $incoming, $shouldEnrich, $chain = [])
    {
        $enriched = new stdClass;
        $enriched->foobar = 'foobar';

        $controllerMock = Mockery::mock(EnrichControllerInterface::class);
        $controllerMock->shouldReceive('getObject')->andReturn($enriched);
        $producerMock = Mockery::mock(ProducerInterface::class);
        $producerMock->shouldReceive('setContentType')->andReturn($producerMock);
        $produced = [];
        $producerMock->shouldReceive('publish')->andReturnUsing(function ($body, $routingKey) use (&$produced) {
            $produced['body'] = $body;
            $produced['routing_key'] = $routingKey;
        });
        $serializer = Mockery::mock(Serializer::class);
        $serializer->shouldReceive('serialize')->andReturn(json_encode($enriched));

        $message = new AMQPMessage(json_encode($incoming));
        $message->delivery_info['routing_key'] = 'baz';

        $consumer = new EnrichMessageConsumer($controllerMock, $producerMock, $serializer, 'foobar_id', 'foobar', $chain);
        $consumer->execute($message);

        if ($shouldEnrich) {
            $this->assertEquals('baz', $produced['routing_key']);
            $this->assertJson($produced['body']);

            $newMessage = json_decode($produced['body']);
            $this->assertTrue(property_exists($newMessage, 'foobar'));
            $this->assertTrue(property_exists($newMessage->foobar, 'foobar'));
            $this->assertEquals('foobar', $newMessage->foobar->foobar);
        } else {
            $this->assertEmpty($produced);
        }
    }

    /**
     * Test not double enriching a message.
     */
    public function provideEnrichTestData()
    {
        return [
            [['foobar_id' => 'foo'], true],
            [['foobar_id' => 'foo'], false, ['foobaz']],
            [['foobar_id' => 'foo', 'foobaz' => 'foobaz'], true, ['foobaz']],
            [['foobar_id' => 'foo', 'foobar' => ['foobar' => 'foobar']], false],
            [['foobar' => ['foobar' => 'foobar']], false]
        ];
    }
}
