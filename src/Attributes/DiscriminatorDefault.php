<?php
declare(strict_types=1);

namespace Legion112\SerializerDiscriminatorDefault\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
/** @codeCoverageIgnore  */
class DiscriminatorDefault
{
    public function __construct(public readonly string $class)
    {
    }
}