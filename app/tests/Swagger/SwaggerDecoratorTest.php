<?php

declare(strict_types=1);

namespace App\Tests\Swagger;

use App\Swagger\SwaggerDecorator;
use ArrayObject;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Yaml\Parser;

class SwaggerDecoratorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private const CONFIG_FILE_PATH = __DIR__ . '/../config/';

    private const DEFAULT_DOCS = [
        'basePath' => '/',
        'info' => ['title' => 'Default Doc', 'version' => '1.0']
    ];

    /** @var NormalizerInterface|\Mockery\MockInterface */
    private $defaultDecorator;
    
    /** @var ParameterBagInterface|\Mockery\MockInterface */
    private $params;

    public function setUp(): void
    {
        $this->defaultDecorator = Mockery::mock(NormalizerInterface::class);
        $this->params = Mockery::mock(ParameterBagInterface::class);
    }

    public function testNormalizeMergeConfigIntoDoc(): void
    {
        $swaggerDecorator = new SwaggerDecorator(
            $this->defaultDecorator,
            self::CONFIG_FILE_PATH . 'swagger.yaml',
            new Parser(),
            $this->params
        );

        $object = ['some' => 'data'];

        $this->defaultDecorator->shouldReceive('normalize')
            ->with($object, null, [])
            ->andReturn(self::DEFAULT_DOCS);

        $expected = ['basePath' => '/', 'info' => ['title' => 'Test doc']];

        $this->assertSame($expected, $swaggerDecorator->normalize($object));
    }

    public function testNormalizeMergeConfigIntoDocAndRemoveWildcard(): void
    {
        $swaggerDecorator = new SwaggerDecorator(
            $this->defaultDecorator,
            self::CONFIG_FILE_PATH . 'wildcard.yaml',
            new Parser(),
            $this->params
        );

        $object = ['some' => 'data'];

        $docs = [
            'basePath' => '/',
            'info' => [
                'wildcardKey' => 'test1',
                'wildcardKeyAndSomething' => 'test2',
                'NO_MATCH_WITH_WILDCARD' => 'Docs title'
            ]
        ];
        $this->defaultDecorator->shouldReceive('normalize')
            ->with($object, null, [])
            ->andReturn($docs);

        $expected = ['basePath' => '/', 'info' => ['NO_MATCH_WITH_WILDCARD' => 'Docs title']];
        $swaggerDecorator->normalize($object);
        $this->assertSame($expected, $swaggerDecorator->normalize($object));
    }

    public function testNormalizeEmptyConfig(): void
    {
        $swaggerDecorator = new SwaggerDecorator(
            $this->defaultDecorator,
            self::CONFIG_FILE_PATH . 'empty.yaml',
            new Parser(),
            $this->params
        );

        $object = ['some' => 'data'];

        $this->defaultDecorator->shouldReceive('normalize')
            ->with($object, null, [])
            ->andReturn(self::DEFAULT_DOCS);

        $this->assertSame(self::DEFAULT_DOCS, $swaggerDecorator->normalize($object));
    }

    public function testNormalizeAddsNewEntities(): void
    {
        $swaggerDecorator = new SwaggerDecorator(
            $this->defaultDecorator,
            self::CONFIG_FILE_PATH . 'new-endpoint.yaml',
            new Parser(),
            $this->params
        );

        $object = ['some' => 'data'];
        $defaultDocs = self::DEFAULT_DOCS;
        $defaultDocs['paths'] = new ArrayObject(['foo' => 'bar', 'bar' => 'baz']);

        $this->defaultDecorator->shouldReceive('normalize')
            ->with($object, null, [])
            ->andReturn($defaultDocs);

        $expected = self::DEFAULT_DOCS;
        $expected['paths'] = new ArrayObject([
            'bar' => 'baz',
            'foo' => 'bar',
            'new-endpoint' => [
                'get' => [
                    'tags' => ['Mock'],
                ],
            ],
        ]);

        $this->assertEquals($expected, $swaggerDecorator->normalize($object));
    }

    public function testNormalizeSortArrayObjectList(): void
    {
        $swaggerDecorator = new SwaggerDecorator(
            $this->defaultDecorator,
            self::CONFIG_FILE_PATH . 'new-endpoint.yaml',
            new Parser(),
            $this->params
        );

        $object = ['some' => 'data'];
        $defaultDocs = self::DEFAULT_DOCS;
        $defaultDocs['paths'] = new ArrayObject(['foo' => 'bar', 'bar' => 'baz']);

        $this->defaultDecorator->shouldReceive('normalize')
            ->with($object, null, [])
            ->andReturn($defaultDocs);

        $actual = $swaggerDecorator->normalize($object);
        $expected = ['bar', 'foo', 'new-endpoint'];

        $this->assertSame($expected, array_keys($actual['paths']->getArrayCopy()));
    }

    public function testNormalizeSortsNativeArrayList(): void
    {
        $swaggerDecorator = new SwaggerDecorator(
            $this->defaultDecorator,
            self::CONFIG_FILE_PATH . 'new-endpoint.yaml',
            new Parser(),
            $this->params
        );

        $object = ['some' => 'data'];
        $defaultDocs = self::DEFAULT_DOCS;
        $defaultDocs['paths'] = ['foo' => 'bar', 'bar' => 'baz'];

        $this->defaultDecorator->shouldReceive('normalize')
            ->with($object, null, [])
            ->andReturn($defaultDocs);

        $actual = $swaggerDecorator->normalize($object);
        $expected = ['bar', 'foo', 'new-endpoint'];

        $this->assertSame($expected, array_keys($actual['paths']));
    }

    public function testSupportsNormalization(): void
    {
        $swaggerDecorator = new SwaggerDecorator(
            $this->defaultDecorator,
            self::CONFIG_FILE_PATH . 'swagger.yaml',
            new Parser(),
            $this->params
        );
        $data = ['some' => 'data'];
        $format = 'format';

        $this->defaultDecorator->shouldReceive('supportsNormalization')
            ->with($data, $format)
            ->andReturn(true);

        $this->assertTrue($swaggerDecorator->supportsNormalization($data, $format));
    }
}
