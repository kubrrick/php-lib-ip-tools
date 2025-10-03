<?php

namespace Kubrick\IpTools\IP;

use JetBrains\PhpStorm\Deprecated;
use Kubrick\IpTools\Exception\OutOfIPv6PrefixException;
use Kubrick\IpTools\Exception\TooFewEntitiesExceptions;
use Kubrick\IpTools\Exception\WrongIPv6FormatException;
use Kubrick\IpTools\Exception\WrongMaskException;
use Kubrick\IpTools\Exception\WrongNetworkException;

abstract class IPv6SubnetCalculator
{
    /**
     * @param IPv6 $ipAddress
     * @param int $nb
     * @param bool $ex
     * @return IPv6[]
     * @throws OutOfIPv6PrefixException
     * @throws WrongIPv6FormatException
     * @throws WrongMaskException
     */
    public static function nextAddresses(IPv6 $ipAddress, int $nb = 1, bool $ex = false): array
    {

        $ipAddresses = [$ipAddress];

        for ($round = 0; $round < $nb; $round++) {

            $ipAddress = end($ipAddresses);

            $newBinaryHostPart = decbin(bindec($ipAddress->binaryHostPart) + 1);

            if (strlen($ipAddress->binaryHostPart) < strlen($newBinaryHostPart)) {
                if ($ex) {
                    throw new OutOfIPv6PrefixException("");
                } else {
                    break;
                }
            } else {
                for ($i = strlen($newBinaryHostPart); $i < $ipAddress->hostMask; $i++) {
                    $newBinaryHostPart = 0 . $newBinaryHostPart ;
                }
            }


            $hexFullIPv6 = "";
            foreach (str_split($ipAddress->binaryNetworkPart . $newBinaryHostPart, 4) as $value) {
                $hexFullIPv6 .= array_search($value, IPv6::HEX_VALUES);
            }

            $hexSplitIPv6 = str_split($hexFullIPv6, 4);
            $stringIPv6 = "";
            foreach ($hexSplitIPv6 as $value) {
                $stringIPv6 = implode(":", $hexSplitIPv6);
            }

            $ipAddresses[] = new IPv6($stringIPv6, $ipAddress->networkMask);
        }

        array_shift($ipAddresses);
        return $ipAddresses;
    }

    /**
     * @param IPv6 $ipPrefix
     * @param int $upperPrefix
     * @param int $nb
     * @param bool $ex
     * @return IPv6[] return n IPv6 Prefixes
     * @throws WrongIPv6FormatException
     * @throws WrongMaskException
     * @throws OutOfIPv6PrefixException
     */
    public static function nextPrefixes(IPv6 $ipPrefix, int $upperPrefix, int $nb = 1, bool $ex = false): array
    {

        if ($upperPrefix > 63 || $upperPrefix > $ipPrefix->networkMask) {
            return [];
        }

        $IPv6s = [$ipPrefix];

        for ($round = 0; $round < $nb; $round++) {

            $ipPrefix = end($IPv6s);

            $binaryPrefix =  substr($ipPrefix->binaryNetworkPart, $upperPrefix);
            $upperBinaryPrefix = substr($ipPrefix->binaryNetworkPart, 0, $upperPrefix);
            $nextBinaryPrefix = decbin(bindec($binaryPrefix) + 1);

            if (strlen($binaryPrefix) < strlen($nextBinaryPrefix)) {
                if ($ex) {
                    throw new OutOfIPv6PrefixException();
                } else {
                    break;
                }
            } elseif (strlen($binaryPrefix) > strlen($nextBinaryPrefix)) {
                for ($i = strlen($nextBinaryPrefix); $i < strlen($binaryPrefix); $i++) {
                    $nextBinaryPrefix = 0 . $nextBinaryPrefix;
                }
            }

            $newIPv6BinaryAddress = $nextBinaryPrefix = $upperBinaryPrefix . $nextBinaryPrefix;
            for ($i = strlen($nextBinaryPrefix); $i < 128; $i++) {
                $newIPv6BinaryAddress .= 0;
            }

            $binarySplitIPv6 = str_split($newIPv6BinaryAddress, 4);

            $hexFullIPv6 = "";
            foreach ($binarySplitIPv6 as $value) {
                $hexFullIPv6 .= array_search($value, IPv6::HEX_VALUES);
            }

            $hexSplitIPv6 = str_split($hexFullIPv6, 4);
            $stringIPv6 = "";
            foreach ($hexSplitIPv6 as $value) {
                $stringIPv6 = implode(":", $hexSplitIPv6);
            }

            $IPv6s[] = new IPv6($stringIPv6, $ipPrefix->networkMask);
        }

        array_shift($IPv6s);
        return $IPv6s;

    }

    /**
     * @param IPv6[] $ipAddresses
     * @return IPv6[]
     * @throws TooFewEntitiesExceptions
     * @throws WrongNetworkException
     */
    public static function missingAddresses(array $ipAddresses): array
    {
        if (count($ipAddresses) < 2) {
            throw new TooFewEntitiesExceptions("Array must contain at least 2 addresses and actually contain " . count($ipAddresses));
        }

        // We assume first ipv6 is in charge of network prefix
        $networkPart = $ipAddresses[0]->binaryNetworkPart;

        foreach ($ipAddresses as $address) {
            if ($address->binaryNetworkPart != $networkPart) {
                throw new WrongNetworkException('Ip address: ' . $address->ipAddressAbv . ' does not belong to ' . $ipAddresses[0]->ipAddressAbv . '/' . $ipAddresses[0]->networkMask . ' network');
            }
        }
        sort($ipAddresses);

        $nbMissing = bindec(end($ipAddresses)->binaryHostPart) - bindec(reset($ipAddresses)->binaryHostPart);

        $requested = self::nextAddresses($ipAddresses[0], $nbMissing);

        $missing = [];
        foreach ($requested as $item) {
            if (in_array($item, $ipAddresses)) {
                continue;
            }
            $missing[] = $item;
        }

        return $missing;
    }

    /**
     * @param IPv6[] $prefixes
     * @param int $upperPrefix
     * @return IPv6[]
     * @throws TooFewEntitiesExceptions|WrongNetworkException
     * @throws WrongMaskException
     */
    public static function missingPrefixes(array $prefixes, int $upperPrefix): array
    {
        if (count($prefixes) < 2) {
            throw new TooFewEntitiesExceptions("Array must contain at least 2 prefixes and actually contain " . count($prefixes));
        }

        // We assume first ipv6 Prefix is in charge of ISP prefix
        $networkPart = substr($prefixes[0]->binaryNetworkPart, 0, $upperPrefix);

        foreach ($prefixes as $ipv6Prefix) {
            if (substr($ipv6Prefix->binaryNetworkPart, 0, $upperPrefix) != $networkPart) {
                throw new WrongNetworkException('Ip prefix: ' . $ipv6Prefix->ipAddressAbv . ' does not belong to ' . $prefixes[0]->ipAddressAbv . '/' . $upperPrefix. ' network');
            }
            if ($upperPrefix >= $ipv6Prefix->networkMask) {
                throw new WrongMaskException('Upper prefix /'. $upperPrefix .' cannot be higher or equal than /' . $ipv6Prefix->networkMask);
            }
        }

        sort($prefixes);

        $nbMissing = bindec(substr(end($prefixes)->binaryNetworkPart, $upperPrefix)) - bindec(substr(reset($prefixes)->binaryNetworkPart, $upperPrefix));

        $requested = self::nextPrefixes($prefixes[0], $upperPrefix, $nbMissing);

        $missing = [];
        foreach ($requested as $rq) {
            if (in_array($rq, $prefixes)) {
                continue;
            }
            $missing[] = $rq;
        }

        return $missing;
    }

    /**
     * @param IPv6[] $addresses
     * @return array
     */
    public static function findDuplicateAddresses(array $addresses): array
    {
        $addressesString = [];
        foreach ($addresses as $address) {
            $addressesString[] = $address->ipAddress;
        }

        $duplicates = array();
        foreach (array_count_values($addressesString) as $val => $c) {
            if ($c > 1) {
                $duplicates[] = $val;
            }
        }
        return $duplicates;
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
