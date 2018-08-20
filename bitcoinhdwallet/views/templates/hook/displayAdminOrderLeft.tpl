{*
* 2018 LX
*
* NOTICE OF LICENSE
*
*  @author    LX
*  @copyright 2018 LX
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<script type="text/template" id="bitcoin-address">
<a class="btn btn-success row-margin-top" target="_blank" href="https://blockchain.info/address/{$address|escape:'html':'UTF-8'}">
    {l s='Bitcoin Address' mod='bitcoinhdwallet'} :
    <strong>{$address|escape:'html':'UTF-8'}</strong>
</a>
</script>

<script type="text/javascript">
    $(function() {
        $('#tabOrder').prev().append($('#bitcoin-address').html())
    });
</script>