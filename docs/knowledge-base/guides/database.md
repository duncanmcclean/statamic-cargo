---
title: Storing data in a database
description: "Out of the box, Cargo stores all of its data in flat files. However, you can opt to store some (or all) of your data in a traditional database instead."
---

Out of the box, Cargo stores all of its data in flat files. However, you can opt to store some (or all) of your data in a traditional database instead.

## Migrating

To get started, run the following command:

```
php please cargo:database
```

You'll be presented with a list of repositories to choose from. Select the ones you'd like to migrate, and Cargo will publish the necessary migrations, run them, and update your config file.

If you'd like to migrate all repositories at once, you may use the `--all` flag:

```
php please cargo:database --all
```

The available repositories are:

| Repository  | Config Key                                | Docs                                 |
|-------------|-------------------------------------------|--------------------------------------|
| Carts       | `statamic.cargo.carts.driver`             | [Carts](/docs/carts#storage)         |
| Discounts   | `statamic.cargo.discounts.driver`         | [Discounts](/docs/discounts#storage) |
| Orders      | `statamic.cargo.orders.driver`            | [Orders](/docs/orders#storage)       |
| Tax Classes | `statamic.cargo.taxes.tax_classes.driver` | [Taxes](/docs/taxes#database)        |
| Tax Zones   | `statamic.cargo.taxes.tax_zones.driver`   | [Taxes](/docs/taxes#database)        |

Products are stored as entries, so you can use the official [Eloquent Driver](https://statamic.dev/knowledge-base/tips/storing-content-in-a-database) to move them to the database. 

Customers are Statamic users, so can be moved to the database by following [this guide on the Statamic docs](https://statamic.dev/knowledge-base/tips/storing-users-in-a-database).

## Importing Existing Data

During the migration process, you'll be asked if you'd like to import existing data from flat files into the database. If you'd prefer to skip the prompt, you can pass the `--import` flag:

```
php please cargo:database --all --import
```

## Customizing Models & Tables

Each repository allows you to customize the Eloquent model and database table used. You can do this by updating the relevant keys in your `cargo.php` config file. See the individual docs pages for more details.
