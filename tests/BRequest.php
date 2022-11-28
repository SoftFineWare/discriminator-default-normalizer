<?php
declare(strict_types=1);

namespace Tests\Legion112\SerializerDiscriminatorDefault;

class BRequest extends BaseRequest
{
    public function __construct(string $id, string $type, public readonly string $specialPropertyB)
    {
        parent::__construct($id, $type);
    }
}