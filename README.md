#  Envios Kaguro Shipping for M2
## Installation

To install the **Envios Kaguro** extension.

Run the command _composer require envioskanguro/shipping_
```bash
composer require envioskanguro/shipping
```
There are to possibilities:

Please add our repository to your Magento _composer.json_

```bash
"require": {
    "envioskanguro/shipping": "~1.0"
}
```
Or 
```bash
{
    "repositories": [
        {
            "url": "git@github.com:envioskanguro/shipping",
            "type": "git"
        }
    ]
}
```
## Enable Module

To enable our module via Magento 2 CLI go to your Magento root and run:

    bin/magento module:enable --clear-static-content Envioskanguro_Shipping


To initialize the Database updates you must run following command afterwards:

    bin/magento setup:upgrade

The Envios Kaguro module should now be installed and ready to use.

## Issues
Please use our chat or contact form at: https://envioskanguro.com/#queremos_escucharte
