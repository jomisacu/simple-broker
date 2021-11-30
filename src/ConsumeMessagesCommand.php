<?php
/**
 * @author Jorge Miguel Sanchez Cuello <jomisacu.software@gmail.com>
 *
 * Date: 2021-11-19 11:48
 */

declare(strict_types=1);

namespace Jomisacu\SimpleBroker;

use Jomisacu\SimpleBroker\Contracts\Dispatcher;
use Jomisacu\SimpleBroker\Contracts\MessageBroker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConsumeMessagesCommand extends Command
{
    /**
     * @var MessageBroker
     */
    private $messageBroker;
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    public function __construct(MessageBroker $messageBroker, Dispatcher $dispatcher)
    {
        parent::__construct();

        $this->messageBroker = $messageBroker;
        $this->dispatcher = $dispatcher;
    }

    protected function configure()
    {
        $this->setName('simple-broker:consume-messages');
        $this->addArgument('queue', InputArgument::REQUIRED, 'The queue from the messages comes');
        $this->addOption('timeout', '', InputOption::VALUE_REQUIRED, 'The timeout for this execution');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startTime = time();
        $endTime = $startTime + ($input->getOption('timeout') ?: 10 * 60);

        while (true) {
            $this->messageBroker->consume($input->getArgument('queue'), function ($originalEvent) use ($input, $output) {
                $eventClass = get_class($originalEvent);
                $output->writeln(sprintf('Consuming event %s from the queue %s', $eventClass, $input->getArgument('queue')));
                $output->writeln(sprintf('handle the event %s...', $eventClass));
                $this->dispatcher->dispatch($originalEvent);
                $output->writeln(sprintf('Event %s the handled!', $eventClass));
            }, 0);

            if (time() >= $endTime) {
                return 0;
            }
        }
    }
}
