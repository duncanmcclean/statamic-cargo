---
title: Installation
---

## Prerequisites
* Statamic 6
	* We recommend using Statamic Pro.
* PHP 8.3 and above
* [PHP's `intl` extension](https://www.php.net/manual/en/book.intl.php)

## Install

First, install Cargo using Composer:

```
composer require duncanmcclean/simple-commerce
```

Then, once Composer has finished doing its thing, run Cargo's install command. It'll publish various stubs, setup your products collection, and more.

```
php please cargo:install
```

## Next steps
We suggest you follow along with the docs, starting with [Carts & Orders](/docs/the-basics/carts-orders) to better understand the core concepts of Cargo.