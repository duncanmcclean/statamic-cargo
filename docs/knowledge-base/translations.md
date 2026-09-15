---
title: Translations
---

Cargo is developed in English. However, if you speak another language, you're more than welcome to contribute translations back to Cargo.

Any validation messages on the frontend or text in the Control Panel can be translated.

## Configuration
You can read more about changing the language used in the Control Panel over on the [Statamic docs](https://statamic.dev/cp-translations#configuration).

Cargo includes French translations (`fr`) for the Control Panel, validation messages, prebuilt checkout, packing slips, and email notification templates. Set your application's locale to `fr` to use them. The Control Panel locale can also be overridden in each user's preferences.

Translations are provided in `lang/fr.json` for short strings and in `lang/fr/` for field instructions and validation messages. You can override JSON strings in your application's `lang/fr.json`, and PHP translations in `lang/vendor/cargo/fr/`.

The checkout and email templates are copied into your application when installed. Existing installations should update their published templates to use the translation helpers, preserving any customizations.

## State names
State names are translated using the `cargo::states.{country}.{code}` key, where `{country}` is the ISO 3166-1 alpha-3 country code and `{code}` is the state's code. Cargo ships German translations for Germany, Austria and Switzerland.

To translate or override state names in your app, create a `lang/vendor/cargo/{locale}/states.php` file:

```php
return [
    'DEU' => [
        'BY' => 'Bayern',
        'HE' => 'Hessen',
    ],
];
```

Any states you don't list will fall back to the name in `states.json`.

## Contributing translations
1. In your app, create a `{locale}.json` file in the `lang` directory (or `resources/lang` for older apps).
	* The locale should follow the [language code standard](https://www.science.co.il/language/Codes.php).
2. Find a list of the [translatable strings](https://github.com/search?q=repo%3Aduncanmcclean%2Fstatamic-cargo+__%28&type=code) by searching `__(` in Cargo's code. 
3. In the JSON file you created, ensure the key is the English text and the value is your translated text:

```json
{
	"Orders": "**Bestellungen**"
}
```

4. Follow these steps if you'd like to contribute your translations to Cargo:
	1. [Fork](https://github.com/duncanmcclean/statamic-cargo/fork) the `statamic-cargo` repository
	2. Copy your JSON file into the `lang` directory.
	3. Commit, push and [open a pull request](https://github.com/duncanmcclean/statamic-cargo/pulls)