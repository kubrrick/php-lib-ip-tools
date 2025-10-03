<?php

namespace Kubrick\IpTools\DUID;

use Kubrick\IpTools\Enum\IanaHWType;
use Kubrick\IpTools\Enum\DUIDType;
use Kubrick\IpTools\Exception\WrongDUIDFormatException;
use Kubrick\IpTools\Exception\WrongMacException;
use Kubrick\IpTools\MAC\MacAddress;

/**
 *
 * Based on https://datatracker.ietf.org/doc/html/rfc8415#page-32
 *
 */
readonly class DUID
{
    public string $duid;
    public DUIDType $type;
    public ?IanaHWType $link;
    public ?MacAddress $macAddress;
    public ?int $pen;
    public ?string $identifier;
    public ?int $timestamp;
    public ?string $uuid;

    /**
     * @throws WrongMacException
     * @throws WrongDUIDFormatException
     */
    public function __construct(string $duid)
    {
        $duid = str_replace(':', '', $duid);
        if (!preg_match('#^000[1-4]\w+$#', $duid)) {
            throw new WrongDUIDFormatException($duid . ' is not a valid DUID format');
        }

        $this->duid = $duid;

        $this->type = self::type($duid);
        $this->link = self::linkType($duid);
        $this->macAddress = self::macAddress($duid);
        $this->pen = self::penNumber($duid);
        $this->identifier = self::identifier($duid);
        $this->timestamp = self::timestamp($duid);
        $this->uuid = self::uuid($duid);

        /**
        if ($this->type == DUIDType::LLT) {
            $this->link = self::linkType($duid);
            $this->timestamp = self::timestamp($duid);
            if ($this->link == IanaHWType::ETHERNET) {
                $this->macAddress = self::macAddress($duid);
            } else {
                $this->identifier = self::identifier($duid, 16);
            }
        }
        if ($this->type == DUIDType::EN) {
            $this->pen = self::penNumber($duid);
            $this->identifier = self::identifier($duid);
        }
        if ($this->type == DUIDType::LL) {
            $this->link = self::linkType($duid);
            if ($this->link == IanaHWType::ETHERNET) {
                $this->macAddress = self::macAddress($duid);
            } else {
                $this->identifier = self::identifier($duid, 8);
            }
        }
        if ($this->type == DUIDType::UUID) {
            $this->uuid = self::uuid($duid);
        }
        **/
    }

    /**
     *
     * Get the DUID type based on RFC8415 -> https://tools.ietf.org/html/rfc8415
     *
     * @param string $value
     * @return DUIDType
     */
    private function type(string $value): DUIDType
    {
        return DUIDType::from((int)substr($value, 3, 1));
    }

    /**
     *
     * Get the DUID link type based on iana -> https://www.iana.org/assignments/arp-parameters/arp-parameters.xhtml
     *
     * @param string $value
     * @return IanaHWType|null
     */
    private function linkType(string $value): ?IanaHWType
    {
        if (self::type($value) == DUIDType::LLT || self::type($value) == DUIDType::LL) {
            return IanaHWType::from(hexdec(substr($value, 4, 4)));
        } else {
            return null;
        }
    }

    /**
     *
     * DUID-EN IANA PEN
     *
     * @param string $value
     * @return string|null
     */
    private function penNumber(string $value): ?string
    {
        if (self::type($value) == DUIDType::EN) {
            return hexdec(substr($value, 4, 8));
        }
        return null;
    }

    /**
     *
     * DUID-EN, DUID_LLT, DUID-LL Identifier
     *
     * @param string $value
     * @return string|null
     */
    private function identifier(string $value): ?string
    {
        if ($this->type == DUIDType::LLT && $this->link != IanaHWType::ETHERNET) {
            return substr($value, 16);
        }
        if ($this->type == DUIDType::LL && $this->link != IanaHWType::ETHERNET) {
            return substr($value, 8);
        }
        if ($this->type == DUIDType::EN) {
            return substr($value, 12);
        }
        return null;
    }

    /**
     *
     * DUID-LLT timestamp
     *
     * @param string $value
     * @return int|null
     */
    private function timestamp(string $value): ?int
    {
        if (self::type($value) == DUIDType::LLT) {
            return hexdec(substr($value, 8, 8)) + 946684800; // convert timestamp from 1er january 2000 to 1er january 1970
        }
        return null;
    }

    /**
     *
     * DUID-LLT | DUID-LL mac address
     *
     * @param string $value
     * @return MacAddress|null
     * @throws WrongMacException
     */
    private function macAddress(string $value): ?MacAddress
    {
        if (($this->type == DUIDType::LLT && $this->link == IanaHWType::ETHERNET) || ($this->type == DUIDType::LL && $this->link == IanaHWType::ETHERNET)) {
            return new MacAddress(substr($value, -12));
        }
        return null;
    }

    /**
     *
     * DUID-UUID uuid
     *
     * @param string $value
     * @return string|null
     */
    private function uuid(string $value): ?string
    {
        if (self::type($value) == DUIDType::UUID) {
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

    public function __toString(): string
    {
        switch ($this->type) {
            case DUIDType::LLT:
                if ($this->link == IanaHWType::ETHERNET) {
                    return $this->macAddress->toLong();
                } else {
                    return $this->identifier;
                }
                // no break
            case DUIDType::LL:
                return $this->macAddress->toLong();
            case DUIDType::EN:
                return $this->identifier;
            case DUIDType::UUID:
                return $this->uuid;
            default:
                return '';
        }
    }
}
