# PHP Lib IP Tools

![Static Badge](https://img.shields.io/badge/licence-WTFPL-brightgreen?style=flat)

IPv4 and IPv6 tools for PHP

## Getting Started

### Installation

lib-ip-tools requires PHP >= 8.2.

```shell
composer require kubrick/lib-ip-tools
```


## 1. IP Address creation

you can invoque an ACS Service

``` php
use Kubrick\IpTools\IP\IPv4;

include 'vendor/autoload.php';

// Create an IPv4 address 10.0.0.1/24
$ipv4 = new \Kubrick\IpTools\IP\IPv4('10.0.0.1', 24);

// Get the next 4 IPv4 adresses
try {
$ipv4NextAddresses = \Kubrick\IpTools\Calculator\IPv4SubnetCalculator::next($ipv4,4);
} catch (\Kubrick\IpTools\Exception\OutOfIPv4Exception $e) {
    
}

```