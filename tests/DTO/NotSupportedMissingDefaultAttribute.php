<?php
declare(strict_types=1);

namespace Tests\DTO;

use Symfony\Component\Serializer\Annotation\DiscriminatorMap;

#[DiscriminatorMap(typeProperty: 'type', mapping: [
    'a' => ARequest::class,
    'b' => BRequest::class,
])]
abstract class NotSupportedMissingDefaultAttribute
{
}