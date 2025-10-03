<?php

namespace Kubrick\IpTools\Enum;

enum DUIDType: int
{
    case LLT = 1;
    case EN = 2;
    case LL = 3;
    case UUID = 4;
    case UNKNOWN = 0;

    public function description(): string
    {
        return match($this) {
            self::LLT => 'Link Layer concatenated with a timestamp DUID type',
            self::EN => 'Vendor-assigned unique ID based on enterprise number DUID Type',
            self::LL => 'Link Layer DUID type',
            self::UUID => 'UUID-based DUID type',
            self::UNKNOWN => 'Unknown DUID type'
        };
    }
}
