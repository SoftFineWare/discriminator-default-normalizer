<?php
declare(strict_types=1);

namespace Tests;

use Psr\Log\LoggerInterface;
use SoftFineWare\SerializerDiscriminatorDefault\DiscriminatorDefaultNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorMapping;
use Symfony\Component\Serializer\Mapping\ClassMetadataInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Tests\DTO\ARequest;
use Tests\DTO\BaseRequest;
use Tests\DTO\DefaultRequest;
use Tests\DTO\NotSupportedMissingDefaultAttribute;

/**
 * @covers \SoftFineWare\SerializerDiscriminatorDefault\DiscriminatorDefaultNormalizer
 */
class DiscriminatorDefaultNormalizerTest extends TestCase
{
    private DiscriminatorDefaultNormalizer $normalizer;
    /**
     * @var ClassMetadataFactoryInterface&MockObject
     */
    private ClassMetadataFactoryInterface $classMetadataFactory;
    /**
     * @var ObjectNormalizer&MockObject
     */
    private ObjectNormalizer $objectNomalizer;
    private CamelCaseToSnakeCaseNameConverter $nameConverter;
    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->classMetadataFactory = $this->createMock(ClassMetadataFactoryInterface::class);
        $this->objectNomalizer = $this->createMock(ObjectNormalizer::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->normalizer = new DiscriminatorDefaultNormalizer(
            $this->classMetadataFactory,
            $this->objectNomalizer,
            logger: $this->logger,
        );
        $this->nameConverter = new CamelCaseToSnakeCaseNameConverter();

        parent::setUp(); // TODO: Change the autogenerated stub
    }

    public function getSupportCases(): \Generator
    {
        yield [true, BaseRequest::class];
        yield [false, NotSupportedMissingDefaultAttribute::class];
    }

    private function getNormalizerWithNameConverter():DiscriminatorDefaultNormalizer
    {
        return new DiscriminatorDefaultNormalizer(
            $this->classMetadataFactory,
            $this->objectNomalizer,
            $this->nameConverter,
        );
    }


    /**
     * @dataProvider getSupportCases
     * @param class-string $type
     */
    public function testItShouldSupportNormilizingClassWithDiscriminatorDefaultAttribute(bool $supported, string $type): void
    {
        $this->classMetadataFactory->method('hasMetadataFor')->willReturn(true);
        /** @noinspection PhpInternalEntityUsedInspection */
        $this->classMetadataFactory->method('getMetadataFor')->willReturn(
            $metadata = $this->createMock(ClassMetadataInterface::class)
        );
        $metadata->method('getReflectionClass')->willReturn(new \ReflectionClass($type));
        $metadata->method('getClassDiscriminatorMapping')->willReturn(
            $this->createMock(ClassDiscriminatorMapping::class)
        );
        self::assertSame(
            $supported,
            $this->normalizer->supportsDenormalization([], $type, 'json', [])
        );
    }


    public function testItShouldNotSupportClassWithMissingMetadata(): void
    {
        $this->classMetadataFactory->method('hasMetadataFor')->willReturn(false);
        self::assertFalse(
            $this->normalizer->supportsDenormalization([], 'ClassWithMissingMetadata', 'json', [])
        );

    }

    public function testItShouldNotSupportClassWithNotDiscriminatorMapping(): void
    {
        $this->classMetadataFactory->method('hasMetadataFor')->willReturn(true);
        /** @noinspection PhpInternalEntityUsedInspection */
        $this->classMetadataFactory->method('getMetadataFor')->willReturn(
            $metadata = $this->createMock(ClassMetadataInterface::class)
        );
        $metadata->method('getClassDiscriminatorMapping')
            ->willReturn(null);
        self::assertFalse(
            $this->normalizer->supportsDenormalization([], 'ClassWithMissingMetadata', 'json', [])
        );
    }


    public function testItShouldUseObjectNormalizerWhenClassDiscriminatorMappingNotDefined():void
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        $this->classMetadataFactory->method('getMetadataFor')->willReturn(
            $metadata = $this->createMock(ClassMetadataInterface::class)
        );
        $object = new class() {};
        $metadata->method('getReflectionClass')->willReturn(new \ReflectionClass($object::class));
        $metadata->method('getClassDiscriminatorMapping')->willReturn(null);
        $arguments = [[], $object::class, 'json', []];
        $this->objectNomalizer->method('denormalize')->with(...$arguments)->willReturn($object);
        self::assertSame(
            $object,
            $this->normalizer->denormalize(
                ...$arguments
            )
        );
    }

    public function testItShouldUseObjectNormalizerWhenClassDiscriminatorMappingHasTypeFromData():void
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        $this->classMetadataFactory->method('getMetadataFor')->willReturn(
            $metadata = $this->createMock(ClassMetadataInterface::class)
        );
        $typePropertyName = 'type';
        $existedType = 'definedTypeInDiscriminator';
        $object = new class($existedType) {
            public function __construct(
                public readonly string $type
            ) {
            }
        };
        $metadata->method('getReflectionClass')->willReturn(new \ReflectionClass($object::class));
        $metadata->method('getClassDiscriminatorMapping')->willReturn(
            new ClassDiscriminatorMapping($typePropertyName, ['definedTypeInDiscriminator' => $object::class])
        );
        $arguments = [[$typePropertyName => $existedType], $object::class, 'json', []];
        $this->objectNomalizer->method('denormalize')->with(...$arguments)->willReturn($object);
        self::assertSame(
            $object,
            $this->normalizer->denormalize(
                ...$arguments
            )
        );
    }


    public function testItShouldUseObjectNormalizerWithDefaultTypeWhenDiscriminatorDefaultAttributeExist():void
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        $this->classMetadataFactory->method('getMetadataFor')->willReturn(
            $metadata = $this->createMock(ClassMetadataInterface::class)
        );
        $typePropertyName = 'type';
        $notExistedType = 'NOTDefinedTypeInDiscriminator';

        $class = BaseRequest::class;
        $metadata->method('getReflectionClass')->willReturn(new \ReflectionClass($class));
        $metadata->method('getClassDiscriminatorMapping')->willReturn(
            new ClassDiscriminatorMapping($typePropertyName, ['a' => ARequest::class])
        );
        $arguments = [[$typePropertyName => $notExistedType, 'id' => 'someId'], $class, 'json', []];
        $shouldCallingWith = [1=>DefaultRequest::class] + $arguments;
        ksort($shouldCallingWith);
        $this->objectNomalizer->method('denormalize')
            ->with(...$shouldCallingWith)
            ->willReturn($object = new DefaultRequest('someId', $notExistedType));
        self::assertSame(
            $object,
            $this->normalizer->denormalize(
                ...$arguments
            )
        );
    }


    public function testItShouldUseSupportNameConverter():void
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        $this->classMetadataFactory->method('getMetadataFor')->willReturn(
            $metadata = $this->createMock(ClassMetadataInterface::class)
        );
        $typePropertyName = 'multiWordType';
        $existedType = 'definedTypeInDiscriminator';
        $object = new class($existedType) {
            public function __construct(
                public readonly string $multiWordType
            ) {
            }
        };
        $metadata->method('getReflectionClass')->willReturn(new \ReflectionClass($object::class));
        $metadata->method('getClassDiscriminatorMapping')->willReturn(
            new ClassDiscriminatorMapping($typePropertyName, ['definedTypeInDiscriminator' => $object::class])
        );
        $arguments = [[$this->nameConverter->normalize($typePropertyName) => $existedType], $object::class, 'json', []];
        $this->objectNomalizer->method('denormalize')->with(...$arguments)->willReturn($object);
        self::assertSame(
            $object,
            $this->getNormalizerWithNameConverter()->denormalize(
                ...$arguments
            )
        );
    }

    public function testNormalizerShouldLogExceptionIfAnyAccured():void
    {
        $this->classMetadataFactory->method('hasMetadataFor')->willReturn(true);
        $this->classMetadataFactory->method('getMetadataFor')
            ->willThrowException($e =new InvalidArgumentException);
        $this->logger->expects(self::once())->method('error')->with($e);
        $this->normalizer->supportsDenormalization([], 'ClassWithMissingMetadata', 'json', []);

    }
}