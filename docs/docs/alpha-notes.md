---
title: Alpha Notes
---

First things first, **thank you** for helping me test Cargo while it's in alpha!

It feels like I've been working on Cargo for ages now. It started out as me tinkering around with new ideas for Simple Commerce, and eventually turned into a full-on rewrite and rename. 

Needless to say, I'm excited to finally be able to share it with you! 

## It's an alpha!
There are a couple of things you should be aware of going into the alpha:

### Stuff will be broken.
I know it's probably obvious, but I just thought I'd mention it anyway.

Please don't use Cargo in production just yet. You're welcome to migrate existing sites to Cargo, but please don't deploy anything until Cargo hits 1.0.

### Cargo requires Statamic 6
Cargo requires Statamic 6, which is currently in its own alpha stage. You'll need to [update from Statamic 5 to 6](https://github.com/statamic/cms/releases/tag/6.0.0-alpha.1) before you can install Cargo.

If you spot any bugs in Statamic 6, please report them on the [`statamic/cms`](https://github.com/statamic/cms/issues) repository.

### Breaking changes
Cargo is still in development, and while I'm not planning any _huge_ breaking changes, I wouldn't be surprised if a few small ones slip in before `1.0`. I'll be sure to document any breaking changes in the changelogs as they happen.

## Install Process
### New Site
First, install Cargo using Composer:

```
composer require duncanmcclean/statamic-cargo
```

Once Cargo has been installed, run Cargo's install command to publish various stubs, set up your products collection, and more:

```
php please cargo:install
```

Now you've got everything set up, I recommend you follow along with the docs, starting with [Products](/docs/products) to better understand the core concepts of Cargo.

### Existing Simple Commerce site
First, uninstall Simple Commerce and install Cargo using Composer:

```
composer remove duncanmcclean/simple-commerce
composer require duncanmcclean/statamic-cargo
```

Once Cargo has been installed, run Cargo's install command to publish various stubs, set up your products collection, and more:

```
php please cargo:install
```

Finally, review the [migration guide](/docs/migrating-from-simple-commerce) to see what has changed and how to migrate your data and config options into Cargo.

## Feedback
**All feedback is welcome!**

If you come across a bug, please open a [GitHub issue](https://github.com/duncanmcclean/statamic-cargo/issues/new?template=bug_report.yml) and I'll take a look when I can. 

If you spot a typo in the docs, or something looking off, and have time to fix it, please consider opening a [pull request](https://github.com/duncanmcclean/statamic-cargo/pulls). 

If you need help with something, or have an idea for a feature, please [start a discussion](https://github.com/duncanmcclean/statamic-cargo/discussions/new/choose).

Otherwise, for anything else, ping me on Discord. 
