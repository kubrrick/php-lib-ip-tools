<?php

namespace Kubrick\IpTools\IP;

use Kubrick\IpTools\Exception\OutOfIPv4Exception;
use Kubrick\IpTools\Exception\TooFewEntitiesExceptions;
use Kubrick\IpTools\Exception\WrongIPv4FormatException;
use Kubrick\IpTools\Exception\WrongMaskException;
use Kubrick\IpTools\Exception\WrongNetworkException;

abstract class IPv4SubnetCalculator
{
    /**
     * @param IPv4 $ipAddress
     * @param int $nb
     * @param bool $ex
     * @return IPv4[]
     * @throws OutOfIPv4Exception
     * @throws WrongIPv4FormatException
     * @throws WrongMaskException
     */
    public static function nextAddresses(IPv4 $ipAddress, int $nb = 1, bool $ex = false): array
    {

        $ipAddresses = [$ipAddress];

        for ($round = 0; $round < $nb; $round++) {

            $ipAddress = end($ipAddresses);

            $newBinaryHostPart = decbin(bindec($ipAddress->binaryHostPart) + 1);

            if (strlen($ipAddress->binaryHostPart) < strlen($newBinaryHostPart)) {
                if ($ex) {
                    throw new OutOfIPv4Exception("");
                } else {
                    break;
                }
            } elseif (strlen($ipAddress->binaryHostPart) > strlen($newBinaryHostPart)) {
                for ($i = strlen($newBinaryHostPart); $i < strlen($ipAddress->binaryHostPart); $i++) {
                    $newBinaryHostPart = 0 . $newBinaryHostPart;
                }
            }

            if (preg_match("#^1+$#", $newBinaryHostPart) || preg_match("#^0+$#", $newBinaryHostPart)) {
                if ($ex) {
                    throw new OutOfIPv4Exception("");
                } else {
                    break;
                }
            }


            $arrayIPv4 = [];
            foreach (str_split($ipAddress->binaryNetworkPart . $newBinaryHostPart, 8) as $block) {
                $arrayIPv4[] = bindec($block);
            }


            $ipAddresses[] = new IPv4(implode(".", $arrayIPv4), $ipAddress->networkMask);
        }

        array_shift($ipAddresses);
        return $ipAddresses;
    }

    /**
     * @param IPv4[] $addresses
     * @return IPv4[]
     * @throws TooFewEntitiesExceptions
     * @throws WrongNetworkException
     */
    public static function missingAddresses(array $addresses): array
    {
        if (count($addresses) < 2) {
            throw new TooFewEntitiesExceptions("Array must contain at least 2 addresses and actually contain " . count($addresses));
        }

        // We assume first ipv4 is in charge of network prefix
        $networkPart = $addresses[0]->binaryNetworkPart;

        foreach ($addresses as $address) {
            if ($address->binaryNetworkPart != $networkPart) {
                throw new WrongNetworkException('Ip address: ' . $address->ipAddress . ' does not belong to ' . $addresses[0]->ipAddress . '/' . $addresses[0]->networkMask . ' network');
            }
        }
        sort($addresses);

        $nbMissing = bindec(end($addresses)->binaryHostPart) - bindec(reset($addresses)->binaryHostPart);

        $requested = self::nextAddresses($addresses[0], $nbMissing);

        $missing = [];
        foreach ($requested as $item) {
            if (in_array($item, $addresses)) {
                continue;
            }
            $missing[] = $item;
        }

        return $missing;
    }

    /**
     * @param IPv4[] $addresses
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
}
