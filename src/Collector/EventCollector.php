<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Debug\Collector;

use ReflectionClass;
use Yiisoft\Yii\Console\Event\ApplicationStartup as ConsoleApplicationStartup;
use Yiisoft\Yii\Http\Event\ApplicationStartup as HttpApplicationStartup;

use function get_class;

class EventCollector implements CollectorInterface, IndexCollectorInterface
{
    use CollectorTrait;

    private array $events = [];

    public function getCollected(): array
    {
        return $this->events;
    }

    public function collect(object $event, string $line): void
    {
        if (
            !$event instanceof HttpApplicationStartup
            && !$event instanceof ConsoleApplicationStartup
            && !$this->isActive()
        ) {
            return;
        }

        $this->collectEvent($event, $line);
    }

    private function collectEvent(object $event, $line): void
    {
        $this->events[] = [
            'name' => get_class($event),
            'event' => $event,
            'file' => (new ReflectionClass($event))->getFileName(),
            'line' => $line,
            'time' => microtime(true),
        ];
    }

    public function getIndexData(): array
    {
        return [
            'event' => [
                'total' => count($this->events),
            ],
        ];
    }

    private function reset(): void
    {
        $this->events = [];
    }
}
