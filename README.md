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
you can interact with IPv4 address like so:

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
N/A

## 2. IPv6

### IPv6 Address

you can interact with IPv6 address like so:

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
N/A

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

## 3. DHCPv6 DUID

```php
require 'vendor/autoload.php';

$duid = new \Kubrick\IpTools\DUID\DUID('000423b6290dcb1b7113059a2247a7b12f05');

// toString Method will automatically display the good value, macAddress, uuid or identifier
echo $duid

// you can olso display other informations
// display DUID mac value if type LLT or LL
echo $duid->macAddress 

// display the pen number in case of DUID-EN type
echo $duid->pen
```
Available attributes are:

-   `->duid` return the DUID without ":"
-   `->type` return the type of DUID
-   `->link` return the link type
-   `->macAddress` return the mac address
-   `->pen` return the iana pen number
-   `->identifier` return the identifier
-   `->timestamp` return the unix epoch timestamp
-   `->uuid` return the uuid
