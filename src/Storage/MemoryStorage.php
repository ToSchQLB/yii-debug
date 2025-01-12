<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Debug\Storage;

use Yiisoft\Yii\Debug\Collector\CollectorInterface;
use Yiisoft\Yii\Debug\DebuggerIdGenerator;

final class MemoryStorage implements StorageInterface
{
    private DebuggerIdGenerator $idGenerator;
    /**
     * @var CollectorInterface[]
     */
    private array $collectors = [];

    public function __construct(DebuggerIdGenerator $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    public function addCollector(CollectorInterface $collector): void
    {
        $this->collectors[$collector->getName()] = $collector;
    }

    public function read($type = self::TYPE_INDEX): array
    {
        if ($type === self::TYPE_INDEX) {
            return [$this->idGenerator->getId() => $this->getData()];
        }

        return $this->getData();
    }

    public function getData(): array
    {
        $data = [];

        foreach ($this->collectors as $name => $collector) {
            $data[$name] = $collector->getCollected();
        }

        return $data;
    }

    public function flush(): void
    {
        $this->collectors = [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function clear(): void
    {
    }
}
