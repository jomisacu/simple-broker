<?php
/**
 * @author Jorge Miguel Sanchez Cuello <jomisacu.software@gmail.com>
 *
 * Date: 2021-11-18 22:52
 */

declare(strict_types=1);

namespace Jomisacu\SimpleBroker;

use Enqueue\SnsQs\SnsQsContext;
use Interop\Queue\Message;
use Jomisacu\SimpleBroker\Contracts\MessageBroker;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class MessageBrokerQueueSnsqs implements MessageBroker
{
    /**
     * @var \Enqueue\SnsQs\SnsQsTopic|\Interop\Queue\Topic
     */
    protected $topic;
    protected $topicName;
    /**
     * @var SnsQsContext
     */
    private $context;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SnsQsContext $context, string $topicName)
    {
        $this->context = $context;
        $this->topicName = $topicName;
        $this->serializer = $this->buildSerializer();
    }

    public function createTopic(string $topicName): void
    {
        $topic = $this->context->createTopic($topicName);
        $this->context->declareTopic($topic);
    }

    public function createQueue(string $queueName): void
    {
        $queue = $this->context->createQueue($queueName);
        $this->context->declareQueue($queue);
    }

    public function bindQueueToTopic(string $topicName, string $queueName): void
    {
        $topic = $this->context->createTopic($topicName);
        $queue = $this->context->createQueue($queueName);

        $this->context->bind($topic, $queue);
    }

    public function publish(object $event): void
    {
        $message = $this->context->createMessage(
            $this->serialize($event),
            ['fqcn' => get_class($event)]
        );

        $this->context->createProducer()->send($this->getTopic(), $message);
    }

    public function getTopic()
    {
        if (null == $this->topic) {
            $this->topic = $this->context->createTopic($this->topicName);
        }

        return $this->topic;
    }

    public function consume(string $queueName, callable $handler, int $timeout = 0)
    {
        $queue = $this->context->createQueue($queueName);
        $consumer = $this->context->createConsumer($queue);

        $message = $consumer->receive($timeout);
        $originalEvent = $this->deserialize($message);

        $wrapped = function () use ($handler, $originalEvent) {
            call_user_func($handler, $originalEvent);
        };

        $wrapped();
        $consumer->acknowledge($message);
    }

    private function serialize(object $event)
    {
        return $this->serializer->serialize($event, 'json');
    }

    private function deserialize(Message $message)
    {
        return $this->serializer->deserialize($message->getBody(), $message->getProperty('fqcn'), 'json');
    }

    private function buildSerializer(): SerializerInterface
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        return new Serializer($normalizers, $encoders);
    }
}
