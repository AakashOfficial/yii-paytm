<?php

//Place the Paytm configuration array inside your 'components' definitions
return array(
    // application components
    'components' => array(
        'paytm' => array(
            'class' => 'application.components.Paytm',
            'api_live' => false,
            'merchant_key' => 'PAYTM_MERCHANT_KEY',
            'merchant_id' => 'PAYTM_MERCHANT_MID',
            'website' => 'PAYTM_MERCHANT_WEBSITE',
            'industry_type_id' => 'PAYTM_INDUSTRY_TYPE_ID',
        ),
    ),
);
