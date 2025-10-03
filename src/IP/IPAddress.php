<?php

namespace Kubrick\IpTools\IP;

abstract class IPAddress
{
    protected string $ipPacked;
    protected array $ipArray;
    public readonly string $ipBinary;
    public readonly string $ipAddress;
    public readonly int $networkMask;
    public readonly int $hostMask;
    public readonly string $binaryNetworkPart;
    public readonly string $binaryHostPart;

    public function __toString()
    {
        return $this->ipAddress;
    }
}
