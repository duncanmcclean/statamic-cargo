---
title: Alpha Notes
---

**Thanks for considering helping us test Cargo while its in alpha!** 

There's a couple of things you should bear in mind going into the alpha:
* Cargo is *basically* [Simple Commerce](https://github.com/duncanmcclean/simple-commerce), but completely rewritten.
	* Carts and orders are no longer entries, they now live in their own [Stache stores](https://statamic.dev/stache#stores).
	* Carts and orders are separate. Gone are the days of order numbers being messed up by abandoned carts. 
	* Customers are now always Statamic users.
	* *Most* (but not all) of the features in Simple Commerce have been ported over to Cargo. If you spot something missing, please [open an issue](https://github.com/duncanmcclean/cargo/issues/new). 
* Cargo requires Statamic 6.
	* This means that until an alpha is out, you'll need to require `dev-master` and manually build the Control Panel's CSS and JS.
	```
	composer require statamic/cms:dev-master -W
	cd vendor/statamic/cms
	npm ci
	npm run build
	```
* There's still room for breaking changes to be introduced. We'll add notes about any breaking changes to the changelogs.
* If you discover any bugs, please open a [GitHub issue](https://github.com/duncanmcclean/cargo/issues/new?template=bug_report.yml) and we'll take a look when we can.
* Also, if you spot any places for improvements, PRs are always welcome. 😄

## Install Process
### New Site
First, install Cargo using Composer:

```
composer require duncanmcclean/simple-commerce
```

Then, once Composer has finished doing its thing, run Cargo's install command. It'll publish various stubs, setup your products collection, and more.

```
php please cargo:install
```

### Existing Simple Commerce site
First, remove Simple Commerce from your project's `composer.json` file:

```json
"duncanmcclean/simple-commerce": "^7.0"
```

Then, install Cargo using Composer:

```
composer require duncanmcclean/simple-commerce
```

Once Composer has finished doing its thing, run Cargo's install command. It'll publish various stubs, setup your products collection, and more.

```
php please cargo:install
```

Follow the steps in the [upgrade guide](/docs/upgrade-guide). 