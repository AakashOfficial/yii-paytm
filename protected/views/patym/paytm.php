<html>
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <h4>Processing ...</h4>
        <form id="checkout_form" name="checkout_form" method="post" action="<?= Yii::app()->paytm->txn_url; ?>">	
            <?php foreach ($param_list as $name => $value) : ?>
                <input type="hidden" name="<?= $name ?>" value="<?= $value; ?>">
            <?php endforeach; ?>
        </form>

        <script type="text/javascript">
            // self executing function
            (function () {
                // auto submit form
                document.getElementById("checkout_form").submit();
            })();
        </script>
    </body>
</html>