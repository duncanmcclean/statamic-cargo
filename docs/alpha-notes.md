---
title: Alpha Notes
---

First things first, **thank you** for helping me test Cargo while it's in alpha!

It feels like I've been working on Cargo for ages now. It started out as me tinkering around with new ideas for Simple Commerce, and eventually turned into a full-on rewrite and rename. 

Needless to say, I'm excited to finally be able to share it with you! 

## It's an alpha!
There's a couple of things you should be aware of going into the alpha:

1. Stuff will be broken.
	* It's probably obvious, but I just thought I'd say it anyway.
    * Please don't use Cargo in production just yet. However, you're welcome to migrate existing sites to Cargo, but just don't deploy them.
2. Cargo requires Statamic 6.
   * As of the time of writing, Statamic 6 isn't even in alpha yet. So, to use Cargo, you'll need to require `dev-master` and manually build the Control Panel's CSS and JS:
     
	```
	composer config minimum-stability dev
	composer require statamic/cms:dev-master -W
	cd vendor/statamic/cms
	npm ci
	npm run build
    cd ../../..
	```

	* You can find the [draft v5 -> v6 upgrade guide here](https://github.com/statamic/docs/blob/6.0/content/collections/docs/5-to-6.md).
    * Statamic 6 will include a complete UI redesign of the Control Panel. It hasn't been merged in just yet, but when it is, expect to see a bunch of broken stuff. 🫠
3. There's still room for breaking changes.
    * I'm not planning on any huge breaking changes, but there's bound to be a few small ones here and there. I'll add notes about any breaking changes to the changelogs.

## Install Process
### New Site
First, add a composer repository to your `composer.json` file:

```json
"repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:duncanmcclean/statamic-cargo.git"
    }
]
```

Next, install Cargo using Composer:

```
composer require duncanmcclean/statamic-cargo
```

Once Composer has finished doing its thing, run Cargo's install command. It'll publish various stubs, set up your products collection, and more.

```
php please cargo:install
```

Next, because the Cargo repository isn't public yet, you'll need to build the Control Panel CSS and JS manually:

```
cd vendor/duncanmcclean/statamic-cargo
npm ci
npm run build
cd ../../..
```

Finally, review the [migration guide](/docs/migrating-from-simple-commerce) to see what's changed, and how to migrate your configuration and data into Cargo.

### Existing Simple Commerce site
First, add a composer repository to your `composer.json` file:

```json
"repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:duncanmcclean/statamic-cargo.git"
    }
]
```

Next, uninstall Simple Commerce and install Cargo using Composer:

```
composer remove duncanmcclean/simple-commerce
composer require duncanmcclean/statamic-cargo
```

Once Composer has finished doing its thing, run Cargo's install command. It'll publish various stubs, setup your products collection, and more.

```
php please cargo:install
```

Next, because the Cargo repository isn't public yet, you'll need to build the Control Panel CSS and JS manually:

```
cd vendor/duncanmcclean/statamic-cargo
npm ci
npm run build
cd ../../..
```

Finally, review the [migration guide](/docs/migrating-from-simple-commerce) to see what's changed, and how to migrate your configuration and data into Cargo.

## Feedback
**All feedback is welcome!**

If you come across a bug, please open a [GitHub issue](https://github.com/duncanmcclean/statamic-cargo/issues/new?template=bug_report.yml) and I'll take a look when I can. 

If you spot a typo in the docs, or something looking off, and have time to fix it, please consider opening a [pull request](https://github.com/duncanmcclean/statamic-cargo/pulls). 

If you need help with something, or have an idea for a feature, please [start a discussion](https://github.com/duncanmcclean/statamic-cargo/discussions/new/choose).

Otherwise, for anything else, ping me on Discord. 