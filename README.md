Please contact [Nexio integration support](nexiointegrations.slack.com)
if you would like to integrate with Magento through [Nexio](nexiohub.com).

# Description

A Nexio Magento extension. Takes credit card payments directly on your Magento store using Nexio.
Accept credit card transactions with [Nexio's](https://nexiopay.com/) payment platform. 

# Requirements
- Megento 2.4 and higher
- Requires PHP: 7.4

# Installation:

## Installation via composer

Navigate to your Magento project directory and update your composer.json file. 
```
composer require nexiopay/nexio-magento2-ext
```

Verify that the extension installed properly, run the following command: 
```
bin/magento module:status Nexio_OnlinePayment
```

By default, the extension is disabled:
```
Module is disabled
```

Enable the extension
```
bin/magento module:enable Nexio_OnlinePayment
```

Register the extension
```
bin/magento setup:upgrade
```


Recompile your Magento project.
```
bin/magento setup:di:compile
```

Deploy static content
```
php bin/magento setup:static-content:deploy
```

Verify that the extension is enabled
```
bin/magento module:status Nexio_OnlinePayment
```

## Manually install the extension

Download latest version of extension from 
```
https://github.com/nexiopay/nexio-magento
```

Extract into Magento installpath/app/code

For example, suppose your magento installation path is xampp/htdocs/magento, 

then you should copy the extension into xampp/htdocs/magento/app/code/

Register the extension
```
bin/magento setup:upgrade
```


Recompile your Magento project.
```
bin/magento setup:di:compile
```

Deploy static content
```
php bin/magento setup:static-content:deploy
```


# Configuration

####Log in to your Magento Administration page.

####Enable the payment method extension:

   - On the left-hand menu, Select STORES -> Configuration -> SALES -> Payment Methods.

   - Find `Nexio Payment` in OTHER PAYMENT METHODS.
 
   - Config following parameters:
   
     - **Enabled**: Choose `Yes` to active  `Nexio Payment` method
   
     - **Title**: Credit Card (Nexio)
   
     - **Payment from Applicable Countries**: Your choice (Select All Allowed Countries or Select Specific Countries)
   
     - **Instructions**: Your choice (See below for an example.)
   
   - Click ‘Save changes’ button
    
Example:
![Payment methods example](screenshots/paymentMethods.png)

####Configure the Nexio settings and set parameters:

   - On the left-hand menu, Select STORES -> Configuration -> NEXIO -> Settings.
   - Under ‘NEXIO’ click on ‘Settings’.
   - Open the ‘Configuration’ tab.
   - Type in the following fields:
        - **User Name**: Your Nexio username
        - **Password**: Your Nexio password
        _(If you have questions or if you need a Nexio username and password, please contact integrations@nexiopay.com)_
        - **Iframe Height**: Set iframe height
        - **Iframe Width**: Set iframe width
        - **API URL**:
            - For testing: https://api.nexiopaysandbox.com/
            - For production: https://api.nexiopay.com/
        - **Custom CSS url**: The URL where your CSS file is hosted (required for custom CSS).
        - **Custom Text File**: The URL where your custom text file is hosted.
        - **Sandbox public key**: Set public key

     Example:
    ![Payment methods example](screenshots/paymentMethodSettings1.png)


   - Scroll down and Open the ‘Advance Settings’ tab and type the following fields:
        - **Web hook fail URL**: The web hook fail url (baseurl/onlinepayment/index/webhook).
        - **Web hook URL**: The web hook url (baseurl/onlinepayment/index/webhook).
        - **Merchant Id**: The merchant id of your Nexio account.
        - **Auth Only**: Make the transaction auth only.
        - **3DS Check**: Enable or disable 3DS check.
        - **Fraud Check**: Enable fraud check through Kount.
            _(If you would like to enable Kount on your Nexio account, please contact integrations@nexiopay.com)_
        - **Hide Billing**: Hide billing info.
        - **Require CVC**: Require CVC in Nexio form.
        - **Hide CVC**: Hide CVC.
    f. Click ‘Save config’.
    Example:
    ![Payment methods example](screenshots/paymentMethodSettings2.png)


   - Scroll down and Open the Webhook Service’ tab and type the following fields:
        - **Webhook Transactions**: baseurl/onlinepayment/index/webhook.
             _(If you want to set transaction url for all event)_
        - Click ‘Webhook Configuration’ for configure webhooks with merchant id.
    Example:
    ![Payment methods example](screenshots/paymentMethodSettings3.png)

