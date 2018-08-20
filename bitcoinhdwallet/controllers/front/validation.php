<?php
/**
* 2018 LX
*
* NOTICE OF LICENSE
*
*  @author    LX
*  @copyright 2018 LX
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class BitcoinHdWalletValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $cart = $this->context->cart;

        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'bitcoinhdwallet') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            die($this->module->l('This payment method is not available.', 'validation'));
        }

        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $currency = $this->context->currency;
        $currency_iso_code = $this->context->currency->iso_code;
        if (!in_array($currency_iso_code, $this->module->limited_currencies)) {
            Tools::redirect('index.php?controller=order');
        }

        $amountdue = (float)$cart->getOrderTotal(true, Cart::BOTH);
        $value_in_BTC = Tools::file_get_contents("https://blockchain.info/tobtc?currency=" . $currency_iso_code . "&value=" . $amountdue . "");

        $address = $this->module->getNewAddress();

        if (preg_match('/^[13][a-km-zA-HJ-NP-Z1-9]{25,34}$/', $address)) {
            $mailVars = array('{address}' => $address, '{value_in_BTC}' => $value_in_BTC);
            $this->module->validateOrder((int) $cart->id, Configuration::get('LX_OS_AWAITING_BITCOIN_PAYMENT'), $amountdue, $this->module->l('Bitcoin', 'validation'), null, $mailVars, (int) $currency->id, false, $customer->secure_key, null);
            $id_order = Order::getOrderByCartId($cart->id);

            Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "lx_bitcoin_address` 
                    (`id_order`,`value_in_BTC`,`address`,`status`,`crdate`) 
                VALUES
                    ('" . (int) $id_order . "', '" . (double) $value_in_BTC . "', '" . pSQL($address) . "', 'Pending', CURRENT_TIMESTAMP)");
        } else {
            // uncomment 3 lines below for debug
            //$logfile = fopen(dirname(__FILE__).'/error_log.txt', 'a+');
            //fwrite($logfile, $blockchain_url);
            //fclose($logfile);

            PrestaShopLogger::addLog('BitcoinHdWalletPaymentModuleFrontController - unable to generate address', 4, null, 'Cart', (int) $cart->id, true, null);
            die('An unrecoverable error occured: unable to generate new bitcoin address, check your xPub.');
        }
        unset($this->context->cookie->id_cart);
        Tools::redirect('index.php?controller=order-confirmation&id_cart=' . (int) $cart->id . '&id_module=' . (int) $this->module->id . '&id_order=' . (int) $id_order . '&key=' . $customer->secure_key);
    }
}
