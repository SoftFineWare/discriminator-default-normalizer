# Symfony Serializer Normalizer For Default DiscriminatorMap
[![Test commit](https://github.com/SoftFineWare/discriminator-default-normalizer/actions/workflows/commit.yml/badge.svg)](https://github.com/SoftFineWare/discriminator-default-normalizer/actions/workflows/commit.yml)
## How to use it
```php
use SoftFineWare\SerializerDiscriminatorDefault\Attributes\DiscriminatorDefault;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;

#[DiscriminatorDefault(class: DefaultRequest::class)]
#[DiscriminatorMap(typeProperty: 'type', mapping: [
    'a' => ARequest::class,
])]
abstract class BaseRequest
{
    public function __construct(
        public readonly string $id,
        public readonly string $type
    )
    {
    }
}
class DefaultRequest extends BaseRequest {}
```
In case of no match json will de denormalized to default class specifing by `DiscriminatorDefault` attribute

`DiscriminatorDefault` should not point to a class with `DiscriminatorMap` new child class must be create as `DefaultRequest` above

## Installation
This normalizer must have priority -999 before:
```php
\Symfony\Component\Serializer\Normalizer\ObjectNormalizer::class
```