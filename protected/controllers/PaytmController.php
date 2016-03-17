<?php

class PaytmController extends Controller {

    public function index() {
        $param_list = array();
        // Create an array having all required parameters for creating checksum.
        $param_list['MID'] = Yii::app()->paytm->merchant_id;
        $param_list['INDUSTRY_TYPE_ID'] = Yii::app()->paytm->industry_type_id;
        $param_list['CHANNEL_ID'] = Yii::app()->paytm->channel_id;
        $param_list['WEBSITE'] = Yii::app()->paytm->website;

        $param_list['REQUEST_TYPE'] = 'DEFAULT'; //or SUBSCRIBE

        $param_list['ORDER_ID'] = 'sample12345';
        $param_list['TXN_AMOUNT'] = '199'; //INR

        $param_list['CUST_ID'] = 'CUSTOMER_ID';
        $param_list['MOBILE_NO'] = 'CUSTOMER_PHONE'; //Mobile number of customer
        $param_list['EMAIL'] = 'CUSTOMER_EMAIL'; //Email ID of customer
        //Here checksum string will return by getChecksumFromArray() function.
        $checkSum = Yii::app()->paytm->getChecksumFromArray($param_list);
        $param_list['CHECKSUMHASH'] = $checkSum;

        //paytm processing begin
        $this->renderPartial('paytm', array('param_list' => $param_list));
    }

    /**
     * Paytm resonse handler
     */
    public function actionResponse() {
        //validation
        if ($_POST['STATUS'] == 'TXN_SUCCESS') {

            $param_list = $_POST;
            $paytm_checksum = isset($_POST["CHECKSUMHASH"]) ? $_POST["CHECKSUMHASH"] : ""; //Sent by Paytm pg
            //verify checksum
            $isValidChecksum = Yii::app()->paytm->verifychecksum_e($param_list, $paytm_checksum);
            //if checksum matched
            if ($isValidChecksum === true) {
                //mark order as success
                //send success email
                //show thank u page
                $this->redirect(array('paytm/confirm'));
                Yii::app()->end();
            }
        }
        //Transaction status is failure
        $this->redirect(array('paytm/error'));
    }

    public function actionConfirm() {
        $this->render('confirm');
    }

    public function actionError() {
        $this->render('error');
    }

}
