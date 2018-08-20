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
   <div id="bitcoin" class="tab-pane">
      <h4 class="visible-print">{l s='Bitcoin payments' mod='bitcoinhdwallet'} <span class="badge">({$total_transactions|escape:'htmlall':'UTF-8'})</span></h4>
      <div class="table-responsive">
         <table id="bitcoin_table" class="table">
            <thead>
               <tr>
                  <th>
                     <span class="title_box">{l s='Bitcoin address assigned to this order' mod='bitcoinhdwallet'}</span>
                  </th>
                  <th>
                     <span class="title_box ">{l s='Order value in BTC' mod='bitcoinhdwallet'}</span>
                  </th>
                  <th>
                     <span class="title_box ">{l s='Status' mod='bitcoinhdwallet'}</span>
                  </th>
               </tr>
            </thead>
            <tbody>
               <tr>
                  <td>{$address|escape:'htmlall':'UTF-8'}</td>
                  <td><strong>{$value_in_BTC|escape:'htmlall':'UTF-8'}</strong></td>
                  <td><strong>{$status|escape:'htmlall':'UTF-8'}</strong></td>
               </tr>
            </tbody>
         </table>

         <br />

         <table id="transactions_table" class="table">
            <thead>
               <tr>
                  <th>
                     <span class="title_box">#</span>
                  </th>
                  <th>
                     <span class="title_box">{l s='Transactions' mod='bitcoinhdwallet'}</span>
                  </th>
                  <th>
                     <span class="title_box ">{l s='Created' mod='bitcoinhdwallet'}</span>
                  </th>
                  <th>
                     <span class="title_box ">{l s='Confirmed' mod='bitcoinhdwallet'}</span>
                  </th>
                  <th>
                     <span class="title_box ">{l s='Confirmations' mod='bitcoinhdwallet'}</span>
                  </th>
                  <th>
                     <span class="title_box ">{l s='Value in BTC' mod='bitcoinhdwallet'}</span>
                  </th>
               </tr>
            </thead>
            <tbody>
               {foreach from=$transactions item=transaction}
               <tr>
                  <td>{$transaction.nr|escape:'html':'UTF-8'}</td>
                  <td><a href="https://blockchain.info/tx/{$transaction.transaction_hash|escape:'html':'UTF-8'}" target="_blank">{$transaction.transaction_hash_truncated|escape:'html':'UTF-8'}</a></td>
                  <td>{$transaction.crdate|escape:'html':'UTF-8'}</td>
                  <td>{$transaction.update|escape:'html':'UTF-8'}</td>
                  <td>{$transaction.confirmations|escape:'html':'UTF-8'}</td>
                  <td class="amount text-right nowrap">{$transaction.value_in_BTC|escape:'html':'UTF-8'}</td>
               </tr>
               {foreachelse}
               <tr>
                  <td colspan="6">{l s='No transaction in progress' mod='bitcoinhdwallet'}</td>
               </tr>
               {/foreach}
               <tr>
                  <td colspan="5" class="text-right"><strong>{l s='Total' mod='bitcoinhdwallet'}</strong></td>
                  <td class="amount text-right nowrap"><strong>{$total_paid_in_BTC|escape:'htmlall':'UTF-8'}</strong></td>
               </tr>
            </tbody>
         </table>
      </div>
   </div>
