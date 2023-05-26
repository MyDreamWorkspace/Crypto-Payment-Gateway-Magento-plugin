# Magento 2 GoCrypto Payment Gateway Extension

To install this extension on your local machine to should follow these steps.

## Step 1: Extract the GoCryptoPay.zip

1. Extract and copy to magento2 root folder/app/code/

2. You can check module status using this command
   Move to magento2 root folder on command prompt: 
        cd c:\xampp\htdocs\magento2\

``` xml
php bin/magento module:status
```

## Step 2: Install this extension by use these commands.


``` xml
php bin/magento module:enable Eligmaltd_GoCryptoPay
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy -f
php bin/magento cache:flush
```
