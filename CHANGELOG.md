# Changelog

## v1.0.0-alpha.5 (2025-09-13)

### What's changed
- Corrected path to `@statamic/cms` npm package by @duncanmcclean
- Updated some imports & changed how CSS is built, after changes in Statamic #35 by @duncanmcclean

### What's fixed
- Fixed incorrect name for config file in docs #34 by @FR6



## v1.0.0-alpha.4 (2025-09-08)

### What's new
- Pre-built Checkout: Names on addresses are now pre-filled with the customer's name by @duncanmcclean

### What's fixed
- Fixed type in disallowed keys array in `CartLineItemsController` by @duncanmcclean
- Ensure that `first_name`, `last_name` and `email` keys aren't persisted in cart data by @duncanmcclean
- Fixed issue where guest customers weren't being augmented properly in JSON API #33 by @duncanmcclean
- Removed duplicate icons #32 by @duncanmcclean
- Checkout views are now ignored when building CSS for the Control Panel by @duncanmcclean



## v1.0.0-alpha.3 (2025-08-22)

### What's fixed
- Fix undefined variable in `CodeInjection` #30



## v1.0.0-alpha.2 (2025-08-22)

### What's fixed
- The `Schedule` facade is now imported scheduled commands are added during `cargo:install`
- Fixed name input on dummy payment form when user's name isn't separated into first & last name fields
- Fixed converted carts being assigned to users #29 by @duncanmcclean
- Fixed error in discounting logic
- Fixed missing title on default tax class
- Added `value="1"` to quantity input in examples on the docs
- Fixed border in dark mode (in shipping & payment detail fieldtypes)
- Fixed error where pre-built checkout page would break as soon as you start your own Vite server



## v1.0.0-alpha.1 (2025-08-21)

This is the first alpha of **Cargo**, the natural evolution of Simple Commerce! 🚀

To learn more about Cargo, please visit the [Cargo documentation](https://builtwithcargo.dev).