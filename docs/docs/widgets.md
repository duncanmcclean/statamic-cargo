---
title: Widgets
description: "Cargo includes several helpful widgets for your Control Panel dashboard, including Total Revenue, New Customers, and more."
---
Cargo includes a few helpful [widgets](https://statamic.dev/widgets/overview) out-of-the-box for things like Total Revenue, New Customers, etc.

Cargo may have already configured widgets during the install process, but you can add and configure them yourself in the `config/statamic/cp.php` file:

```php
// config/statamic/cp.php

'widgets' => [
    ['type' => 'total_sales', 'width' => 25],
    ['type' => 'total_revenue', 'width' => 25],
    ['type' => 'new_customers', 'width' => 25],
    ['type' => 'returning_customers', 'width' => 25],
    ['type' => 'refunded_orders', 'width' => 25],
    ['type' => 'recent_orders', 'width' => 50, 'limit' => 10],
    ['type' => 'low_stock_products', 'width' => 50, 'limit' => 5],
],
```

## Options

You may provide a `days` option to customise the comparison period for statistic widgets. Defaults to `30`.
```php
['type' => 'total_revenue', 'width' => 25, 'days' => 14],
```

You may provide a `limit` option to control the number of items displayed in listing widgets. Defaults to `5`.
```php
['type' => 'recent_orders', 'width' => 50, 'limit' => 10],
```
