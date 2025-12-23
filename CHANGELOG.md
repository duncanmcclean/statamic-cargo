# Changelog



## v1.0.0-alpha.11 (2025-12-03)

### What's new
- Discounts & Orders can now be searched via the Command Palette! #27 by @duncanmcclean

### What's improved
- Dropped `axios` dependency #69 by @duncanmcclean
- PHP 8.5 compatibility #68 by @duncanmcclean
- Dropped support for Laravel 11 #67 by @duncanmcclean

### What's fixed
- Fixed tax rates fieldtype #72 by @duncanmcclean
- Allow using route bindings on frontend #71 by @duncanmcclean
- Payment & Shipping Details: Border shouldn't be visible unless there are details
- Replaced usage of `Tooltip` component with `v-tooltip`
- Fixed icon for action dropdown on the order details page



## v1.0.0-alpha.10 (2025-11-01)

### What's new
- Added `new_customer` flag to orders #60 by @duncanmcclean

### What's improved
- Minor tweaks to cart and order summaries in pre-built checkout flow #63 by @duncanmcclean
- Improved UI of tax rate fields #61 #66 by @duncanmcclean
- Moved listings into page components #65 by @duncanmcclean

### What's fixed
- Fixed TailwindCSS not being generated in `cp.css` #62 by @duncanmcclean
- Fixed path to pre-built checkout Vite hot file #64 by @duncanmcclean



## v1.0.0-alpha.9 (2025-10-21)

### What's new
- All pages have been converted to Inertia #57 by @duncanmcclean

### What's improved
- Packing Slip Improvements #58 by @duncanmcclean
- `site` is now returned when augmenting carts & orders #59 by @duncanmcclean



## v1.0.0-alpha.8 (2025-10-09)

### What's fixed
- Fixed missing products in the packing slip by @duncanmcclean
- Renamed `public/checkout` directory to avoid Apache conflicts #56 by @duncanmcclean
- Fixed error when converting guest customer to user by @duncanmcclean
- Pre-built checkout: Ensure address name field is pre-filled, not the line 1 field by @duncanmcclean
- Only show shipping tab when order has physical products #52 by @duncanmcclean



## v1.0.0-alpha.7 (2025-09-23)

### What's fixed
- Bind missing fieldtypes to avoid errors during the migration process #49 by @duncanmcclean
- Added note about deleting order and customer collections after migrating by @duncanmcclean
- Don't assume the Simple Commerce `coupons` directory exists during migration #44 by @Jamesking56
- Added output to `cargo:migrate:products` command #47 by @duncanmcclean
- Fixed `undefined array key "title"` error when migrating taxes #43 by @duncanmcclean
- Removed `tailwindcss` plugin from Vite config by @duncanmcclean



## v1.0.0-alpha.6 (2025-09-16)

### What's fixed
- Allow saving decimal tax rates #41 by @duncanmcclean



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