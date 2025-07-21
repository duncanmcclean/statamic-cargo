---
title: Version Control Strategies
---

One of the great things about Statamic is that everything is stored in flat files, making it easy to keep track of in version control. 

However, if you're concerned about privacy or just want to avoid a cluttered Git history, there's a couple of approaches you can take to move personal information out of version control.

## Ignore carts, orders & users from version control
The simplest solution is to ignore carts, orders and users from version control. Just add the paths to your `.gitignore`:

```
content/cargo/carts
content/cargo/orders
users
```

:::tip warning
Since you're no longer "backing up" data into version control, you should make sure you have a backup strategy in place.

This may be via a server provider, or using a package like [`spatie/laravel-backup`](https://github.com/spatie/laravel-backup) to backup files to an S3-compatible filesystem.
:::

## Move carts, orders & users into a database
The other approach is to move carts, orders and users into the database. 

Cargo provides commands for migrating [carts](/docs/carts#database) and [orders](/docs/orders#database) into the database. There's also a guide on the [Statamic documentation](https://statamic.dev/tips/storing-users-in-a-database) covering the process of moving users into the database.