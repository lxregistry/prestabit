{*
* 2018 LX
*
* NOTICE OF LICENSE
*
*  @author    LX
*  @copyright 2018 LX
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<section>
  <p>{l s='You have chosen to pay with Bitcoin.' mod='bitcoinhdwallet'}
    <dl>
      <dt>{l s='Total:' mod='bitcoinhdwallet'}</dt>
      	<dd>{$orderTotal}</dd>
      <dt>{l s='Exchange rate:' mod='bitcoinhdwallet'}</dt>
      	<dd>{$rate}</dd>
      <dt>{l s='The total amount of your order to pay in Bitcoin:' mod='bitcoinhdwallet'}</dt>
      	<dd>{$value_in_BTC} BTC</dd>      	
    </dl>
    {l s='Bitcoin address information will be displayed on the next page.' mod='bitcoinhdwallet'}
  </p>
</section>
