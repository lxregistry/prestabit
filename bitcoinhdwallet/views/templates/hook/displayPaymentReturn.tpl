{*
* 2018 LX
*
* NOTICE OF LICENSE
*
*  @author    LX
*  @copyright 2018 LX
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{if $status == 'ok'}
	<p>{l s='Your order on %s is complete.' sprintf=[$shop_name] mod='bitcoinhdwallet'}</p>
	<div class="box order-confirmation">
		<h3 class="page-subheading">{l s='Use Bitcoin Address:' mod='bitcoinhdwallet'}</h3>
		<br />- {l s='Amount' mod='bitcoinhdwallet'} : <span class="price"><strong>{$total_to_pay}</strong></span>
	    <br />- {l s='Order reference' mod='bitcoinhdwallet'} : <span class="reference"><strong>{$reference}</strong></span>
		<br />- {l s='Send exactly' mod='bitcoinhdwallet'} <strong>{$value_in_BTC} BTC</strong> {l s='to this address' mod='bitcoinhdwallet'}:
		<div style="padding: 5px"><a target="_blank" style="background-color: white;" href="bitcoin:{$address}?amount={$value_in_BTC}&label=Order%3A{$reference}">{$address}</a></div>
		<div style="padding: 5px"><a target="_blank" style="background-color: white;" href="bitcoin:{$address}?amount={$value_in_BTC}&label=Order%3A{$reference}"><img src="https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=bitcoin%3A{$address}%3Famount%3D{$value_in_BTC}%26label%3DOrder%3A{$reference}"></a></div>
		<br />- <strong>{l s='Your order will be sent as soon as we receive your payment.' mod='bitcoinhdwallet'}</strong>
		<br />- {l s='If you have questions, comments or concerns, please contact our [1]expert customer support team[/1].' mod='bitcoinhdwallet' tags=["<a href='{$contact_url}'>"]}
	</div>
{else}
    <p class="warning">
      {l s='We noticed a problem with your order. If you think this is an error, feel free to contact our [1]expert customer support team[/1].' mod='bitcoinhdwallet' tags=["<a href='{$contact_url}'>"]}
    </p>
{/if}
