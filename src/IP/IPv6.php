<?php

/** @noinspection PhpReadonlyPropertyWrittenOutsideDeclarationScopeInspection */

namespace Kubrick\IpTools\IP;

use Kubrick\IpTools\Exception\WrongIPv6FormatException;
use Kubrick\IpTools\Exception\WrongMaskException;

class IPv6 extends IPAddress
{
    public const IP_MAX_NETWORK_LENGTH = 128;
    public const IP_OCTETS = 16;
    public const BINARY_VALUES = array(
        "0000" => "0",
        "0001" => "1",
        "0010" => "2",
        "0011" => "3",
        "0100" => "4",
        "0101" => "5",
        "0110" => "6",
        "0111" => "7",
        "1000" => "8",
        "1001" => "9",
        "1010" => "a",
        "1011" => "b",
        "1100" => "c",
        "1101" => "d",
        "1110" => "e",
        "1111" => "f",
    );
    public const HEX_VALUES = array(
        "0" => "0000",
        "1" => "0001",
        "2" => "0010",
        "3" => "0011",
        "4" => "0100",
        "5" => "0101",
        "6" => "0110",
        "7" => "0111",
        "8" => "1000",
        "9" => "1001",
        "a" => "1010",
        "b" => "1011",
        "c" => "1100",
        "d" => "1101",
        "e" => "1110",
        "f" => "1111"
    );

    public readonly string $ipAddressAbv;

    /**
     * @throws WrongMaskException
     * @throws WrongIPv6FormatException
     */
    public function __construct(string $ip, int $mask = self::IP_MAX_NETWORK_LENGTH)
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new WrongIPv6FormatException($ip . ' is not a valid IPv6 Address');
        }
        if ($mask > self::IP_MAX_NETWORK_LENGTH) {
            throw new WrongMaskException('Mask can not be higher than ' . self::IP_MAX_NETWORK_LENGTH . ' you give: ' . $mask);
        }
        $this->ipPacked = inet_pton($ip);
        $this->ipArray = $this->addressToArray($ip);
        $this->ipBinary = $this->addressToBinary($this->ipArray);
        $this->ipAddress = $this->fullAddress();
        $this->ipAddressAbv = $this->abbreviateAddress();
        $this->networkMask = $mask;
        $this->hostMask = self::IP_MAX_NETWORK_LENGTH - $mask;
        $this->binaryNetworkPart = substr($this->ipBinary, 0, -$this->hostMask);
        $this->binaryHostPart = substr($this->ipBinary, $this->networkMask);
    }

    /**
     * @param string $binaryAddress
     * @param int $mask
     * @return IPv6
     * @throws WrongIPv6FormatException
     * @throws WrongMaskException
     */
    public static function createFromBinary(string $binaryAddress, int $mask): IPv6
    {
        strlen($binaryAddress) == self::IP_MAX_NETWORK_LENGTH ?: throw new WrongIPv6FormatException('Ipv6 address cannot be less or higher than 32 bits, you gave: ' . strlen($binaryAddress));

        $ipAddressSplitted = [];
        foreach (str_split($binaryAddress, 8) as $item) {
            $ipAddressSplitted[] = bindec($item);
        }

        return new IPv6(implode('.', $ipAddressSplitted), $mask);
    }

    private function addressToArray($IPv6): array
    {

        preg_match_all("#^(?<=)[0-9a-fA-F:]+(?=::)#", $IPv6, $IPv6_temp_a);
        preg_match_all("#[a-fA-F0-9]+#", implode("", $IPv6_temp_a[0]), $IPv6_temp_a);
        preg_match_all("#(?<=::)[0-9a-fA-F:]+(?<=)$#", $IPv6, $IPv6_temp_b);
        preg_match_all("#[a-fA-F0-9]+#", implode("", $IPv6_temp_b[0]), $IPv6_temp_b);

        preg_match_all("#[a-fA-F0-9]+#", $IPv6, $IPv6_temp_all);

        $IPv6_unit = array();
        if ((count($IPv6_temp_a[0]) || count($IPv6_temp_b[0])) > 0) {
            $missing_numbers = 8 - count($IPv6_temp_all[0]);

            foreach ($IPv6_temp_a[0] as $item) {
                $IPv6_unit[] = $item;
            }

            for ($i = 0; $i < $missing_numbers; $i++) {
                $IPv6_unit[] = "0000";
            }

            foreach ($IPv6_temp_b[0] as $item) {
                $IPv6_unit[] = $item;
            }
        } else {
            foreach ($IPv6_temp_all[0] as $item) {
                $IPv6_unit[] = $item;
            }
        }

        foreach ($IPv6_unit as $key => $item) {
            if (strlen($item) < 4) {
                for ($i = 0; $i < (4 - strlen($item)) ; $i++) {
                    $IPv6_unit[$key] = "0" . $IPv6_unit[$key];
                }
            }
        }

        return $IPv6_unit;
    }

    private function addressToBinary($array_IPv6): string
    {
        $full_ipv6 = implode('', $array_IPv6);

        $split_IPv6 = str_split($full_ipv6, 1);

        $binary = "";
        foreach ($split_IPv6 as $value) {
            $binary .= array_search($value, self::BINARY_VALUES);
        }
        return $binary;
    }

    private function fullAddress(): string
    {
        return implode(":", $this->ipArray);
    }

    private function abbreviateAddress(): string
    {
        $array = $this->ipArray;
        foreach ($array as $id => $block) {
            $array[$id] = preg_replace("#^0+#", "", $block);

            if (empty($array[$id])) {
                $array[$id] = "0";
            }
        }

        $final = "";
        $abb = false;
        $nbz = 0;
        for ($i = 7; $i + 1; $i--) {
            if ($array[$i] == "0") {
                if ($abb) {
                    $final = $array[$i] . $final;
                    $final = ($i != 0) ? ":" . $final : $final;
                } else {
                    $nbz++;
                }
            } else {
                if ($nbz >= 2 && !$abb) {
                    $abb = true;
                    $final = ($final != "") ? $array[$i] . ":" . $final : $array[$i] . "::";
                } else {
                    $final = $array[$i] . $final;
                }
                $final = ($i != 0) ? ":" . $final : $final;
            }
        }
        return $final;
    }

    public function __toString(): string
    {
        return $this->ipAddress;
    }
}
