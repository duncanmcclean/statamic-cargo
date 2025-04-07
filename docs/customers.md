---
title: Customers
---

Carts and orders are associated with customers - representing the customer who purchased something from your store.

There are two types of customers in Cargo:

* Users
	* Plain old [Statamic users](https://statamic.dev/users)
	* Makes it really simple to build user account functionality (using Statamic's [login/register tags](https://statamic.dev/tags/user-login_form#overview)).
	* Allows you to easily manage customers in the Control Panel
* Guest customers
	* Guest customers are saved on individual orders, useful for stores without user accounts or selling one-time purchases.
	* Can be converted to a user in the Control Panel.

When a **logged out** customer checks out, Cargo creates a "guest customer" using their name and email address.

Whereas, when a **logged in** customer checks out, Cargo will associate the order with the currently logged in user.

To force customers to be *real* Statamic users, you should ensure they're already logged in before hitting the checkout page. 

## Merging carts upon login
By default, when a customer logs in, their current cart will be merged with any carts started on other devices.

You can disable this behaviour in the `cargo.php` config file:

```php
// config/statamic/cargo.php

'carts' => [
	'merge_on_login' => true,
],
```
