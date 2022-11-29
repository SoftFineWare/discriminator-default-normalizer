<?php
declare(strict_types=1);

namespace Tests\DTO;

class ARequest extends BaseRequest
{
    public function __construct(string $id, string $type, public readonly string $specialPropertyA)
    {
        parent::__construct($id, $type);
    }
}