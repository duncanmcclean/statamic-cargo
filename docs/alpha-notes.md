---
title: Alpha Notes
---

First things first, **thank you** for helping me test Cargo while it's in alpha!

It feels like I've been working on Cargo for ages now. It started out as me tinkering around with new ideas for Simple Commerce, and eventually turned into a full-on rewrite and rename. 

Needless to say, I'm excited to finally be able to share it with you! 

## It's an alpha!
There's a couple of things you should be aware of going into the alpha:

### Stuff will be broken.
I know it's probably obvious, but I just thought I'd mention it anyway.

Please don't use Cargo in production just yet. You're welcome to migrate existing sites to Cargo, just please don't deploy them.

### Cargo requires Statamic 6
As of the time of writing, Statamic 6 isn't even in alpha yet. This means that in order to use Cargo, you'll need to require the `dev-master` branch, as well as manually building Statamic's CSS & JS.
     
```
composer config minimum-stability dev
composer require statamic/cms:dev-master -W
cd vendor/statamic/cms
npm ci
npm run build
cd ../../..
```

You can safely ignore any `Failed to download` or `Can't locate path` errors thrown by Composer, they are happening because we're installing a dev version of Statamic rather than a proper release.

If you're upgrading an existing site from Statamic 5, you can find the [draft v5 -> v6 upgrade guide here](https://github.com/statamic/docs/blob/6.0/content/collections/docs/5-to-6.md). You can skip the "Upgrade using Composer" section.

Statamic 6 will include a complete UI overhaul of the Control Panel. However, right now, it's still a work-in-progress and hasn't been merged into the `master` branch yet. When it is, expect to see a bunch of broken stuff in the Control Panel until I get a chance to make everything look nice.

### Breaking changes
Cargo is still in development, and while I'm not planning any _huge_ breaking changes, I wouldn't be surprised if a few small ones slip in before `1.0`. I'll be sure to document any breaking changes in the changelogs as they happen.

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

You can safely ignore any `Failed to download` or `Can't locate path` errors thrown by Composer, they are due to us installing a dev version of Cargo.

Once Composer has finished doing its thing, run Cargo's install command. It'll publish various stubs, set up your products collection, and more.

```
php please cargo:install
```

Now that you're all setup, I recommend you follow along with the docs, starting with [Carts & Orders](/docs/carts-and-orders) to better understand the core concepts of Cargo.

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

You can safely ignore any `Failed to download` or `Can't locate path` errors thrown by Composer, they are due to us installing a dev version of Cargo.

Once Composer has finished doing its thing, run Cargo's install command. It'll publish various stubs, setup your products collection, and more.

```
php please cargo:install
```

Finally, review the [migration guide](/docs/migrating-from-simple-commerce) to see what's changed, and how to migrate your configuration and data into Cargo.

## Feedback
**All feedback is welcome!**

If you come across a bug, please open a [GitHub issue](https://github.com/duncanmcclean/statamic-cargo/issues/new?template=bug_report.yml) and I'll take a look when I can. 

If you spot a typo in the docs, or something looking off, and have time to fix it, please consider opening a [pull request](https://github.com/duncanmcclean/statamic-cargo/pulls). 

If you need help with something, or have an idea for a feature, please [start a discussion](https://github.com/duncanmcclean/statamic-cargo/discussions/new/choose).

Otherwise, for anything else, ping me on Discord. 