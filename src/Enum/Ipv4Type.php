<?php

namespace Kubrick\IpTools\Enum;

enum Ipv4Type: int
{
    case NETWORK = 1;
    case HOST = 2;
    case BROADCAST = 3;
}
