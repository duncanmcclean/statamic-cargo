import DiscountsIndex from './pages/discounts/Index.vue';
import DiscountsEmpty from './pages/discounts/Empty.vue';
import CustomersFieldtype from './components/fieldtypes/CustomersFieldtype.vue';
import CustomersFieldtypeIndex from './components/fieldtypes/CustomersFieldtypeIndex.vue';
import DiscountCodeFieldtype from './components/fieldtypes/DiscountCodeFieldtype.vue';
import MoneyFieldtype from './components/fieldtypes/MoneyFieldtype.vue';
import OrderReceiptFieldtype from './components/fieldtypes/OrderReceiptFieldtype.vue';
import ProductVariantsFieldtype from './components/fieldtypes/ProductVariantsFieldtype.vue';
import OrderStatusFieldtypeIndex from './components/fieldtypes/OrderStatusFieldtypeIndex.vue';
import PaymentDetailsFieldtype from './components/fieldtypes/PaymentDetailsFieldtype.vue';
import ShippingDetailsFieldtype from './components/fieldtypes/ShippingDetailsFieldtype.vue';
import StatesFieldtype from './components/fieldtypes/StatesFieldtype.vue';
import OrdersListing from './components/orders/Listing.vue';
import OrderPublishForm from './components/orders/PublishForm.vue';
import TaxClassesIndex from './pages/tax-classes/Index.vue';
import TaxClassesEmpty from './pages/tax-classes/Empty.vue';
import TaxZonesIndex from './pages/tax-zones/Index.vue';
import TaxZonesEmpty from './pages/tax-zones/Empty.vue';

Statamic.booting(() => {
    // Discounts
    Statamic.$inertia.register('cargo::Discounts/Index', DiscountsIndex);
    Statamic.$inertia.register('cargo::Discounts/Empty', DiscountsEmpty);

    // Fieldtypes
    Statamic.$components.register('customers-fieldtype', CustomersFieldtype);
    Statamic.$components.register('customers-fieldtype-index', CustomersFieldtypeIndex);
    Statamic.$components.register('discount_code-fieldtype', DiscountCodeFieldtype);
    Statamic.$components.register('money-fieldtype', MoneyFieldtype);
    Statamic.$components.register('order_receipt-fieldtype', OrderReceiptFieldtype);
    Statamic.$components.register('product_variants-fieldtype', ProductVariantsFieldtype);
    Statamic.$components.register('order_status-fieldtype-index', OrderStatusFieldtypeIndex);
    
    Statamic.$components.register('payment_details-fieldtype', PaymentDetailsFieldtype);
    Statamic.$components.register('shipping_details-fieldtype', ShippingDetailsFieldtype);
    Statamic.$components.register('states-fieldtype', StatesFieldtype);

    // Orders
    Statamic.$components.register('orders-listing', OrdersListing);
    Statamic.$components.register('order-publish-form', OrderPublishForm);

    // Tax Classes
    Statamic.$inertia.register('cargo::TaxClasses/Index', TaxClassesIndex);
    Statamic.$inertia.register('cargo::TaxClasses/Empty', TaxClassesEmpty);

    // Tax Zones
    Statamic.$inertia.register('cargo::TaxZones/Index', TaxZonesIndex);
    Statamic.$inertia.register('cargo::TaxZones/Empty', TaxZonesEmpty);
});
