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

class Blockchain
{
    public static function balance($address, $nb_confirmations = 0)
    {
        $balance = \Tools::file_get_contents(sprintf('https://blockchain.info/q/addressbalance/%s?confirmations=%d', $address, $nb_confirmations));

        return is_numeric($balance) ? $balance : false;
    }


    public static function transactions($address)
    {
        $block_height = \Tools::file_get_contents('https://blockchain.info/fr/q/getblockcount');

        if (!is_numeric($block_height)) {
            return false;
        }

        $result = \Tools::file_get_contents(sprintf('https://blockchain.info/fr/address/%s?format=json', $address));

        $data = Tools::jsonDecode($result, true);

        if (!isset($data['txs'])) {
            return false;
        }

        $return = array();

        foreach ($data['txs'] as $tx) {
            $amount = 0;

            foreach ($tx['out'] as $out) {
                $amount += ($out['addr'] === $address) ? $out['value'] : 0;
            }

            $tx_block_height = isset($tx['block_height']) ? (int) $tx['block_height'] : 0;
            $confirmations = $block_height - $tx_block_height + 1;

            if ($amount > 0) {
                $return[] = array(
                    'id' => $tx['hash'],
                    'time' => $tx['time'],
                    'amount' => $amount,
                    'confirmations' => $confirmations
                );
            }
        }

        return $return;
    }
}
