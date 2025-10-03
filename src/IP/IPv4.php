<?php

/** @noinspection PhpReadonlyPropertyWrittenOutsideDeclarationScopeInspection */

namespace Kubrick\IpTools\IP;

use Kubrick\IpTools\Exception\WrongIPv4FormatException;
use Kubrick\IpTools\Exception\WrongMaskException;
use Kubrick\IpTools\Exception\WrongNetworkException;

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
            throw new WrongMaskException("Mask can not be higher than " . self::IP_MAX_NETWORK_LENGTH . " you gave: " . $mask);
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

    /**
     * @throws WrongMaskException
     * @throws WrongIPv4FormatException
     */
    public static function createFromBinary(string $binaryAddress, int $mask = 32): IPv4
    {
        strlen($binaryAddress) == self::IP_MAX_NETWORK_LENGTH ?: throw new WrongIPv4FormatException('Ipv4 address cannot be less or higher than 32 bits, you gave: ' . strlen($binaryAddress));

        $ipAddressSplitted = [];
        foreach (str_split($binaryAddress, 8) as $item) {
            $ipAddressSplitted[] = bindec($item);
        }

        return new IPv4(implode('.', $ipAddressSplitted), $mask);
    }

    private function addressToArray(string $IPv4): array
    {
        preg_match_all("#[0-9]+#", $IPv4, $IP_array);
        return $IP_array[0];
    }

    private function addressToBinary(array $arrayIPv4): string
    {
        $binary = '';
        foreach ($arrayIPv4 as $block) {
            $binaryBlock = decbin($block);
            if (strlen($binaryBlock) < self::IP_OCTETS * 2) {
                for ($i = strlen($binaryBlock); $i < self::IP_OCTETS * 2; $i++) {
                    $binaryBlock = 0 . $binaryBlock;
                }
            }
            $binary .= $binaryBlock;
        }
        return $binary;
    }


    /**
     * @return IPv4
     * @throws WrongNetworkException
     */
    public function networkAddress(): IPv4
    {
        !in_array($this->networkMask, [31, 32]) ?: throw new WrongNetworkException($this->networkMask . ' cannot have network address');
        return self::createFromBinary($this->binaryNetworkPart . str_repeat('0', self::IP_MAX_NETWORK_LENGTH - strlen($this->binaryNetworkPart)), $this->networkMask);
    }

    /**
     * @return IPv4
     * @throws WrongNetworkException
     */
    public function broadcastAddress(): IPv4
    {
        !in_array($this->networkMask, [31, 32]) ?: throw new WrongNetworkException($this->networkMask . ' cannot have broadcast address');
        return self::createFromBinary($this->binaryNetworkPart . str_repeat('1', self::IP_MAX_NETWORK_LENGTH - strlen($this->binaryNetworkPart)), $this->networkMask);
    }

}
