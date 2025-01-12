<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Debug\Tests\Collector;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Yiisoft\Yii\Debug\Collector\CollectorInterface;
use Yiisoft\Yii\Debug\Collector\RequestCollector;
use Yiisoft\Yii\Http\Event\AfterRequest;
use Yiisoft\Yii\Http\Event\BeforeRequest;

final class RequestCollectorTest extends CollectorTestCase
{
    /**
     * @param CollectorInterface|RequestCollector $collector
     */
    protected function collectTestData(CollectorInterface $collector): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $uriMock = $this->createMock(UriInterface::class);
        $bodyMock = $this->createMock(StreamInterface::class);

        $uriMock->method('getPath')
            ->willReturn('url');
        $uriMock->method('getQuery')
            ->willReturn('');
        $uriMock->method('__toString')
            ->willReturn('http://test.site/url');

        $requestMock->method('getMethod')
            ->willReturn('GET');
        $requestMock->method('getHeaderLine')
            ->willReturn('');
        $requestMock->method('getUri')
            ->willReturn($uriMock);

        $responseMock->method('getStatusCode')
            ->willReturn(200);
        $responseMock->method('getBody')
            ->willReturn($bodyMock);

        $collector->collect(new BeforeRequest($requestMock));
        $collector->collect(new AfterRequest($responseMock));
    }

    protected function getCollector(): CollectorInterface
    {
        return new RequestCollector();
    }

    protected function checkCollectedData(CollectorInterface $collector): void
    {
        parent::checkCollectedData($collector);
        $this->assertInstanceOf(ServerRequestInterface::class, $collector->getCollected()['request']);
        $this->assertInstanceOf(ResponseInterface::class, $collector->getCollected()['response']);
    }

    protected function checkIndexData(CollectorInterface $collector): void
    {
        parent::checkIndexData($collector);
        if ($collector instanceof RequestCollector) {
            $data = $collector->getIndexData();

            $this->assertEquals('http://test.site/url', $data['request']['url']);
            $this->assertEquals('GET', $data['request']['method']);
            $this->assertEquals(200, $data['response']['statusCode']);
        }
    }
}
