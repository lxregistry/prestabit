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

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Wallet.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Blockchain.php';

class BitcoinHdWallet extends PaymentModule
{

    public function __construct()
    {
        $this->name = 'bitcoinhdwallet';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.1';
        $this->author = 'LX';
        $this->module_key = 'd5af00e1dc5a9c80dc8fcf0d1ffbcd2f';
        $this->controllers = array('validation');

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        // https://blockchain.info/api/exchange_rates_api
        $this->limited_currencies = array('USD','ISK','HKD','TWD','CHF','EUR','DKK','CLP','CAD','CNY','THB','AUD','SGD','KRW','JPY','PLN','GBP','SEK','NZD','BRL','RUB');

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Direct Bitcoin Payments in HD Wallet');
        $this->description = $this->l('Accept bitcoin payments directly into your own HD wallet.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->ps_versions_compliancy['min'] = '1.7.4.2';
        $this->cron_token = Tools::encrypt($this->name);

        if (!Configuration::get('LX_EXTENDED_PUBLIC_KEY')) {
            $this->warning = $this->l('No Extended Public Key provided.');
        }
        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency has been set for this module.');
        }
    }


    public function install()
    {
        if (!extension_loaded('curl')) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module.');
            return false;
        }

        if (!extension_loaded('gmp')) {
            $this->_errors[] = $this->l('You have to enable the GMP extension on your server to install this module.');
            return false;
        }

        if (!Currency::exists('USD', 0) &&
            !Currency::exists('ISK', 0) &&
            !Currency::exists('HKD', 0) &&
            !Currency::exists('TWD', 0) &&
            !Currency::exists('CHF', 0) &&
            !Currency::exists('EUR', 0) &&
            !Currency::exists('DKK', 0) &&
            !Currency::exists('CLP', 0) &&
            !Currency::exists('CAD', 0) &&
            !Currency::exists('CNY', 0) &&
            !Currency::exists('THB', 0) &&
            !Currency::exists('AUD', 0) &&
            !Currency::exists('SGD', 0) &&
            !Currency::exists('KRW', 0) &&
            !Currency::exists('JPY', 0) &&
            !Currency::exists('PLN', 0) &&
            !Currency::exists('GBP', 0) &&
            !Currency::exists('SEK', 0) &&
            !Currency::exists('NZD', 0) &&
            !Currency::exists('BRL', 0) &&
            !Currency::exists('RUB', 0)) {
            $this->_errors[] = $this->l('Bitcoin Payment Method is only available when one of this (USD, ISK, HKD, TWD, CHF, EUR, DKK, CLP, CAD, CNY, THB, AUD, SGD, KRW, JPY, PLN, GBP, SEK, NZD, BRL, RUB) currency is activated.');
            return false;
        }

        if (!Configuration::get('LX_OS_AWAITING_BITCOIN_PAYMENT')) {
            $orderState = new OrderState();
            $orderState->name = array();
            $orderState->invoice = false;
            $orderState->send_email = true;
            $orderState->module_name = $this->name;
            $orderState->color = '#4169E1';
            $orderState->unremovable = true;
            $orderState->hidden = false;
            $orderState->logable = false;
            $orderState->delivery = false;
            $orderState->shipped = false;
            $orderState->paid = false;
            $orderState->pdf_delivery = false;
            $orderState->pdf_invoice = false;
            $orderState->deleted = false;
            $orderState->template = array();
            foreach (Language::getLanguages() as $language) {
                $orderState->name[$language['id_lang']] = 'Awaiting Bitcoin payment';
                $orderState->template[$language['id_lang']] = $this->name;
            }
            if ($orderState->add()) {
                Tools::copy($this->getLocalPath() . 'views/img/os.gif', _PS_ORDER_STATE_IMG_DIR_ . (int) $orderState->id . '.gif');
            }
            Configuration::updateValue('LX_OS_AWAITING_BITCOIN_PAYMENT', (int) $orderState->id);
        }

        foreach (Language::getLanguages() as $language) {
            if (!file_exists(_PS_MAIL_DIR_ . $language['iso_code'])) {
                mkdir(_PS_MAIL_DIR_ . $language['iso_code']);
            }
            if (!file_exists(_PS_MAIL_DIR_ . $language['iso_code'] . DIRECTORY_SEPARATOR . $this->name . '.html')) {
                Tools::copy($this->getLocalPath() . 'mails' . DIRECTORY_SEPARATOR . 'en' . DIRECTORY_SEPARATOR . $this->name . '.html', _PS_MAIL_DIR_ . $language['iso_code'] . DIRECTORY_SEPARATOR . $this->name . '.html');
            }
            if (!file_exists(_PS_MAIL_DIR_ . $language['iso_code'] . DIRECTORY_SEPARATOR . $this->name . '.txt')) {
                Tools::copy($this->getLocalPath() . 'mails' . DIRECTORY_SEPARATOR . 'en' . DIRECTORY_SEPARATOR . $this->name . '.txt', _PS_MAIL_DIR_ . $language['iso_code'] . DIRECTORY_SEPARATOR . $this->name . '.txt');
            }
        }

        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (!parent::install() ||
            !$this->registerHook('paymentOptions') ||
            !$this->registerHook('hookDisplayPaymentEU') ||
            !$this->registerHook('displayPaymentReturn') ||
            !$this->registerHook('displayAdminOrderLeft') ||
            !$this->registerHook('displayAdminOrderTabOrder') ||
            !$this->registerHook('displayAdminOrderContentOrder') ||
            !$this->registerHook('displayOrderDetail') ||
            !Configuration::updateValue('LX_EXTENDED_PUBLIC_KEY', false) ||
            !Configuration::updateValue('LX_FIRST_ADDRESS', false)) {
            return false;
        }

        include($this->getLocalPath() . 'install/install.php');

        return true;
    }


    public function uninstall()
    {
        if (!parent::uninstall() ||
            !Configuration::deleteByName('LX_EXTENDED_PUBLIC_KEY') ||
            !Configuration::deleteByName('LX_FIRST_ADDRESS')) {
            return false;
        }

        //$sql_a = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "lx_bitcoin_transaction`";

        //if (Db::getInstance()->execute($sql_a) == false) {
            //return false;
        //}

        //$sql_b = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "lx_bitcoin_address`";

        //if (Db::getInstance()->execute($sql_b) == false) {
            //return false;
        //}

        //$OrderState = new OrderState((int)Configuration::get('LX_OS_AWAITING_BITCOIN_PAYMENT'));
        //$OrderState->delete();
        //Configuration::deleteByName('LX_OS_AWAITING_BITCOIN_PAYMENT');

        return true;
    }


    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit' . $this->name)) {
            $lx_extended_public_key = Tools::getValue('LX_EXTENDED_PUBLIC_KEY');
            if (!$lx_extended_public_key || empty($lx_extended_public_key) || !Validate::isGenericName($lx_extended_public_key)) {
                $output .= $this->displayError($this->l('Invalid EXTENDED PUBLIC KEY Configuration value.'));
            } elseif (!preg_match('/^xpub[a-zA-Z0-9]{107}$/', $lx_extended_public_key)) {
                $output .= $this->displayError($this->l('Invalid EXTENDED PUBLIC KEY Configuration value.'));
            } else {
                Configuration::updateValue('LX_EXTENDED_PUBLIC_KEY', $lx_extended_public_key);
                $output .= $this->displayConfirmation($this->l('Settings EXTENDED PUBLIC KEY updated.'));
            }

            $lx_first_address = Tools::getValue('LX_FIRST_ADDRESS');
            $wallet = new Wallet;
            if (!$lx_first_address || empty($lx_first_address) || !Validate::isGenericName($lx_first_address)) {
                $output .= $this->displayError($this->l('Invalid FIRST ADDRESS Configuration value.'));
            } elseif (!preg_match('/^[13][a-km-zA-HJ-NP-Z1-9]{25,34}$/', $lx_first_address)) {
                $output .= $this->displayError($this->l('Invalid BITCOIN ADDRESS Configuration value.'));
            } elseif (!$wallet->isValidFirstAddress($lx_first_address)) {
                $output .= $this->displayError($this->l('Invalid FIRST WALLET ADDRESS Configuration value.'));
            } else {
                $blockchain = new Blockchain;
                $txs = $blockchain->transactions($lx_first_address, 0);
                if (!empty($txs)) {
                    $output .= $this->displayError($this->l('There must be no transactions in the First Address, and the following addresses.'));
                } else {
                    Configuration::updateValue('LX_FIRST_ADDRESS', $lx_first_address);
                    $output .= $this->displayConfirmation($this->l('Settings FIRST ADDRESS updated.'));
                }
            }
        }
        $this->context->smarty->assign('link', $this->context->link);
        $this->context->smarty->assign('token', $this->cron_token);
        $output .= $this->context->smarty->fetch($this->getLocalPath() . 'views/templates/admin/configure.tpl');
        return $output . $this->displayForm();
    }


    public function displayForm()
    {
        // Get default language
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $fields_form = array();
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-btc'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Extended Public Key'),
                    'name' => 'LX_EXTENDED_PUBLIC_KEY',
                    'hint' => $this->l('Master Public Key'),
                    'desc' => $this->l('Master Public Key from your Hierarchical Deterministic Wallet'),
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('First Address from Wallet'),
                    'name' => 'LX_FIRST_ADDRESS',
                    'hint' => $this->l('First Address from Wallet'),
                    'desc' => $this->l('First Address from your Hierarchical Deterministic Wallet'),
                    'required' => true
                )
            ),
            'submit' => array(
                'title' => $this->l('Save')
            )
        );

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;       // false -> remove toolbar
        $helper->toolbar_scroll = true;     // yes -> Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit' . $this->name;
        $helper->toolbar_btn = array(
            'save' => array(
                    'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' .$this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                    'desc' => $this->l('Save')
                ),
            'back' => array(
                    'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                    'desc' => $this->l('Back to list')
            )
        );

        // Load current value
        $helper->fields_value['LX_EXTENDED_PUBLIC_KEY'] = Configuration::get('LX_EXTENDED_PUBLIC_KEY');
        $helper->fields_value['LX_FIRST_ADDRESS'] = Configuration::get('LX_FIRST_ADDRESS');

        return $helper->generateForm($fields_form);
    }


    /**
     * This method is used to render the payment button,
     * Take care if the button should be displayed or not.
     */
    public function hookPaymentOptions($params)
    {
        $currency_id = $params['cart']->id_currency;
        $currency = new Currency((int) $currency_id);

        if (in_array($currency->iso_code, $this->limited_currencies) == false) {
            return false;
        }

        if (!$this->active || !$this->checkCurrency($params['cart'])) {
            return;
        }

        $this->smarty->assign(
            $this->getTemplateVars()
        );

        $newOption = new PaymentOption();
        $newOption->setCallToActionText($this->l('Pay in Bitcoin'))
                ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
                ->setAdditionalInformation($this->fetch('module:bitcoinhdwallet/views/templates/hook/paymentOptions.tpl'))
                ->setLogo(Media::getMediaPath($this->getLocalPath() . 'views/img/bitcoinhdwallet.png'));

        return array($newOption);
    }


    public function hookDisplayPaymentEU($params)
    {
        if (!$this->active || !$this->checkCurrency($params['cart'])) {
            return;
        }

        $payment_options = array(
            'cta_text' => $this->l('Pay in Bitcoin'),
            'action' => $this->context->link->getModuleLink($this->name, 'validation', array(), true)
        );

        return $payment_options;
    }


    public function hookdisplayPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        $state = $params['order']->getCurrentState();
        if (in_array($state, array(Configuration::get('LX_OS_AWAITING_BITCOIN_PAYMENT'), Configuration::get('PS_OS_OUTOFSTOCK'), Configuration::get('PS_OS_OUTOFSTOCK_UNPAID')))) {
            if (isset($params['order']->reference) && !empty($params['order']->reference)) {
                $this->smarty->assign('reference', $params['order']->reference);
            }
            $result = Db::getInstance()->getRow("SELECT * FROM `" . _DB_PREFIX_ . "lx_bitcoin_address` WHERE `id_order` = '" . (int) $params['order']->id . "'");
            if ($result) {
                $address = $result['address'];
                $value_in_BTC = $result['value_in_BTC'];

                if ($value_in_BTC == 0) {
                    $this->smarty->assign(
                        array(
                            'status' => 'failed',
                            'contact_url' => $this->context->link->getPageLink('contact', true),
                        )
                    );
                } else {
                    $this->smarty->assign(array(
                        'shop_name' => $this->context->shop->name,
                        'total_to_pay' => Tools::displayPrice($params['order']->getOrdersTotalPaid(), new Currency($params['order']->id_currency), false),
                        'address' => $address,
                        'value_in_BTC' => $value_in_BTC,
                        'status' => 'ok',
                        'id_order' => $params['order']->id,
                        'contact_url' => $this->context->link->getPageLink('contact', true)
                    ));
                }
            } else {
                $this->smarty->assign(
                    array(
                        'status' => 'failed',
                        'contact_url' => $this->context->link->getPageLink('contact', true),
                    )
                );
            }
        } else {
            $this->smarty->assign(
                array(
                    'status' => 'failed',
                    'contact_url' => $this->context->link->getPageLink('contact', true),
                )
            );
        }

        return $this->fetch('module:bitcoinhdwallet/views/templates/hook/displayPaymentReturn.tpl');
    }


    public function hookDisplayAdminOrderLeft($params)
    {
        $order = new Order($params['id_order']);

        if (Validate::isLoadedObject($order) && $order->module == $this->name) {
            $result = Db::getInstance()->getRow("SELECT * FROM `" . _DB_PREFIX_ . "lx_bitcoin_address` WHERE `id_order` = '" . (int) $order->id . "'");
            if ($result) {
                $address = $result['address'];
            } else {
                $address = 'NONE';
            }

            $this->smarty->assign('address', $address);

            return $this->display(__FILE__, '/views/templates/hook/displayAdminOrderLeft.tpl');
        }
    }


    public function hookdisplayAdminOrderTabOrder($params)
    {
        $order = $params['order'];

        if (Validate::isLoadedObject($order) && $order->module == $this->name) {
            $result = Db::getInstance()->getRow("SELECT * FROM `" . _DB_PREFIX_ . "lx_bitcoin_address` WHERE `id_order` = '" . (int) $order->id . "'");
            if ($result) {
                $address = $result['address'];
            } else {
                $address = 'NONE';
            }

            $total_transactions = Db::getInstance()->getValue("SELECT COUNT(*) FROM `" . _DB_PREFIX_ . "lx_bitcoin_transaction` WHERE `address` = '" . pSQL($address) . "'");

            $this->smarty->assign('total_transactions', $total_transactions);

            return $this->display(__FILE__, '/views/templates/hook/displayAdminOrderTabOrder.tpl');
        }
    }


    public function hookdisplayAdminOrderContentOrder($params)
    {
        $order = $params['order'];

        if (Validate::isLoadedObject($order) && $order->module == $this->name) {
            $result = Db::getInstance()->getRow("SELECT * FROM `" . _DB_PREFIX_ . "lx_bitcoin_address` WHERE `id_order` = '" . (int) $order->id . "'");
            if ($result) {
                $address = $result['address'];
                $value_in_BTC = $result['value_in_BTC'];
                $status = $result['status'];
            } else {
                $address = 'NONE';
                $value_in_BTC = 0;
                $status = 'NONE';
            }

            $total_transactions = Db::getInstance()->getValue("SELECT COUNT(*) FROM `" . _DB_PREFIX_ . "lx_bitcoin_transaction` WHERE `address` = '" . pSQL($address) . "'");
            $res = Db::getInstance()->executeS("SELECT * FROM `" . _DB_PREFIX_ . "lx_bitcoin_transaction` WHERE `address` = '" . pSQL($address) . "'");

            $transactions = array();
            $nr = 0;
            foreach ($res as $row) {
                $nr++;
                $row['nr'] = $nr;
                $row['transaction_hash_truncated'] = Tools::substr($row['transaction_hash'], 0, 8) . ' ...';
                $row['value_in_BTC'] = number_format(($row['value_in_satoshi'] / 100000000), 8);
                $transactions[] = $row;
            }

            $total_paid_in_satoshi = (double) Db::getInstance()->getValue("
                SELECT SUM(`value_in_satoshi`)
                FROM `" . _DB_PREFIX_ . "lx_bitcoin_transaction`
                WHERE `address` = '" . pSQL($address) . "'");

            $total_paid_in_btc = $total_paid_in_satoshi / 100000000;

            $this->smarty->assign(array(
                'address' => $address,
                'value_in_BTC' => $value_in_BTC,
                'status' => $status,
                'total_transactions' => $total_transactions,
                'transactions' => $transactions,
                'total_paid_in_BTC' => $total_paid_in_btc
            ));

            return $this->display(__FILE__, '/views/templates/hook/displayAdminOrderContentOrder.tpl');
        }
    }


    public function hookdisplayOrderDetail($params)
    {
        $order = $params['order'];

        if (Validate::isLoadedObject($order) && $order->module == $this->name) {
            $result = Db::getInstance()->getRow("SELECT * FROM `" . _DB_PREFIX_ . "lx_bitcoin_address` WHERE `id_order` = '" . (int) $order->id . "'");
            if ($result) {
                $address = $result['address'];
                $value_in_BTC = $result['value_in_BTC'];
                $status = $result['status'];
            } else {
                $address = 'NONE';
                $value_in_BTC = 0;
                $status = 'NONE';
            }

            $total_transactions = Db::getInstance()->getValue("SELECT COUNT(*) FROM `" . _DB_PREFIX_ . "lx_bitcoin_transaction` WHERE `address` = '" . pSQL($address) . "'");
            $res = Db::getInstance()->executeS("SELECT * FROM `" . _DB_PREFIX_ . "lx_bitcoin_transaction` WHERE `address` = '" . pSQL($address) . "'");

            $transactions = array();
            $nr = 0;
            foreach ($res as $row) {
                $nr++;
                $row['nr'] = $nr;
                $row['transaction_hash_truncated'] = Tools::substr($row['transaction_hash'], 0, 8) . ' ...';
                $row['value_in_BTC'] = number_format(($row['value_in_satoshi'] / 100000000), 8);
                $transactions[] = $row;
            }

            $total_paid_in_satoshi = (double) Db::getInstance()->getValue("
                SELECT SUM(`value_in_satoshi`)
                FROM `" . _DB_PREFIX_ . "lx_bitcoin_transaction`
                WHERE `address` = '" . pSQL($address) . "'");

            $total_paid_in_btc = $total_paid_in_satoshi / 100000000;

            $this->smarty->assign(array(
                'address' => $address,
                'value_in_BTC' => $value_in_BTC,
                'status' => $status,
                'total_transactions' => $total_transactions,
                'transactions' => $transactions,
                'total_paid_in_BTC' => $total_paid_in_btc
            ));

            return $this->fetch('module:bitcoinhdwallet/views/templates/hook/displayOrderDetail.tpl');
        }
    }


    public function checkCurrency($cart)
    {
        $currency_order = new Currency((int) ($cart->id_currency));
        $currencies_module = $this->getCurrency((int) $cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }

            return false;
        }
    }


    public function getTemplateVars()
    {
        $cart = $this->context->cart;
        $total = Tools::displayPrice($cart->getOrderTotal(true, Cart::BOTH));
        $currency_iso_code = $this->context->currency->iso_code;
        $amountdue = $cart->getOrderTotal(true, Cart::BOTH);
        $value_in_BTC = Tools::file_get_contents('https://blockchain.info/tobtc?currency=' . $currency_iso_code . '&value=' . $amountdue . '');

        $rate = Tools::ps_round(($amountdue / $value_in_BTC), 2);

        return array(
            'orderTotal' => $total,
            'rate' => '1 BTC = ' . $rate . ' ' . $currency_iso_code,
            'value_in_BTC' => $value_in_BTC
        );
    }


    public function getNewAddress()
    {
        $wallet = new Wallet;

        $id = (int)Db::getInstance()->getValue("SELECT `id` FROM `" . _DB_PREFIX_ . "lx_bitcoin_address` ORDER BY `id` DESC");

        return $wallet->getNewBitcoinAddress($id + 1);
    }


    public function checkOrderPayment($id_order)
    {
        $lx = Db::getInstance()->getRow("SELECT `value_in_BTC`, `address` FROM `" . _DB_PREFIX_ . "lx_bitcoin_address` WHERE `id_order` = '" . (int) $id_order . "'");

        $blockchain = new Blockchain;
        if (!$blockchain->balance($lx['address'], 1)) {
            return false;
        }

        $txs = $blockchain->transactions($lx['address']);

        if (!is_array($txs)) {
            return false;
        }

        $order = new Order((int) $id_order);

        foreach ($txs as $tx) {
            $crdate = date('Y-m-d H:i:s', $tx['time']);
            Db::getInstance()->execute("INSERT IGNORE INTO `" . _DB_PREFIX_ . "lx_bitcoin_transaction` 
                    (`transaction_hash`, `address`, `confirmations`, `value_in_satoshi`, `crdate`) 
                VALUES
                    ('" . pSQL($tx['id']) . "', '" . pSQL($lx['address']) . "', '" . (int) $tx['confirmations'] . "', '" . (double) $tx['amount'] . "', '" . pSQL($crdate) . "')");

            $order_currency = new Currency($order->id_currency);
            $amount = Tools::ps_round(($order->getOrdersTotalPaid() * ($tx['amount'] / 100000000) / $lx['value_in_BTC']), 2);

            $id_order_payment = (int)Db::getInstance()->getValue("SELECT `id_order_payment` FROM `" . _DB_PREFIX_ . "order_payment` WHERE `transaction_id` = '" . pSQL($tx['id']) . "'");
            if (!$id_order_payment) {
                if (!$order->addOrderPayment($amount, 'Bitcoin', $tx['id'], $order_currency, date('Y-m-d H:i:s', $tx['time']))) {
                    PrestaShopLogger::addLog('PaymentModule::bitcoinhdwallet - Cannot save Order Payment', 3, null, 'Cart', (int) $order->id_cart, true);
                    throw new PrestaShopException('Can\'t save Order Payment');
                }
            }

            if ($tx['confirmations'] <= 5) {
                Db::getInstance()->execute("UPDATE `" . _DB_PREFIX_ . "lx_bitcoin_address` SET `status` = 'AwaitingConfirmations' WHERE `address` = '" . pSQL($lx['address']) . "'");
                Db::getInstance()->execute("UPDATE `" . _DB_PREFIX_ . "lx_bitcoin_transaction` SET `confirmations` = '" . (int) $tx['confirmations'] . "' WHERE `transaction_hash` = '" . pSQL($tx['id']) . "'");

                echo '*waiting 6 confirmations*';
            } elseif ($tx['confirmations'] > 5) {
                Db::getInstance()->execute("UPDATE `" . _DB_PREFIX_ . "lx_bitcoin_transaction` SET `confirmations` = '" . (int) $tx['confirmations'] . "' WHERE `transaction_hash` = '" . pSQL($tx['id']) . "'");
                $total_paid_in_satoshi = (double) Db::getInstance()->getValue("
                    SELECT SUM(`value_in_satoshi`)
                    FROM `" . _DB_PREFIX_ . "lx_bitcoin_transaction`
                    WHERE `address` = '" . pSQL($lx['address']) . "' AND `confirmations` > 5");

                $total_paid_in_btc = $total_paid_in_satoshi / 100000000;

                if ($total_paid_in_btc >= $lx['value_in_BTC']) {
                    if ($total_paid_in_btc == $lx['value_in_BTC']) {
                        Db::getInstance()->execute("UPDATE `" . _DB_PREFIX_ . "lx_bitcoin_address` SET `status` = 'Paid' WHERE `address` = '" . pSQL($lx['address']) . "'");
                    } elseif ($total_paid_in_btc > $lx['value_in_BTC']) {
                        Db::getInstance()->execute("UPDATE `" . _DB_PREFIX_ . "lx_bitcoin_address` SET `status` = 'OverPaid' WHERE `address` = '" . pSQL($lx['address']) . "'");
                    }
                } elseif ($total_paid_in_btc < $lx['value_in_BTC']) {
                    Db::getInstance()->execute("UPDATE `" . _DB_PREFIX_ . "lx_bitcoin_address` SET `status` = 'UnderPaid' WHERE `address` = '" . pSQL($lx['address']) . "'");
                }

                echo '*ok*';
            }
        }

        $total_paid_in_satoshi = (double) Db::getInstance()->getValue("
            SELECT SUM(`value_in_satoshi`)
            FROM `" . _DB_PREFIX_ . "lx_bitcoin_transaction`
            WHERE `address` = '" . pSQL($lx['address']) . "' AND `confirmations` > 5");

        $total_paid_in_btc = $total_paid_in_satoshi / 100000000;

        if ($total_paid_in_btc >= $lx['value_in_BTC']) {
            $history = new OrderHistory();
            $history->id_order = $order->id;
            $history->changeIdOrderState((int) Configuration::get('PS_OS_PAYMENT'), $order->id, true);
            $history->addWithemail();
        }

        return true;
    }
}
