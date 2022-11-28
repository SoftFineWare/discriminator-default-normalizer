<?php
declare(strict_types=1);

namespace Legion112\SerializerDiscriminatorDefault;

use Legion112\SerializerDiscriminatorDefault\Attributes\DiscriminatorDefault;
use ReflectionClass;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * This denormalized will try to convert not defined type to default one specified in attribute annotation
 * @see DiscriminatorDefault
 */
class DiscriminatorDefaultNormalizer implements DenormalizerInterface
{
    public function __construct(
        private readonly ClassMetadataFactoryInterface $metadataFactory,
        private readonly ObjectNormalizer $objectNormalizer,
        private readonly ?NameConverterInterface $nameConverter = null,
    ) {
    }

    /**
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
        // TODO add name converter support here
        if ($this->nameConverter) {
            $key = $this->nameConverter->normalize($discriminator->getTypeProperty());
        } else {
            $key = $discriminator->getTypeProperty();
        }
        if (array_key_exists($data[$key], $discriminator->getTypesMapping()) ){
            return $this->objectNormalizer->denormalize($data, $type, $format, $context);
        }

        $attributes = $reflectionClass->getAttributes(DiscriminatorDefault::class);
        /** @var DiscriminatorDefault $default */
        /** @noinspection NullPointerExceptionInspection */
        $default = array_pop($attributes)->newInstance();
        return $this->objectNormalizer->denormalize($data, $default->class, $format, $context);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        if (!$this->metadataFactory->hasMetadataFor($type)){
            return false;
        }
        if (!$this->metadataFactory->getMetadataFor($type)->getClassDiscriminatorMapping()){
            return false;
        }
        return $this->hasDefaultAttribute($type);
    }

    private function hasDefaultAttribute(string $class):bool
    {
        $reflectionClass = $this->getReflection($class);
        $attributes = $reflectionClass->getAttributes(DiscriminatorDefault::class);
        return !empty($attributes);
    }

    private function getReflection(string $class):ReflectionClass
    {
        return $this->metadataFactory->getMetadataFor($class)->getReflectionClass();
    }
}