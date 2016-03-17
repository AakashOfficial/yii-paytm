yii-paytm
==========

A Paytm component for Yii framework



Installation
==========

Download the package, extract the component file (Paytm.php) to your components directory and checkout 
sample controller and view files to test the component.
Place the Paytm configuration array inside your 'components' definitions.

```php
'paytm' => array(
    'class' => 'application.components.Paytm',
    'api_live' => false,
    'merchant_key' => 'PAYTM_MERCHANT_KEY',
    'merchant_id' => 'PAYTM_MERCHANT_MID',
    'website' => 'PAYTM_MERCHANT_WEBSITE',
    'industry_type_id' => 'PAYTM_INDUSTRY_TYPE_ID',
),
```
