---
title: Orders Tag
---

The `{{ orders }}` tag allows you to loop through your orders on the frontend. Useful for display a customer's order history.

:::tip Warning
The `{{ orders }}` tag isn't automatically scoped to the current user's orders. Therefore, you should make sure you have sufficient authorisation / filtering in place to prevent leaking sensitive information.
:::

::tabs
::tab antlers
```antlers
<h1>Order History</h1>

<ul>
	{{ orders :customer:is="current_user:id" }}
		<li>#{{ order_number }} - {{ date }} ({{ grand_total }})</li>
	{{ /orders }}
</ul>
```
::tab blade
```blade
<h1>Order History</h1>  
  
<ul>  
    <statamic:orders customer:is="{{ $current_user->id }}">  
        <li>#{{ $order_number }} - {{ $date }} ({{ $grand_total }})</li>  
    </statamic:orders>  
</ul>
```
::

The `{{ orders }}` tag re-uses a lot of the same filtering, ordering, grouping logic as Statamic's built-in `{{ collection }}` tag.

To avoid duplicating content, please consult the [Statamic documentation](https://statamic.dev/tags/collection) for information on the available parameters and syntax.