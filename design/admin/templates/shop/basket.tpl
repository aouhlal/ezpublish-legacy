{* Feedback if some items were removed by the system. *}
{section show=$removed_items}
<div class="message-warning">
<h2>{'Items removed'|i18n( 'design/admin/shop/basket' )}</h2>
<p>{'The following items were removed from your basket because the products have changed:'|i18n( 'design/admin/shop/basket' )}</p>
<ul>
{section var=RemovedItems loop=$removed_items}
<li><a href={concat( '/content/view/full/', $RemovedItems.item.contentobject.main_node_id, '/' )|ezurl}>{$RemovedItems.item.contentobject.name}</a></li>
{/section}
</ul>
</div>
{/section}

<form method="post" action={'/shop/basket/'|ezurl}>

<div class="context-block">

{* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

<h1 class="context-title">{'Shopping basket'|i18n( 'design/admin/shop/basket' )}</h1>

{* DESIGN: Subline *}<div class="header-mainline"></div>

{* DESIGN: Header END *}</div></div></div></div></div></div>

{* DESIGN: Content START *}<div class="box-ml"><div class="box-mr"><div class="box-content">

{section show=$basket.items}
<table class="list" cellspacing="0">
<tr>
	<th class="tight">&nbsp;</th>
	<th>{'Product'|i18n( 'design/admin/shop/basket' )}</th>
	<th>{'Quantity'|i18n( 'design/admin/shop/basket' )}</th>
	<th>{'VAT'|i18n( 'design/admin/shop/basket' )}</th>
	<th>{'Price (ex. VAT)'|i18n( 'design/admin/shop/basket' )}</th>
	<th>{'Price (inc. VAT)'|i18n( 'design/admin/shop/basket' )}</th>
	<th>{'Discount'|i18n( 'design/admin/shop/basket' )}</th>
	<th>{'Total (ex. VAT)'|i18n( 'design/admin/shop/basket' )}</th>
	<th>{'Total (inc. VAT)'|i18n( 'design/admin/shop/basket' )}</th>
</tr>
{section var=Products loop=$basket.items sequence=array( bglight, bgdark )}
<tr class="{$Products.sequence}">
	<td><input type="checkbox" name="RemoveProductItemDeleteList[]" value="{$Products.item.id}" /></td>
	<td><input type="hidden" name="ProductItemIDList[]" value="{$Products.item.id}" /><a href={concat( '/content/view/full/', $Products.item.node_id, '/' )|ezurl}>{$Products.item.object_name}</a></td>
	<td><input type="text" name="ProductItemCountList[]" value="{$Products.item.item_count}" size="3" /></td>
	<td>{$Products.item.vat_value} %</td>
	<td>{$Products.item.price_ex_vat|l10n( currency )}</td>
	<td>{$Products.item.price_inc_vat|l10n( currency )}</td>
	<td>{$Products.item.discount_percent}%</td>
	<td>{$Products.item.total_price_ex_vat|l10n( currency )}</td>
	<td>{$Products.item.total_price_inc_vat|l10n( currency )}</td>
</tr>
{section show=$Products.item.item_object.option_list}
<tr>
  <td colspan='4'>
  <table class="shop-option_list">
  <tr>
<td colspan='3'>
{'Selected options'|i18n( 'design/admin/shop/basket' )}
</td>
</tr>
     {section var=Options loop=$Products.item.item_object.option_list}
      <tr>
        <td width="33%">{$Options.item.name}</td>
        <td width="33%">{$Options.item.value}</td>
        <td width="33%">{$Options.item.price|l10n( currency )}</td>
      </tr>
    {/section}
     </table>
   </td>
  <td class="{$Products.sequence}" colspan='5'>
  </td>
</tr>
{/section}
{/section}
<tr>
<td colspan="9"><hr /></td>
</tr>
<tr>
<td colspan="7">&nbsp;</td>
<td><b>{'Subtotal (ex. VAT)'|i18n( 'design/admin/shop/basket' )}</b></td>
<td><b>{'Subtotal (inc. VAT)'|i18n( 'design/admin/shop/basket' )}</b></td>
</tr>
<tr>
<td colspan="7">
</td>
<td>{$basket.total_ex_vat|l10n( currency )}</td>
<td>{$basket.total_inc_vat|l10n( currency )}</td>
</tr>
</table>

{section-else}
<div class="block">
    <p>{'There are no products in your shopping basket.'|i18n( 'design/admin/shop/basket' )}</p>
</div>
{/section}

{* DESIGN: Content END *}</div></div></div>

{* Buttons. *}
<div class="controlbar">
{* DESIGN: Control bar START *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">
    <div class="block">
    {section show=$basket.items}
    <div class="button-left">
        <input class="button" type="submit" name="RemoveProductItemButton" value="{'Remove selected'|i18n( 'design/admin/shop/basket' )}" title="{'Remove selected items from the basket.'|i18n( 'design/admin/shop/basket' )}" />
        <input class="button" type="submit" name="StoreChangesButton" value="{'Apply changes'|i18n( 'design/admin/shop/basket' )}" title="{'Store changes (quantity, option, etc.) and update the basket.'|i18n( 'design/admin/shop/basket' )}" />
    </div>
    <div class="button-right">
        <input class="button" type="submit" name="ContinueShoppingButton" value="{'Continue shopping'|i18n( 'design/admin/shop/basket' )}" title="{'Leave the basket and go back to where you came from.'|i18n( 'design/admin/shop/basket' )}" />
        <input class="button" type="submit" name="CheckoutButton" value="{'Checkout'|i18n( 'design/admin/shop/basket' )}" title="{'Proceed to checkout and purchase the items that are in the basket.'|i18n( 'design/admin/shop/basket' )}" />
    </div>
    {section-else}
    <div class="button-left">
        <input class="button-disabled" type="submit" name="RemoveProductItemButton" value="{'Remove selected'|i18n( 'design/admin/shop/basket' )}" disabled="disabled" title="{'You can not remove any items because there are no items in the basket.'|i18n( 'design/admin/shop/basket' )}" />
        <input class="button-disabled" type="submit" name="StoreChangesButton" value="{'Apply changes'|i18n( 'design/admin/shop/basket' )}" disabled="disabled" title="{'You can not store any changes because the basket is empty.'|i18n( 'design/admin/shop/basket' )}" />
    </div>
    <div class="button-right">
        <input class="button" type="submit" name="ContinueShoppingButton" value="{'Continue shopping'|i18n( 'design/admin/shop/basket' )}" title="{'Leave the basket and go back to where you came from.'|i18n( 'design/admin/shop/basket' )}" />
        <input class="button-disabled" type="submit" name="CheckoutButton" value="{'Checkout'|i18n( 'design/admin/shop/basket' )}" disabled="disabled" title="{'You can not check out because the basket is empty.'|i18n( 'design/admin/shop/basket' )}" />
    </div>
    {/section}
    <div class="break"></div>
    </div>
{* DESIGN: Control bar END *}</div></div></div></div></div></div>
</div>

</div>
</form>

