# ConnectHolland Rabbit MQ Message Enrich Bundle

## Installation

```
composer require connectholland/rabbit-mq-message-bundle
```

Do no add the bundle to your AppKernel, there is no bundle class (required).

## Configuration

* Configure producer(s) to put messages on the queue as documented at: https://github.com/php-amqplib/RabbitMqBundle
* Configure consumers to listen for messages as documented at: https://github.com/php-amqplib/RabbitMqBundle
* Configure the service for the consumer to be of type ```ConnectHolland\RabbitMQMessageEnrichBundle\Consumer\EnrichMessageConsumer```
* Configure the JMS serializer to be able to serialize the object you enrich with: https://github.com/schmittjoh/serializer

Example to enrich messages with a ```user_id``` with the information from ```FoobarBundle:User``` in ```user``` listening and publishing on a channel ```foo``` for messages with routing_key ```bar```.

config.yml

``` yml
old_sound_rabbit_mq:
    connections:
        default:
            ...
    consumers:
        enrich_message:
            connection:       default
            exchange_options: {name: 'foo', type: direct, durable: false}
            queue_options:    {name: '', durable: false, auto_delete: true, exclusive: true, routing_keys:  ['bar']}
            callback:         foobar_consumer
    producers:
        foobar_producer:
            exchange_options: {name: 'foo', type: direct, durable: false}
            queue_options: {name: '', durable: false, auto_delete: true, exclusive: true}
```

services.yml
``` yml
services:
    userController:
        class: ConnectHolland\RabbitMQMessageEnrichBundle\Controller\DoctrineEnrichController
        arguments: ["@doctrine", "FoobarBundle:User"]
    foobar_consumer:
        class: ConnectHolland\RabbitMQMessageEnrichBundle\Consumer\EnrichMessageConsumer
        arguments: ["@userController", "@old_sound_rabbit_mq.foobar_producer_producer", "@jsm_serializer", "user_id", "user"]
```

Enitity.User.yml
``` yml
FoobarBundle\Entity\User:
    exclusion_policy: ALL
    properties:
        id:
            expose: true
        user_id:
            expose: true
        email:
            expose: true
        firstName:
            expose: true
        middleName:
            expose: true
        lastName:
            expose: true

```
