---
title: Installation
---

## Prerequisites
* Statamic 6
	* We recommend using Statamic Pro.
* PHP 8.3 and above
* [PHP's `intl` extension](https://www.php.net/manual/en/book.intl.php)
* [PHP's `zip` extension](https://www.php.net/manual/en/book.zip.php)

## Install

First, install Cargo using Composer:

```
composer require duncanmcclean/statamic-cargo
```

Then, once Composer has finished doing its thing, run Cargo's install command. It'll publish various stubs, set up your products collection, and more.

```
php please cargo:install
```

## Next steps
We suggest you follow along with the docs, starting with [Products](/docs/products) to better understand the core concepts of Cargo.