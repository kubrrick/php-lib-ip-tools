<?php

namespace Kubrick\IpTools\IP;

use Kubrick\IpTools\Exception\OutOfIPv4Exception;
use Kubrick\IpTools\Exception\WrongIPv4FormatException;
use Kubrick\IpTools\Exception\WrongMaskException;

abstract class IPv4SubnetCalculator
{
    /**
     * @param IPv4 $IPv4
     * @param int $nb
     * @param bool $ex
     * @return IPv4[]
     * @throws OutOfIPv4Exception
     * @throws WrongIPv4FormatException
     * @throws WrongMaskException
     */
    public static function next(IPv4 $IPv4, int $nb = 1, bool $ex = false): array
    {

        $IPv4s = [$IPv4];

        for ($round = 0; $round < $nb; $round++) {

            $IPv4 = end($IPv4s);

            $new_binary_host_part = decbin(bindec($IPv4->binaryHostPart) + 1);

            if (strlen($IPv4->binaryHostPart) < strlen($new_binary_host_part)) {
                if ($ex) {
                    throw new OutOfIPv4Exception("");
                } else {
                    break;
                }
            } elseif (strlen($IPv4->binaryHostPart) > strlen($new_binary_host_part)) {
                for ($i = strlen($new_binary_host_part); $i < strlen($IPv4->binaryHostPart); $i++) {
                    $new_binary_host_part = 0 . $new_binary_host_part;
                }
            }

            if (preg_match("#^1+$#", $new_binary_host_part) || preg_match("#^0+$#", $new_binary_host_part)) {
                if ($ex) {
                    throw new OutOfIPv4Exception("");
                } else {
                    break;
                }
            }

            $newBinaryIPv4 = $IPv4->binaryNetworkPart . $new_binary_host_part;
            $binary_array_IPV4 = str_split($newBinaryIPv4, 8);


            $array_IPv4 = [];
            foreach ($binary_array_IPV4 as $block) {
                $array_IPv4[] = bindec($block);
            }

            $IPv4s[] = new IPv4(implode(".", $array_IPv4), $IPv4->networkMask);

        }

        array_shift($IPv4s);
        return $IPv4s;
    }
}
