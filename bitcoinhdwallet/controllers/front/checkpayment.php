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

class BitcoinHdWalletCheckPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        ini_set('max_execution_time', 3600);

        if ($this->module->cron_token === Tools::getValue('token')) {
            // maybe an ignore here for old unpaid orders?
            $results = Db::getInstance()->executeS("SELECT `id_order` FROM `" . _DB_PREFIX_ . "orders` WHERE `current_state` = '" . (int)Configuration::get('LX_OS_AWAITING_BITCOIN_PAYMENT') . "'");
            foreach ($results as $result) {
                $this->module->checkOrderPayment($result['id_order']);
            }

            echo 'Done.';
        }

        die();
    }
}
