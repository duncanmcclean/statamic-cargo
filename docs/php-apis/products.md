---
title: "PHP APIs: Products"
---

Since products are just entries, you can use the [Entry Repository](https://statamic.dev/repositories/entry-repository) and the associated `Entry` class to work with products.

### Product Variants
If you need to add product variants programatically (maybe for a custom importer), you should first create an example product, add a couple of options/variants to see how it gets saved.

Then, in your custom code, you should try and construct the array using the same format.