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

$sql_a = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "lx_bitcoin_address` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_order` int(10) unsigned NOT NULL,
    `value_in_BTC` double NOT NULL,
    `address` varchar(64) NOT NULL,
    `status` enum('Pending','AwaitingConfirmations','UnderPaid','Paid','OverPaid') NOT NULL DEFAULT 'Pending',
    `crdate` datetime NOT NULL,
    `update` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `id_order` (`id_order`),
    UNIQUE KEY `address` (`address`)
    ) ENGINE=" . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

if (Db::getInstance()->execute($sql_a) == false) {
    return false;
}

$sql_b = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "lx_bitcoin_transaction` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `transaction_hash` varchar(255) NOT NULL,
    `address` varchar(64) NOT NULL,
    `confirmations` int(10) unsigned NOT NULL DEFAULT '0',
    `value_in_satoshi` double NOT NULL,
    `crdate` datetime NOT NULL,
    `update` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `transaction_hash` (`transaction_hash`)
    ) ENGINE=" . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

if (Db::getInstance()->execute($sql_b) == false) {
    return false;
}

if (_MYSQL_ENGINE_ == 'InnoDB') {
    $result1 = Db::getInstance()->getRow("SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE` WHERE `TABLE_NAME` = '" . _DB_PREFIX_ . "lx_bitcoin_address' AND `CONSTRAINT_NAME` = '" . _DB_PREFIX_ . "lx_bitcoin_address_ibfk_1' AND `TABLE_SCHEMA` = '" . _DB_NAME_ . "'");
    if (!$result1) {
        Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "lx_bitcoin_address` ADD CONSTRAINT `" . _DB_PREFIX_ . "lx_bitcoin_address_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `" . _DB_PREFIX_ . "orders` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE");
    }

    $result2 = Db::getInstance()->getRow("SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE` WHERE `TABLE_NAME` = '" . _DB_PREFIX_ . "lx_bitcoin_transaction' AND `CONSTRAINT_NAME` = '" . _DB_PREFIX_ . "lx_bitcoin_transaction_ibfk_1' AND `TABLE_SCHEMA` = '" . _DB_NAME_ . "'");
    if (!$result2) {
        Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "lx_bitcoin_transaction` ADD CONSTRAINT `" . _DB_PREFIX_ . "lx_bitcoin_transaction_ibfk_1` FOREIGN KEY (`address`) REFERENCES `" . _DB_PREFIX_ . "lx_bitcoin_address` (`address`) ON DELETE CASCADE ON UPDATE CASCADE");
    }

    //Db::getInstance()->execute("ALTER IGNORE TABLE `" . _DB_PREFIX_ . "lx_bitcoin_address` ADD CONSTRAINT `" . _DB_PREFIX_ . "lx_bitcoin_address_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `" . _DB_PREFIX_ . "orders` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE");
    //Db::getInstance()->execute("ALTER IGNORE TABLE `" . _DB_PREFIX_ . "lx_bitcoin_transaction` ADD CONSTRAINT `" . _DB_PREFIX_ . "lx_bitcoin_transaction_ibfk_1` FOREIGN KEY (`address`) REFERENCES `" . _DB_PREFIX_ . "lx_bitcoin_address` (`address`) ON DELETE CASCADE ON UPDATE CASCADE");
}
