# PHP Lib IP Tools

![Static Badge](https://img.shields.io/badge/licence-WTFPL_2.0-brightgreen?style=flat)

Various IPv4 and IPv6 tools for PHP

## Getting Started
### Installation

lib-ip-tools requires PHP >= 8.2.

```shell
composer require kubrick/lib-ip-tools
```

## 1. IPv4
### IPv4 address

``` php
include 'vendor/autoload.php';

// Create an IPv4 address
$ipv4 = new \Kubrick\IpTools\IP\IPv4('10.0.0.1', 24);
```
Available attributes are:

-   `->ipBinary` return the string equivalent binary ip address eg: 00001010000000000000000000000000
-   `->ipAddress` return the ip address eg: 10.0.0.1
-   `->networkMask` return the network mask eg: 24
-   `->hostMask` return the host mask eg: 8
-   `->binaryNetworkPart` return the network part as string equivalent eg: 000010100000000000000000
-   `->binaryHostPart` return the host part as string equivalent eg: 00000001

### IPv4 Subnet calculator
You can also

```php
include 'vendor/autoload.php';

// Will return the next 5 addresses (.2 .3 .4 .5 .6)
$addresses = \Kubrick\IpTools\IP\IPv4SubnetCalculator::nextAddresses(
    new \Kubrick\IpTools\IP\IPv4('10.0.0.1', 24),
    5
);

// Will return ip addresses between .2 and .49 (given ip address are excluded)
$addresses = \Kubrick\IpTools\IP\IPv4SubnetCalculator::missingAddresses(
    [
        new \Kubrick\IpTools\IP\IPv4('10.0.0.1', 24),
        new \Kubrick\IpTools\IP\IPv4('10.0.0.10', 24),
        new \Kubrick\IpTools\IP\IPv4('10.0.0.50', 24)
    ]
);
```

## 2. IPv6

### IPv6 Address

``` php
include 'vendor/autoload.php';

// Create an IPv6 address
$ipv6 = new \Kubrick\IpTools\IP\IPv6('2001::1', 64);
```
Available attributes are:

-   `->ipBinary` return the string equivalent binary ip address eg: 00100000000000010000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000001
-   `->ipAddress` return the ip address eg: 2001:0000:0000:0000:0000:0000:0000:0001
-   `->ipAddressAbv` return the ip address on abbreviate format eg: 2001::1
-   `->networkMask` return the network mask eg: 64
-   `->hostMask` return the host mask eg: 64
-   `->binaryNetworkPart` return the network part as string equivalent eg: 0010000000000001000000000000000000000000000000000000000000000000
-   `->binaryHostPart` return the host part as string equivalent eg: 0000000000000000000000000000000000000000000000000000000000000001

### IPv6 Subnet calculator

```php
require 'vendor/autoload.php';

// Like ipv4 this will return the 5 new addresses
$addresses = \Kubrick\IpTools\IP\IPv6SubnetCalculator::nextAddresses(
    new \Kubrick\IpTools\IP\IPv6('2001::', 64),
    5
);

// This will return the 5 next IPv6 prefixes, you need to pass the ISP prefix eg. 48 and the number of prefixes you want
$addresses = \Kubrick\IpTools\IP\IPv6SubnetCalculator::nextPrefixes(
    new \Kubrick\IpTools\IP\IPv6('2001::', 56),
    48,
    5
);

// like ipv4 this will return missing ipv6 addresses
$addresses = \Kubrick\IpTools\IP\IPv6SubnetCalculator::missingAddresses(
    [
        new \Kubrick\IpTools\IP\IPv6('2001::', 64),
        new \Kubrick\IpTools\IP\IPv6('2001::5', 64),
        new \Kubrick\IpTools\IP\IPv6('2001::f', 64)
    ]
);

// like ipv4 this will return the missings prefixes, you juste have to provive the ISP prefix.
$addresses = \Kubrick\IpTools\IP\IPv6SubnetCalculator::missingPrefixes(
    [
        new \Kubrick\IpTools\IP\IPv6('2001:0:0:0000::', 56),
        new \Kubrick\IpTools\IP\IPv6('2001:0:0:0300::', 56)
    ],
    48
);
```

## 3. Mac address

```php
include 'vendor/autoload.php';

//create a mac address
$mac = new \Kubrick\IpTools\MAC\MacAddress('00:11:22:33:44:55');

// get the short format (001122334455)
echo $mac->toShort();

// get the long format (00:11:22:33:44:55)
echo $mac->toLong();

// you can also increment or decrement mac address (00:11:22:33:44:5a)
$mac->increment(5)->toLong()
```
Available methods are:

-   `toShort():string` return the short format of the mac address eg. 001122334455 
-   `toLong():string` return the long format of the mac address eg. 00:11:22:33:44:55
-   `getOUI():string` return the OUI eg. 001122
-   `getNIC():string` return the NIC eg. 334455
-   `incrementBy(int):self` increment mac address by the number given
-   `decrementBy(int):self` decrement mac address by the number given

## 4. DHCPv6 DUID

```php
require 'vendor/autoload.php';

$duid = new \Kubrick\IpTools\DUID\DUID('000423b6290dcb1b7113059a2247a7b12f05');

// getIdentifier() method will automatically display the good value, macAddress, uuid or identifier
echo $duid->getIdentifier();

// you can olso display other informations
// display DUID mac value if type LLT or LL
echo $duid->macAddress;

// display the pen number in case of DUID-EN type
echo $duid->pen;
```
Available attributes are:

-   `->duid` returns the DUID without ":"
-   `->type` returns the type of DUID
-   `->link` returns the link type
-   `->macAddress` returns the mac address
-   `->pen` returns the iana pen number
-   `->identifier` returns the identifier
-   `->timestamp` returns the unix epoch timestamp
-   `->uuid` returns the uuid

Available methods are:
- `getIdentifier()` returns the identifier base on duid type (eg mac address for LL & LLT, ...)

Available static methods are:
- `::createDUIDLLT()` create a LLT DUID Type
- `::createDUIDLL()` create a LL DUID type
- `::createDUIDEN()` create an EN DUID type
