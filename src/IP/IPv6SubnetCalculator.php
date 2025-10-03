<?php

namespace Kubrick\IpTools\IP;

use JetBrains\PhpStorm\Deprecated;
use Kubrick\IpTools\Exception\OutOfIPv6PrefixException;
use Kubrick\IpTools\Exception\WrongIPv6FormatException;
use Kubrick\IpTools\Exception\WrongMaskException;

abstract class IPv6SubnetCalculator
{
    /**
     * @param IPv6 $IPv6
     * @param int $upper_prefix
     * @param int $nb
     * @param bool $ex
     * @return IPv6[] return n IPv6 Prefixes
     * @throws WrongIPv6FormatException
     * @throws WrongMaskException
     * @throws OutOfIPv6PrefixException
     */
    public static function NextPrefix(IPv6 $IPv6, int $upper_prefix, int $nb = 1, bool $ex = false): array
    {

        if ($upper_prefix > 63 || $upper_prefix > $IPv6->networkMask) {
            return [];
        }

        $IPv6s = [$IPv6];

        for ($round = 0; $round < $nb; $round++) {

            $IPv6 = end($IPv6s);

            $binary_prefix =  substr($IPv6->binaryNetworkPart, $upper_prefix);
            $upper_binary_prefix = substr($IPv6->binaryNetworkPart, 0, $upper_prefix);
            $next_binary_prefix = decbin(bindec($binary_prefix) + 1);

            if (strlen($binary_prefix) < strlen($next_binary_prefix)) {
                if ($ex) {
                    throw new OutOfIPv6PrefixException();
                } else {
                    break;
                }
            } elseif (strlen($binary_prefix) > strlen($next_binary_prefix)) {
                for ($i = strlen($next_binary_prefix); $i < strlen($binary_prefix); $i++) {
                    $next_binary_prefix = 0 . $next_binary_prefix;
                }
            }

            $new_ipv6_binary_address = $next_binary_prefix = $upper_binary_prefix . $next_binary_prefix;
            for ($i = strlen($next_binary_prefix); $i < 128; $i++) {
                $new_ipv6_binary_address .= 0;
            }

            $binary_split_IPv6 = str_split($new_ipv6_binary_address, 4);

            $hexa_full_IPv6 = "";
            foreach ($binary_split_IPv6 as $value) {
                $hexa_full_IPv6 .= array_search($value, IPv6::HEX_VALUES);
            }

            $hexa_split_IPv6 = str_split($hexa_full_IPv6, 4);
            $string_IPv6 = "";
            foreach ($hexa_split_IPv6 as $value) {
                $string_IPv6 = implode(":", $hexa_split_IPv6);
            }

            $IPv6s[] = new IPv6($string_IPv6, $IPv6->networkMask);
        }

        array_shift($IPv6s);
        return $IPv6s;

    }

    /**
     * @param IPv6 $IPv6
     * @param int $nb
     * @param int $ex
     * @return IPv6[]
     * @throws OutOfIPv6PrefixException
     */
    public static function NextAddress(IPv6 $IPv6, int $nb = 1, bool $ex = false): array
    {

        $IPv6s = [$IPv6];

        for ($round = 0; $round < $nb; $round++) {

            $IPv6 = end($IPv6s);

            $new_binary_host_part = decbin(bindec($IPv6->binaryHostPart) + 1);

            if (strlen($IPv6->binaryHostPart) < strlen($new_binary_host_part)) {
                if ($ex) {
                    throw new OutOfIPv6PrefixException("");
                } else {
                    break;
                }
            } else {
                for ($i = strlen($new_binary_host_part); $i < $IPv6->hostMask; $i++) {
                    $new_binary_host_part = 0 . $new_binary_host_part ;
                }
            }

            $new_ipv6_binary_address = $IPv6->binaryNetworkPart . $new_binary_host_part;

            $binary_split_IPv6 = str_split($new_ipv6_binary_address, 4);

            $hexa_full_IPv6 = "";
            foreach ($binary_split_IPv6 as $value) {
                $hexa_full_IPv6 .= array_search($value, IPv6::HEX_VALUES);
            }

            $hexa_split_IPv6 = str_split($hexa_full_IPv6, 4);
            $string_IPv6 = "";
            foreach ($hexa_split_IPv6 as $value) {
                $string_IPv6 = implode(":", $hexa_split_IPv6);
            }

            $IPv6s[] = new IPv6($string_IPv6, $IPv6->networkMask);
        }

        array_shift($IPv6s);
        return $IPv6s;
    }

    /**
     * TODO: Write this method
     * @param IPv6 $IPv6
     * @param int $prefix
     * @return IPv6[]
     */
    #[Deprecated(reason: 'not written')]
    public static function PrefixRange(IPv6 $IPv6, int $prefix): array
    {
        return array();
    }

    /**
     * TODO: write this method
     * @param IPv6 $IPv6
     * @return IPv6[]
     */
    #[Deprecated(reason: 'not written')]
    public static function HostRange(IPv6 $IPv6): array
    {
        return array();
    }
}
