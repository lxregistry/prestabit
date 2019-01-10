# prestabit

bitcoinhdwallet - PrestaShop Bitcoin Payment Module

Compatibility with PrestaShop >= v1.7.5


Title:
	Direct Bitcoin Payments in HD Wallet

Overview:
	This module allows you to accept Bitcoin payments from your customers to your own Hierarchical Deterministic Wallet.

What this module does for you:
	This Prestashop module enables merchants' shop to accept Bitcoin payments directly into your Hierarchical Deterministic wallet. This is the quickest and easiest way to begin accepting automated Bitcoin payments. This module increments the merchants' shop value due to accepting payments anonymously from customers.

Features:
	This module manage the price conversion using Blockchain.info's Exchange Rates API. The price of the products must be in one of these currencies (USD,ISK,HKD,TWD,CHF,EUR,DKK,CLP,CAD,CNY,THB,AUD,SGD,KRW,JPY,PLN,GBP,SEK,NZD,BRL,RUB). The payments will be received directly in your wallet.

What your customers will like:
	The purchases performed using Bitcoin are treated anonymously. Moreover, it provides secured and fast payments.

Installation:
	Regular installation. You need to configure the module by adding the Extended Public Key where the payments will be received.

Recommendation:
	The private key for the merchant’s master public key can be kept offline for increased security.

Other:
	This module requires the cURL PHP extension. Bitcoin Payment Method is only available when at least one of these currencies is activated: USD, ISK, HKD, TWD, CHF, EUR, DKK, CLP, CAD, CNY, THB, AUD, SGD, KRW, JPY, PLN, GBP, SEK, NZD, BRL, RUB .





#### bitcoinhdwallet - PrestaShop Payment Module Setup

1. Obtain an Extended Public Key from your Wallet. (ex. Electrum)
2. Obtain First Bitcoin address from your Wallet.
3. Sign in to your PrestaShop admin area.
4. Under **Modules** click **Modules and Services**.
5. Upload 'bitcoinhdwallet.zip' module
6. Configure
7. Click **Save**.




#### HOW IT WORKS

When the customer clicks 'Pay with Bitcoin', he will be redirected to the page with a QR code and the Bitcoin address.
At this step, the order has the 'Awaiting Bitcoin payment' status. Once this order is paid and your shop receives zero confirmations,
the order status changes to 'AwaitingConfirmations'.
After 6 confirmations, the order status changes to 'Payment accepted'.
If the customer paid less then the order value, the order status changes to 'UnderPaid'.
