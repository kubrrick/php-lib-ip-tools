<?php

namespace Kubrick\IpTools\Enum;

enum DuidType: int
{
    case LLT = 1;
    case EN = 2;
    case LL = 3;
    case UUID = 4;
    case UNKNOWN = 0;

    public function description(): string
    {
        return match($this) {
            self::LLT => 'Link Layer concatenated with a timestamp Duid type',
            self::EN => 'Vendor-assigned unique ID based on enterprise number Duid Type',
            self::LL => 'Link Layer Duid type',
            self::UUID => 'UUID-based Duid type',
            self::UNKNOWN => 'Unknown Duid type'
        };
    }
}
