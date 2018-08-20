{*
* 2018 LX
*
* NOTICE OF LICENSE
*
*  @author    LX
*  @copyright 2018 LX
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<!-- Tab bitcoin -->
<div id="bitcoin" class="box hidden-sm-down">
   <div id="bitcoin-qrcode">
      <img src="https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=bitcoin%3A{$address}%3Famount%3D{$value_in_BTC}">
   </div>

   <div id="bitcoin-address">
         <table id="bitcoin_table" class="table table-bordered">
            <thead class="thead-default">
               <tr>
                  <th>{l s='Bitcoin address assigned to this order' mod='bitcoinhdwallet'}</th>
                  <th>{l s='Order value in BTC' mod='bitcoinhdwallet'}</th>
                  <th>{l s='Status' mod='bitcoinhdwallet'}</th>
               </tr>
            </thead>
            <tbody>
               <tr>
                  <td><a href="https://blockchain.info/address/{$address}" target="_blank">{$address}</a></td>
                  <td class="text-xs-right"><strong>{$value_in_BTC}</strong></td>
                  <td><strong>{$status}</strong></td>
               </tr>
            </tbody>
         </table>
   </div>

   <div id="bitcoin-transaction">
         <table id="transactions_table" class="table table-bordered">
            <thead class="thead-default">
               <tr>
                  <th>#</th>
                  <th>{l s='Transactions' mod='bitcoinhdwallet'}</th>
                  <th>{l s='Created' mod='bitcoinhdwallet'}</th>
                  <th>{l s='Confirmed' mod='bitcoinhdwallet'}</th>
                  <th>{l s='Confirmations' mod='bitcoinhdwallet'}</th>
                  <th>{l s='Value in BTC' mod='bitcoinhdwallet'}</th>
               </tr>
            </thead>
            <tbody>
               {foreach from=$transactions item=transaction}
               <tr>
                  <td>{$transaction.nr}</td>
                  <td><a href="https://blockchain.info/tx/{$transaction.transaction_hash}" target="_blank">{$transaction.transaction_hash_truncated}</a></td>
                  <td>{$transaction.crdate}</td>
                  <td>{$transaction.update}</td>
                  <td>{$transaction.confirmations}</td>
                  <td class="text-xs-right">{$transaction.value_in_BTC}</td>
               </tr>
               {foreachelse}
               <tr>
                  <td colspan="6">{l s='No transaction in progress' mod='bitcoinhdwallet'}</td>
               </tr>
               {/foreach}
            </tbody>
            <tfoot>   
               <tr>
                  <td colspan="5" class="text-xs-right"><strong>{l s='Total' mod='bitcoinhdwallet'}</strong></td>
                  <td class="text-xs-right"><strong>{$total_paid_in_BTC}</strong></td>
               </tr>
            </tfoot>
         </table>
   </div>
</div>