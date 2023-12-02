<!DOCTYPE html>
<html lang="en">

<!--
    * This file is part of the Laravel Bani package.
    *
    * (c) Prevail Ejimadu <prevailexcellent@gmail.com>
    *
    * For the full copyright and license information, please view the LICENSE
    * file that was distributed with this source code.
    */
-->

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Bani Laravel</title>

    <style>
        body {
            background-color: #dedede;
        }
    </style>

</head>

<body>    
    <script src="https://bani-assets.s3.eu-west-2.amazonaws.com/static/widget/js/window.js"></script>
    <script type="text/javascript">
        var data = @json($data);
        var callback = "{{ route('bani.lara.callback') }}" ?? data.callback;

        function payWithBani() {
            let handler = BaniPopUp({
                amount: data.amount, //The amount the customer wants to pay
                phoneNumber: data.phoneNumber, //The mobile number of the customer in int format i.e +2348173709000
                email: data.email, //The email of the customer
                firstName: data.firstName, //The first name of the customer
                lastName: data.lastName, //The last name of the customer
                merchantKey: data.merchantKey, //The merchant Bani public key
                metadata: data.metadata, //Custom JSON object passed by the merchant. This is optional
                merchantRef: data.merchantRef, //Custom payment reference passed by the merchant. This is optional
                customRef: data.merchantRef, //Custom payment reference passed by the merchant. This is optional
                onClose: (response) => {
                    console.log("ONCLOSE DATA",response)
                    // window.history.back();
                },
                callback: function(response) {
                    // Goes to the callback url with data
                    let url = `${callback}?paymentRef=${response?.reference}&paymentType=${response?.type ?? "fiat"}`;
                    window.location.href = url;
                }
            })
            handler
        }

        payWithBani();
    </script>
</body>

</html>
