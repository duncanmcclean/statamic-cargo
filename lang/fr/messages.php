<?php

return [
    'discount_configure_intro' => 'Proposez des réductions à vos clients, en pourcentage ou sous forme de montant fixe.',
    'discounts' => [
        'customers' => 'Sélectionnez les clients pouvant bénéficier de cette réduction.',
        'discount_code' => 'Saisissez un code de réduction ou laissez ce champ vide pour appliquer la réduction automatiquement.',
        'maximum_uses' => 'Indiquez le nombre maximal d’utilisations. Laissez ce champ vide pour autoriser un nombre illimité d’utilisations.',
        'minimum_order_value' => 'Indiquez le montant minimal de la commande. Laissez ce champ vide pour ne pas imposer de montant minimal.',
        'title' => 'Donnez un titre à votre réduction. Il peut être visible par les clients.',
        'products' => 'Sélectionnez les produits auxquels cette réduction peut s’appliquer.',
        'type' => 'Sélectionnez le type de réduction à créer.',
    ],
    'products' => [
        'downloads' => 'Sélectionnez les fichiers disponibles au téléchargement avec ce produit.',
        'download_limit' => 'Indiquez le nombre maximal de téléchargements de ce produit par client. Laissez ce champ vide pour autoriser un nombre illimité de téléchargements.',
        'price_exclusive_of_tax' => 'Saisissez le prix hors taxes du produit.',
        'price_inclusive_of_tax' => 'Saisissez le prix toutes taxes comprises du produit.',
        'tax_class' => 'Détermine les taxes appliquées à ce produit.',
        'type' => 'Détermine le mode de livraison du produit.',
    ],
    'tax_class_intro' => 'Les catégories de taxes permettent de regrouper les produits selon leur régime de taxation. Elles sont utiles lorsque vos produits sont soumis à des taux différents.',
    'tax_classes_title_instructions' => 'Ce titre peut être visible par les clients dans le détail des taxes de leur commande.',
    'tax_zones_intro' => 'Définissez les taux de chaque catégorie de taxes, avec des taux différents selon le pays, l’État ou le code postal.',
    'tax_zones_rates_instructions' => 'Définissez les taux applicables à cette zone pour chaque catégorie de taxes.',
    'tax_zones_type_instructions' => 'Où cette zone de taxation doit-elle s’appliquer ?',
    'timeline_events' => [
        'order_created' => 'La commande a été créée',
        'order_refunded' => 'La commande a été remboursée à hauteur de :amount',
        'order_status_changed' => 'Le statut est passé à :status',
        'order_updated' => 'La commande a été mise à jour',
    ],
];
