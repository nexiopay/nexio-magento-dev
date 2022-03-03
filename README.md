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
bin/magento module:status nexiopay/nexio-magento2-ext ???
```

By default, the extension is probably disabled:
```
Module is disabled
```

nexiopay/nexio-magento2-ext

1. Copy the `Nexio` entire folder into Magento installpath/app/code. For example, suppose your magento installation path is xampp/htdocs/magento, then you should copy the extension into xampp/htdocs/magento/app/code/
2. Run the following commands:
    a. run php bin/magento setup:upgrade
    b. run php bin/magento setup:di:compile
    c. run php bin/magento setup:static-content:deploy
3. Log in to your Magento Administration page.
4. Enable the payment method extenstion:
    a. On the left-hand menu, Select STORES -> Configuration -> SALES -> Payment Methods.
    b. Find `Nexio Payment` in OTHER PAYMENT METHODS.
    c. Config following parameters:
        - Scroll down to the ‘Nexio Payment’ 
        - **Enabled**: Choose `Yes` to active  `Nexio Payment` method
        - **Title**: Credit Card (Nexio)
        - **Payment from Applicable Countries**: Your choice (Select All Allowed Countries or Select Specific Countries)
        - **Instructions**: Your choice (See below for an example.)
        - Click ‘Save changes’ button
    
Example:
![Payment methods example](screenshots/paymentMethods.png)

5. Configure the Nexio settings and set parameters:
    a. On the left-hand menu, Select STORES -> Configuration -> NEXIO -> Settings.
    b. Under ‘NEXIO’ click on ‘Settings’.
    c. Open the ‘Configuration’ tab.
    d. Type in the following fields:
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


## Notes
- Requires at least: 2.4
- Tested up to: 2.4.0
- Requires PHP: 7.4
- Stable tag: 1.0.0
- License: GPLv3


## Changelog
* 2.4.0 - 2021-08-16

