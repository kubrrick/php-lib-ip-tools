<?php

namespace Kubrick\IpTools\DUID;

use Kubrick\IpTools\Enum\IanaHwType;
use Kubrick\IpTools\Enum\DuidType;
use Kubrick\IpTools\Exception\WrongDuidFormatException;
use Kubrick\IpTools\Exception\WrongIdException;
use Kubrick\IpTools\Exception\WrongMacAddressException;
use Kubrick\IpTools\MAC\MacAddress;

/**
 *
 * Based on https://datatracker.ietf.org/doc/html/rfc8415#page-32
 *
 */
readonly class Duid
{
    public string $duid;
    public DuidType $type;
    public ?IanaHwType $link;
    public ?MacAddress $macAddress;
    public ?int $pen;
    public ?string $identifier;
    public ?int $timestamp;
    public ?string $uuid;

    /**
     * @throws WrongMacAddressException
     * @throws WrongDuidFormatException
     */
    public function __construct(string $duid)
    {
        $duid = str_replace(':', '', $duid);
        if (!preg_match('#^000[1-4]\w+$#', $duid)) {
            throw new WrongDuidFormatException($duid . ' is not a valid Duid format');
        }

        $this->duid = $duid;

        $this->type = self::type($duid);
        $this->link = self::linkType($duid);
        $this->macAddress = self::macAddress($duid);
        $this->pen = self::penNumber($duid);
        $this->identifier = self::identifier($duid);
        $this->timestamp = self::timestamp($duid);
        $this->uuid = self::uuid($duid);
    }

    /**
     * type 2 octets, hw type 2 octets, timestamp 4 octets, id variable (12 octets for mac)
     * @param int $timestamp - unix epoch
     * @param IanaHwType $type - the hardware type
     * @param string $id - id of the
     * @return string
     * @throws WrongIdException
     */
    public static function createDUIDLLT(IanaHwType $type, string $id, int $timestamp = 1531668960): string
    {

        preg_match('/^([0-9A-Fa-f]{2})(:[0-9A-Fa-f]{2})*$/', $id) ?: throw new WrongIdException($id . ' is not a valid ID');
        $id = str_replace(':', '', $id);


        $timestamp = $timestamp - 946684800; // in order to match epoch 1st january 2000
        $timestampHex = dechex($timestamp);

        for ($i = strlen($timestampHex); $i < 8; $i++) {
            $timestampHex = 0 . $timestampHex;
        }
        $timestampHex = implode(':', str_split($timestampHex, 2));


        if ($id == IanaHwType::ETHERNET) {
            strlen($id) == 12 ?: throw new WrongIdException($id . ' is not a valid id for HW Type of ' . $type->name);
        };

        return '00:01:00:0'. $type->value . ':' . $timestampHex . ':' . implode(':', str_split($id, 2));
    }

    /**
     * type 2 octets, hw type 2 octets, id variable (12 octets for mac)
     * @param IanaHwType $type
     * @param string $id
     * @return string
     * @throws WrongIdException
     */
    public static function createDUIDLL(IanaHwType $type, string $id): string
    {
        preg_match('/^([0-9A-Fa-f]{2})(:[0-9A-Fa-f]{2})*$/', $id) ?: throw new WrongIdException($id . ' is not a valid ID');
        $id = str_replace(':', '', $id);

        if ($id == IanaHwType::ETHERNET) {
            strlen($id) == 12 ?: throw new WrongIdException($id . ' is not a valid id for HW Type of ' . $type->name);
        };

        return '00:03:00:0'. $type->value . ':' . implode(':', str_split($id, 2));
    }

    /**
     * type 2 octets, enterprise number 4 octets, identifier variable
     * @param int $penNumber
     * @param string $id
     * @return string
     */
    public static function createDUIDEN(int $penNumber, string $id): string
    {
        $pen_number_hex = dechex($penNumber);
        for ($i = strlen($pen_number_hex); $i < 8; $i++) {
            $pen_number_hex = 0 . $pen_number_hex;
        }

        return '00:02:' . implode(':', str_split($pen_number_hex, 2)) . ':' . $id;
    }

    /**
     *
     * Get the Duid type based on RFC8415 -> https://tools.ietf.org/html/rfc8415
     *
     * @param string $value
     * @return DuidType
     */
    private function type(string $value): DuidType
    {
        return DuidType::from((int)substr($value, 3, 1));
    }

    /**
     *
     * Get the Duid link type based on iana -> https://www.iana.org/assignments/arp-parameters/arp-parameters.xhtml
     *
     * @param string $value
     * @return IanaHwType|null
     */
    private function linkType(string $value): ?IanaHwType
    {
        if (self::type($value) == DuidType::LLT || self::type($value) == DuidType::LL) {
            return IanaHwType::from(hexdec(substr($value, 4, 4)));
        } else {
            return null;
        }
    }

    /**
     *
     * Duid-EN IANA PEN
     *
     * @param string $value
     * @return string|null
     */
    private function penNumber(string $value): ?string
    {
        if (self::type($value) == DuidType::EN) {
            return hexdec(substr($value, 4, 8));
        }
        return null;
    }

    /**
     *
     * Duid-EN, Duid-LLT, Duid-LL Identifier
     *
     * @param string $value
     * @return string|null
     */
    private function identifier(string $value): ?string
    {
        if ($this->type == DuidType::LLT && $this->link != IanaHwType::ETHERNET) {
            return substr($value, 16);
        }
        if ($this->type == DuidType::LL && $this->link != IanaHwType::ETHERNET) {
            return substr($value, 8);
        }
        if ($this->type == DuidType::EN) {
            return substr($value, 12);
        }
        return null;
    }

    /**
     *
     * Duid-LLT timestamp
     *
     * @param string $value
     * @return int|null
     */
    private function timestamp(string $value): ?int
    {
        if (self::type($value) == DuidType::LLT) {
            return hexdec(substr($value, 8, 8)) + 946684800; // convert timestamp from 1er january 2000 to 1er january 1970
        }
        return null;
    }

    /**
     *
     * Duid-LLT | Duid-LL mac address
     *
     * @param string $value
     * @return MacAddress|null
     * @throws WrongMacAddressException
     */
    private function macAddress(string $value): ?MacAddress
    {
        if (($this->type == DuidType::LLT && $this->link == IanaHwType::ETHERNET) || ($this->type == DuidType::LL && $this->link == IanaHwType::ETHERNET)) {
            return new MacAddress(substr($value, -12));
        }
        return null;
    }

    /**
     *
     * Duid-UUID uuid
     *
     * @param string $value
     * @return string|null
     */
    private function uuid(string $value): ?string
    {
        if (self::type($value) == DuidType::UUID) {
            $value = substr($value, 4);
            $uuid = sprintf(
                '%s-%s-%s-%s-%s',
                substr($value, 0, 8),
                substr($value, 8, 4),
                substr($value, 12, 4),
                substr($value, 16, 4),
                substr($value, 20)
            );
            return strtolower($uuid);
        }
        return null;
    }


    public function getIdentifier(): string
    {
        switch ($this->type) {
            case DuidType::LLT:
                if ($this->link == IanaHwType::ETHERNET) {
                    return $this->macAddress->toLong();
                } else {
                    return $this->identifier;
                }
                // no break
            case DuidType::LL:
                return $this->macAddress->toLong();
            case DuidType::EN:
                return $this->identifier;
            case DuidType::UUID:
                return $this->uuid;
            default:
                return '';
        }
    }


    public function __toString(): string
    {
        return  implode(':', str_split($this->duid, 2));
    }
}
