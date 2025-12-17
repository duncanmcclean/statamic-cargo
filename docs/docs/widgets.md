---
title: Widgets
---

## Recent Orders

TODO: Screenshot

```php
// config/statamic/cp.php

'widgets' => [
    ['type' => 'recent_orders', 'width' => 50, 'limit' => 10],
],
```

Supported options:

* `limit`
* `fields`
* `days` - number of days to consider "recent"