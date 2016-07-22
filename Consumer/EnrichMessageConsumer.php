<?php

namespace ConnectHolland\RabbitMQMessageEnrichBundle\Consumer;

use ConnectHolland\RabbitMQMessageEnrichBundle\Controller\EnrichControllerInterface;
use ConnectHolland\RabbitMQMessageEnrichBundle\Util\PropertyAccessor;
use JMS\Serializer\Serializer;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use stdClass;

/**
 * Consumer that can enrich a message with profile information.
 *
 * @author Ron Rademaker
 */
class EnrichMessageConsumer implements ConsumerInterface
{
    /**
     * Controller that can find an object to enrich with.
     *
     * @var EnrichControllerInterface
     */
    private $controller;

    /**
     * Producer to produce the enriched message.
     *
     * @var Producer
     */
    private $producer;

     /**
     * The serializer is description.
     *
     * @var Serializer
     */
    private $serializer;

    /**
     * Field where the id to be enriched is expected.
     *
     * @var string
     */
    private $idField;

    /**
     * Field where the enrichted object is stored.
     *
     * @var string
     */
    private $objectField;

    /**
     * Chain that should be enriched before enriching.
     *
     * @var array
     */
    private $chain;

    /**
     * Create a new EnrichMessageConsumer.
     *
     * @param EnrichControllerInterface $controller
     * @param ProducerInterface $producer
     * @param Serializer $serializer
     * @param string $idField
     * @param string $objectField
     * @param array $chain
     */
    public function __construct(
        EnrichControllerInterface $controller,
        ProducerInterface $producer,
        Serializer $serializer,
        $idField,
        $objectField,
        array $chain = []
    ) {
        $this->setController($controller);
        $this->setProducer($producer);
        $this->setSerializer($serializer);
        $this->idField = $idField;
        $this->objectField = $objectField;
        $this->chain = $chain;
    }

    /**
     * Getter for the controller.
     *
     * @return EnrichControllerInterface
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Setter for the controller.
     *
     * @param  EnrichControllerInterface $controller
     *
     * @return EnrichControllerInterface
     */
    public function setController(EnrichControllerInterface $controller)
    {
        $this->controller = $controller;

        return $this;
    }

     /**
     * Getter for the producer
     *
     * @return ProducerInterface
     */
    public function getProducer()
    {
        return $this->producer;
    }

    /**
     * Setter for the producer.
     *
     * @param  ProducerInterface $producer
     *
     * @return EnrichControllerInterface
     */
    public function setProducer(ProducerInterface $producer)
    {
        $this->producer = $producer;

        return $this;
    }

    /**
     * Getter for the serializer.
     *
     * @return Serializer
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * Setter for the serializer.
     *
     * @param  Serializer serializer
     */
    public function setSerializer(Serializer $serializer)
    {
        $this->serializer = $serializer;

        return $this;
    }

    /**
     * Enriches the message if not yet enriched.
     *
     * @param AMQPMessage $message
     *
     * @return int
     */
    public function execute(AMQPMessage $message)
    {
        if (array_key_exists('routing_key', $message->delivery_info) && false !== ($body = $this->parseBody($message))) {
            $propertyAccessor = new PropertyAccessor($body);
            if ($propertyAccessor->exists($this->idField) && $this->shouldEnrich($propertyAccessor)) {
                $this->enrichMessage($propertyAccessor, $message->delivery_info['routing_key']);
            }

            return ConsumerInterface::MSG_ACK;
        }

        return ConsumerInterface::MSG_SINGLE_NACK_REQUEUE;
    }

    /**
     * Returns if the message should be enriched.
     *
     * @param PropertyAccessor $propertyAccessor
     * @return bool
     */
    private function shouldEnrich(PropertyAccessor $propertyAccessor)
    {
        $shouldEnrich = !$propertyAccessor->exists($this->objectField);

        foreach ($this->chain as $chainField) {
            $shouldEnrich = $shouldEnrich && $propertyAccessor->exists($chainField);
        }

        return $shouldEnrich;
    }

    /**
     * Parses the body.
     *
     * @param AMQPMessage $message
     *
     * @return stdClass|false
     */
    private function parseBody(AMQPMessage $message)
    {
        $body = json_decode($message->body);
        if (false !== $body) {
            return $body;
        }

        return false;
    }

    /**
     * Enrich the message and put it on the queue.
     *
     * @param PropertyAccessor $propertyAccessor
     * @param string $routingKey
     */
    private function enrichMessage(PropertyAccessor $propertyAccessor, $routingKey)
    {
        $object = $this->getController()->getObject($propertyAccessor->get($this->idField));
        if (is_object($object)) {
            $propertyAccessor->set($this->objectField, json_decode($this->serializer->serialize($object, 'json')));

            $this->getProducer()->setContentType('application/json')
                ->publish(json_encode($propertyAccessor->getObject()), $routingKey);
        }
    }
}
