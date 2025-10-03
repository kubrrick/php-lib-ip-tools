<?php

namespace Kubrick\IpTools\IP;

use JetBrains\PhpStorm\Deprecated;
use Kubrick\IpTools\Exception\WrongIPv4FormatException;
use Kubrick\IpTools\Exception\WrongMaskException;

class IPv4 extends IPAddress
{
    public const IP_MAX_NETWORK_LENGTH = 32;
    public const IP_OCTETS = 4;


    /**
     * @throws WrongMaskException
     * @throws WrongIPv4FormatException
     */
    public function __construct(string $ip, int $mask = self::IP_MAX_NETWORK_LENGTH)
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            throw new WrongIPv4FormatException($ip . ' is not a valid IPv4 Address');
        }
        if ($mask > self::IP_MAX_NETWORK_LENGTH) {
            throw new WrongMaskException("Mask can not be higher than " . self::IP_MAX_NETWORK_LENGTH . " you give: " . $mask);
        }
        $this->ipAddress = $ip;
        $this->ipPacked = inet_pton($ip);
        $this->ipArray = $this->addressToArray($ip);
        $this->ipBinary =  $this->addressToBinary($this->ipArray);
        $this->networkMask = $mask;
        $this->hostMask = self::IP_MAX_NETWORK_LENGTH - $mask;
        $this->binaryNetworkPart = substr($this->ipBinary, 0, -$this->hostMask);
        $this->binaryHostPart = substr($this->ipBinary, $this->networkMask);
    }

    private function addressToArray(string $IPv4): array
    {
        preg_match_all("#[0-9]+#", $IPv4, $IP_array);
        return $IP_array[0];
    }

    private function addressToBinary(array $array_IPv4): string
    {
        $binary = "";
        foreach ($array_IPv4 as $block) {
            $binary_block = decbin($block);
            if (strlen($binary_block) < self::IP_OCTETS * 2) {
                for ($i = strlen($binary_block); $i < self::IP_OCTETS * 2; $i++) {
                    $binary_block = 0 . $binary_block;
                }
            }
            $binary .= $binary_block;
        }
        return $binary;
    }


    /**
     * TODO: write this method
     * @return string
     */
    #[Deprecated(reason: 'not written')]
    public function networkAddress(): string
    {
        return '';
    }

    /**
     * TODO: write this method
     * @return string
     */
    #[Deprecated(reason: 'not written')]
    public function broadcasAddress(): string
    {
        return '';
    }

}
