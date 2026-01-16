## Cargo
Cargo is a comprehensive e-commerce addon for Statamic that makes it easy to sell both physical and digital products, keep track of inventory, and manage shipping and taxes.

### Core Concepts
- **Products** are the things being sold. They're normal Statamic entries, so you can treat them like any other collection/entry.
- **Discounts** let you discount a cart's grand total, calculated using a set of user-defined rules. Cargo supports site-wide (automatic) discounts and code-based discounts.
- **Carts** represent the products a customer intends to purchase.
- **Orders** are created from carts when the customer completes the checkout process. They're visible in the Control Panel.
- **Customers** represent the customer behind orders and carts. Customers can either be Statamic users or "guest customers" attached to individual orders.

### Working with Cargo
- When writing code, products can be queried and manipulated like any other Statamic collection entry.
- Reference the Cargo documentation for anything you're not sure about.

### Documentation
Browse the complete documentation optimized for LLM consumption at `https://builtwithcargo.dev/llms.txt`.