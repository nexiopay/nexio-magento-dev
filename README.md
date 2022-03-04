Please contact [Nexio integration support](nexiointegrations.slack.com)
if you would like to integrate with Magento through [Nexio](nexiohub.com).

### Description

A Nexio Magento extension. Takes credit card payments directly on your Magento store using Nexio.
Accept credit card transactions with [Nexio's](https://nexiopay.com/) payment platform. 


## Installation:

### Installation via composer

Navigate to your Magento project directory and update your composer.json file. 
```
composer require nexiopay/nexio-magento2-ext
```

Verify that the extension installed properly, run the following command: 
```
bin/magento module:status Nexio_OnlinePayment
```

By default, the extension is probably disabled:
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

### Configuration

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

    e. Scroll down and Open the ‘Advance Settings’ tab and type the following fields:
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

    g. Scroll down and Open the Webhook Service’ tab and type the following fields:
        - **Webhook Transactions**: baseurl/onlinepayment/index/webhook.
             _(If you want to set transaction url for all event)_
        - Click ‘Webhook Configuration’ for configure webhooks with merchant id.
    Example:
    ![Payment methods example](screenshots/paymentMethodSettings3.png)

## Using the Extenstion:
1. Log in to your Magento Frontend page.
    
2. Add the product to cart:
    a. Go to your Magento shop or product page and the add product to your cart.

3. Check out:
    a. Once on the Cart page, click on the ‘Proceed to checkout’ button.
    ![Cart example](screenshots/cart.png)

    b. Fill your shipping details information and click on the ‘Next’ button..
    c. Choose ‘Credit Card’ as your payment method.
    Example:
    ![Checkout example](screenshots/checkoutPayment.png)
    
4. Select the default save card details or Fill in card information and submit the transaction:
    a. Once the select ‘Credit Card’ page has loaded the default save card details(If have the save card details).
    Example:
    ![Save card example](screenshots/saveCard.png)
    b. Click the ‘New card’ button page has loaded you will see a Nexio payment form. 
    b. Enter in the required fields.
    c. Click the ‘Place Order’ button to submit the transaction.
    d. If the transaction succeeds, you will see an ‘order received’ page, otherwise, it will return to checkout page for retry.
    Example:
    ![Place Order example](screenshots/placeOrder.png)

5. Save and Add the card details:
    a. Go to My Account and On the left-hand menu Select Stored Payment Methods
    b. Click the ‘Add Payment’ button modal has loaded you will see a Nexio form. 
    b. Enter in the required fields.
    c. Click the ‘Add Payment’ button to save the card details.
    d. You will see all save card details.
    Example:
    ![Stored Payment example](screenshots/storedPayment.png)


### Manually install the extension

1. Copy the `Nexio` entire folder into Magento installpath/app/code. For example, suppose your magento installation path is xampp/htdocs/magento, then you should copy the extension into xampp/htdocs/magento/app/code/
2. Run the following commands:
```
php bin/magento setup:upgrade
 ```
```
php bin/magento setup:di:compile
```
```
php bin/magento setup:static-content:deploy
```

## Notes
- Requires at least: 2.4
- Tested up to: 2.4.0
- Requires PHP: 7.4
- Stable tag: 1.0.0
- License: GPLv3


## Changelog
* 2.4.0 - 2021-08-16

