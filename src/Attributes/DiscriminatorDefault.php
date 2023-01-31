<?php
declare(strict_types=1);

namespace SoftFineWare\SerializerDiscriminatorDefault\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class DiscriminatorDefault
{
    public function __construct(public readonly string $class)
    {
    }
}