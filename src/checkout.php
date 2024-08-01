<?php


use OsSalahuddin\CbsClient\CyberSource;

$cybersource = new CyberSource();
$amount = 10;
$request = [
    'access_key' => $cybersource->getAccessKey(),
    'profile_id' => $cybersource->getProfileId(),
    'reference_number' => $_GET['orderId'],
    'transaction_uuid' => uniqid(),
    'signed_field_names' => 'access_key,profile_id,reference_number,transaction_uuid,signed_field_names,transaction_type,currency,unsigned_field_names,signed_date_time,locale,amount,bill_to_forename,bill_to_surname,bill_to_email,bill_to_address_city,bill_to_address_state,bill_to_address_line1,bill_to_address_country,bill_to_address_postal_code,payer_authentication_specification_version',
    'transaction_type' => 'sale',
    'currency' => 'BDT',
    'unsigned_field_names' => '',
    'signed_date_time' => gmdate("Y-m-d\TH:i:s\Z"),
    'locale' => 'en',
    'amount' => $amount,
    'bill_to_forename' => 'noreal',
    'bill_to_surname' => 'name',
    'bill_to_email' => 'null@cybersource.com',
    'bill_to_address_city' => 'Mountain View',
    'bill_to_address_state' => 'CA',
    'bill_to_address_line1' => '1295 Charleston Rd',
    'bill_to_address_country' => 'US',
    'bill_to_address_postal_code' => '94043',
    'payer_authentication_specification_version' => '2.2.0'
];

$paymentUrl = $cybersource->getPaymentUrl();
?>

<!DOCTYPE html>
<html>
<head>
    <noscript>
        <style type="text/css">
            .noscript {
                display: none;
            }
        </style>
    </noscript>
    <style type="text/css">
        .typewriter {
            display: inline-block;
        }

        .typewriter-text {
            display: inline-block;
            overflow: hidden;
            animation: typing 5s steps(30, end), blink .75s step-end infinite;
            white-space: nowrap;
            font-size: 15px;
            font-weight: 700;
            border-right: 2px solid orange;
            box-sizing: border-box;
            line-height: 15px;
            color: #FAA61A;
        }

        @keyframes typing {
            from {
                width: 0%
            }
            to {
                width: 100%
            }
        }

        @keyframes blink {
            from, to {
                border-color: transparent
            }
            50% {
                border-color: #FAA61A;
            }
        }
    </style>
</head>
<body>
<div style="text-align: center; margin: 0 auto">
    <h3>VISA CYBERSOURCE</h3>
    <div class="noscript">
        <div class="typewriter">
            <div class="typewriter-text">
                YOU WILL REDIRECT TO PAYMENT PAGE AUTOMATICALLY. PLEASE WAIT...
            </div>
        </div>
    </div>
</div>
<form id="payment_form" action="<?= $paymentUrl ?>" method="post">
    <?php
    foreach ($requests as $name => $value) {
        echo '<input type="hidden" name="' . $name . '" value="' . $value . '"/>';
    }
    ?>
</form>
<script type="text/javascript">
    window.onload = function () {
        document.forms['payment_form'].submit();
    }
</script>
</body>
</html>