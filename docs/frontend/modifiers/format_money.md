---
title: Format Money Modifier
description: "The `format_money` modifier converts an amount (in pence) to a nicely formatted string, like `£190.55`."
---

The `format_money` modifier converts an amount (in pence) to a nicely formatted string, like `£190.55`.

```php
special_calculation: '19055'
```

::tabs
::tab antlers
```antlers
{{ special_calculation | format_money }}
```
::tab blade
```blade
{{ Statamic::modify($specialCalculation)->formatMoney() }}
``` 
::

```php
£190.55
```
