<?php

namespace Kubrick\IpTools\MAC;

use Exception;
use Kubrick\IpTools\Exception\WrongMacException;

class MacAddress
{
    private int $macAddress;

    /**
     * @throws WrongMacException
     */
    public function __construct(string $mac)
    {
        preg_match_all('#[0-9a-fA-F]{2}#', $mac, $toto);

        if (strlen(implode($toto[0])) <> 12) {
            throw new WrongMacException($mac . ' in not a valid mac address');
        }

        $this->macAddress = hexdec(implode($toto[0]));
    }

    public function toShort(): string
    {
        return $this->toString();
    }
    public function toLong(): string
    {
        return wordwrap($this->toString(), 2, ':', true);
    }
    public function getOUI()
    {
        return substr($this->toString(), 0, 6);
    }
    public function getNIC()
    {
        return substr($this->toString(), -6);
    }

    /**
     * @throws Exception
     */
    public function incrementBy(int $number = 0): self
    {
        $this->macAddress = $this->macAddress + $number;
        if ($this->macAddress > 281474976710655) {
            throw new Exception();
        }
        return $this;
    }
    /**
     * @throws Exception
     */
    public function decrementBy(int $number = 0): self
    {
        $this->macAddress = $this->macAddress + $number;
        if ($this->macAddress < 0) {
            throw new Exception();
        }
        return $this;
    }

    private function toString(): string
    {
        $mac = dechex($this->macAddress);
        if (strlen($mac) < 12) {
            for ($i = strlen($mac); $i < 12; $i++) {
                $mac = '0' . $mac;
            }
        }
        return $mac;
    }

    public function __toString()
    {
        return $this->toLong();
    }
}
