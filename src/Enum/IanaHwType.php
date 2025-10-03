<?php

namespace Kubrick\IpTools\Enum;

/**
 *  From https://www.iana.org/assignments/arp-parameters/arp-parameters.xhtml (Hardware type)
 */
enum IanaHwType: int
{
    case ETHERNET = 1;
    case EXPERIMENTAL_ETHERNET = 2;
    case AX25 = 3;
    case TOKEN_RING = 4;
    case CHAOS = 5;
    case IEEE_802 = 6;
    case HYPERCHANNEL = 8;
    case FRAME_RELAY = 15;
    case FIBRE_CHANNEL = 18;
    case ATM = 19;
    case EUI_64 = 27;
    case ARP_SEC = 30;
    case IPSEC_TUNNEL = 31;
    case INFINIBAND = 32;
    case UNKNOWN = 0;
}
