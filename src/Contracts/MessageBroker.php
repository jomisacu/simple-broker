<?php
/**
 * @author Jorge Miguel Sanchez Cuello <jomisacu.software@gmail.com>
 *
 * Date: 2021-11-18 22:30
 */

declare(strict_types=1);

namespace Jomisacu\SimpleBroker\Contracts;

interface MessageBroker
{
    public function createTopic(string $topicName): void;

    public function createQueue(string $queueName): void;

    public function bindQueueToTopic(string $topicName, string $queueName): void;

    public function publish(object $event): void;

    public function consume(string $queueName, callable $handler, int $timeout);
}
