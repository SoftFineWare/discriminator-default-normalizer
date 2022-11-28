<?php
declare(strict_types=1);

namespace Tests\Legion112\SerializerDiscriminatorDefault;

use Symfony\Component\Serializer\Annotation\DiscriminatorMap;

#[DiscriminatorMap(typeProperty: 'type', mapping: [
    'a' => ARequest::class,
    'b' => BRequest::class,
])]
abstract class NotSupportedMissingDefaultAttribute
{
    public function __construct(
        public readonly string $id,
        public readonly string $type
    )
    {
    }
}