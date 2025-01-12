<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Debug;

use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Strings\WildcardPattern;
use Yiisoft\Yii\Console\Event\ApplicationStartup;
use Yiisoft\Yii\Debug\Collector\CollectorInterface;
use Yiisoft\Yii\Debug\Storage\StorageInterface;
use Yiisoft\Yii\Http\Event\BeforeRequest;

final class Debugger
{
    private bool $skipCollect = false;

    public function __construct(
        private DebuggerIdGenerator $idGenerator,
        private StorageInterface $target,
        /**
         * @var CollectorInterface[]
         */
        private array $collectors,
        private array $ignoredRequests = [],
        private array $ignoredCommands = [],
    ) {
    }

    public function getId(): string
    {
        return $this->idGenerator->getId();
    }

    public function startup(object $event): void
    {
        if ($event instanceof BeforeRequest && $this->isRequestIgnored($event->getRequest())) {
            $this->skipCollect = true;
            return;
        }

        if ($event instanceof ApplicationStartup && $this->isCommandIgnored($event->commandName)) {
            $this->skipCollect = true;
            return;
        }

        $this->idGenerator->reset();
        foreach ($this->collectors as $collector) {
            $this->target->addCollector($collector);
            $collector->startup();
        }
    }

    private function isRequestIgnored(ServerRequestInterface $request): bool
    {
        $path = $request->getUri()->getPath();
        foreach ($this->ignoredRequests as $pattern) {
            if ((new WildcardPattern($pattern))->match($path)) {
                return true;
            }
        }
        return false;
    }

    private function isCommandIgnored(?string $command): bool
    {
        if ($command === null || $command === '') {
            return true;
        }
        foreach ($this->ignoredCommands as $pattern) {
            if ((new WildcardPattern($pattern))->match($command)) {
                return true;
            }
        }
        return false;
    }

    public function shutdown(): void
    {
        try {
            if (!$this->skipCollect) {
                $this->target->flush();
            }
        } finally {
            foreach ($this->collectors as $collector) {
                $collector->shutdown();
            }
            $this->skipCollect = false;
        }
    }

    /**
     * @param array $ignoredRequests Patterns for ignored request URLs.
     *
     * @return self
     *
     * @see WildcardPattern
     */
    public function withIgnoredRequests(array $ignoredRequests): self
    {
        $new = clone $this;
        $new->ignoredRequests = $ignoredRequests;
        return $new;
    }

    /**
     * @param array $ignoredCommands Patterns for ignored commands names.
     *
     * @return self
     *
     * @see WildcardPattern
     */
    public function withIgnoredCommands(array $ignoredCommands): self
    {
        $new = clone $this;
        $new->ignoredCommands = $ignoredCommands;
        return $new;
    }
}
