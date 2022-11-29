<?php
declare(strict_types=1);

namespace Legion112\SerializerDiscriminatorDefault;

use Legion112\SerializerDiscriminatorDefault\Attributes\DiscriminatorDefault;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * This denormalized will try to convert not defined type to default one specified in attribute annotation
 * @see DiscriminatorDefault
 */
final class DiscriminatorDefaultNormalizer implements DenormalizerInterface
{
    public function __construct(
        private readonly ClassMetadataFactoryInterface $metadataFactory,
        private readonly ObjectNormalizer $objectNormalizer,
        private readonly ?NameConverterInterface $nameConverter = null,
    ) {
    }

    /**
     * @psalm-suppress MoreSpecificImplementedParamType
     * @psalm-suppress InternalMethod
     * @param array $data
     * @inheritDoc
     */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []):mixed
    {
        $mapping = $this->metadataFactory->getMetadataFor($type);
        $reflectionClass = $mapping->getReflectionClass();
        $discriminator = $mapping->getClassDiscriminatorMapping();
        if ($discriminator === null) {
            return $this->objectNormalizer->denormalize($data, $type, $format, $context);
        }
        if ($this->nameConverter) {
            $key = $this->nameConverter->normalize($discriminator->getTypeProperty());
        } else {
            $key = $discriminator->getTypeProperty();
        }
        if ($key === 'af') {
            $a = 2;
        }
        /** @psalm-suppress MixedArgument */
        if (array_key_exists($data[$key], $discriminator->getTypesMapping()) ){
            return $this->objectNormalizer->denormalize($data, $type, $format, $context);
        }

        $attributes = $reflectionClass->getAttributes(DiscriminatorDefault::class);
        /** @var DiscriminatorDefault $default */
        /** @noinspection NullPointerExceptionInspection */
        $default = array_pop($attributes)->newInstance();
        return $this->objectNormalizer->denormalize($data, $default->class, $format, $context);
    }

    /**
     * @inheritDoc
     * @psalm-suppress InternalMethod
     */
    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        try {
            if (!$this->metadataFactory->hasMetadataFor($type)) {
                return false;
            }
            if (!$this->metadataFactory->getMetadataFor($type)->getClassDiscriminatorMapping()) {
                return false;
            }
            return $this->hasDefaultAttribute($type);
        } catch (InvalidArgumentException) {
            // TODO good to write log here
            return false;
        }
    }

    /**
     * @psalm-suppress InternalMethod
     * @throws InvalidArgumentException
     */
    private function hasDefaultAttribute(string $class):bool
    {
        $reflectionClass =  $this->metadataFactory->getMetadataFor($class)->getReflectionClass();
        $attributes = $reflectionClass->getAttributes(DiscriminatorDefault::class);
        return !empty($attributes);
    }
}