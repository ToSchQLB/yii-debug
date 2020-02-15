<?php

namespace Yiisoft\Yii\Debug\Collector;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Yiisoft\Yii\Debug\Target\TargetInterface;

class LogCollector implements CollectorInterface, LoggerInterface
{
    private LoggerInterface $logger;
    private array $messages = [];
    /**
     * @var \Yiisoft\Yii\Debug\Target\TargetInterface
     */
    private TargetInterface $target;

    public function __construct(TargetInterface $target, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->target = $target;
    }

    public function export(): void
    {
        $this->target->add($this->messages);
    }

    public function setTarget(TargetInterface $target): void
    {
        $this->target = $target;
    }

    public function emergency($message, array $context = [])
    {
        $this->collectMessages(LogLevel::EMERGENCY, $message, $context);
        $this->logger->emergency($message, $context);
    }

    public function alert($message, array $context = [])
    {
        $this->collectMessages(LogLevel::ALERT, $message, $context);
        $this->logger->alert($message, $context);
    }

    public function critical($message, array $context = [])
    {
        $this->collectMessages(LogLevel::CRITICAL, $message, $context);
        $this->logger->critical($message, $context);
    }

    public function error($message, array $context = [])
    {
        $this->collectMessages(LogLevel::ERROR, $message, $context);
        $this->logger->error($message, $context);
    }

    public function warning($message, array $context = [])
    {
        $this->collectMessages(LogLevel::WARNING, $message, $context);
        $this->logger->warning($message, $context);
    }

    public function notice($message, array $context = [])
    {
        $this->collectMessages(LogLevel::NOTICE, $message, $context);
        $this->logger->notice($message, $context);
    }

    public function info($message, array $context = [])
    {
        $this->collectMessages(LogLevel::INFO, $message, $context);
        $this->logger->info($message, $context);
    }

    public function debug($message, array $context = [])
    {
        $this->collectMessages(LogLevel::DEBUG, $message, $context);
        $this->logger->debug($message, $context);
    }

    public function log($level, $message, array $context = [])
    {
        $this->collectMessages($level, $message, $context);
        $this->logger->log($message, $context);
    }

    private function collectMessages(string $level, string $message, array $context): void
    {
        $this->messages[] = [
            'time' => microtime(true),
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];
    }
}