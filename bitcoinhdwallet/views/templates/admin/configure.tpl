{*
* 2018 LX
*
* NOTICE OF LICENSE
*
*  @author    LX
*  @copyright 2018 LX
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<div class="panel">
	<h3><i class="icon icon-link"></i> {l s='Crontab' mod='bitcoinhdwallet'}</h3>
	<p>
		&raquo; {l s='Setup a crontab to run URL bellow, every 5 minute' mod='bitcoinhdwallet'}:
		<ul>
			<li>  <a href="{$link->getModuleLink('bitcoinhdwallet', 'checkpayment', ['token' => {$token|escape:'htmlall':'UTF-8'}], true)|escape:'html':'UTF-8'}" target="_blank">{$link->getModuleLink('bitcoinhdwallet', 'checkpayment', ['token' => {$token|escape:'htmlall':'UTF-8'}], true)|escape:'html':'UTF-8'}</a>  <i class="icon icon-external-link"></i></li>
		</ul>
	</p>
</div>