<!--
/***************************************************************************
*   This file is part of the PayU TR Payment Processor for CS Cart.		   *
*   Copyright (c) 2013, Yasin Kuyu @yasinkuyu    						   *
*                                                                          *                                                                         *
* 	PayU/TR Payment Processor for CS-Cart 3.x							   *
* 	https://secure.payu.com.tr/docs/alu/			                       *
****************************************************************************/
-->

{if $card_id}
	{assign var="id_suffix" value="_`$card_id`"}
{else}
	{assign var="id_suffix" value=""}
{/if}
{assign var="card_item" value=$card_data|default:$cart.payment_info}

{assign var='rates' value=','|explode:"0,0,0,6.03,6.71,6.91,8.11,8.83,9.54,10.28,11.01,11.61"}

<table cellpadding="0" cellspacing="0" border="0" class="credit-card">
<tr valign="top">
	<td>
		<div class="form-field">
			<label for="cc_type{$id_suffix}" class="cm-required cm-cc-type">{$lang.select_card}</label>
			<select id="cc_type{$id_suffix}" name="payment_info[card]" onchange="fn_check_cc_type(this.value, '{$id_suffix}');">
				{foreach from=$credit_cards item="c"}
					<option value="{$c.param}" {if $card_item.card == $c.param}selected="selected"{/if}>{$c.descr}</option>
				{/foreach}
			</select>
		</div>
				
		<div class="form-field">
			<label for="cc_installment" class="cm-required cc_installment">Taksit</label>
			<select id="cc_installment" name="payment_info[installment]">
				{section name=installment start=0 loop=12 step=1}
					
					{assign var="no" value=$smarty.section.installment.index}
					{assign var="total" value=$cart.total}
					{assign var="rate" value=$rates[$no]}
					
					{if $no == '0'}
						<option value="0">TEK ÇEKİM {$cart.total} TL</option>
					{else}
						<option value="{$no}"> {$no+1} Taksit 
						({math equation="(total + (total * ( rate / 100 )))" total=$total rate=$rate format="%.2f"} TL / {$no+1} = 
						{math equation="((total + (total * ( rate / 100 ))) / (no+1))" total=$total rate=$rate no=$no format="%.2f"}) TL</option>
					{/if}
				
				{/section}
			</select> 
		</div>
			
		<div class="form-field">
			<label for="cc_number{$id_suffix}" class="cm-required cm-custom (validate_cc)">{$lang.card_number}</label>
			<input id="cc_number{$id_suffix}" size="35" type="text" name="payment_info[card_number]" value="" class="input-text cm-autocomplete-off" />
		</div>

		<div class="form-field">
			<label for="cc_name{$id_suffix}" class="cm-required">{$lang.cardholder_name}</label>
			<input id="cc_name{$id_suffix}" size="35" type="text" name="payment_info[cardholder_name]" value="" class="input-text" />
		</div>

		<div class="form-field hidden" id="display_start_date{$id_suffix}">
			<label class="cm-required">{$lang.start_date}</label>
			<label for="cc_start_month{$id_suffix}" class="hidden cm-required cm-custom (check_cc_date)">{$lang.month}</label><label for="cc_start_year{$id_suffix}" class="hidden cm-required cm-custom (check_cc_date)">{$lang.year}</label>
			<input type="text" id="cc_start_month{$id_suffix}" name="payment_info[start_month]" value="" size="2" maxlength="2" class="input-text-short" />&nbsp;/&nbsp;<input type="text" id="cc_start_year{$id_suffix}" name="payment_info[start_year]" value="" size="2" maxlength="2" class="input-text-short" />&nbsp;({$lang.expiry_date_format})
		</div>

		<div class="form-field">
			<label class="cm-required">{$lang.expiry_date}</label>
			<label for="cc_exp_month{$id_suffix}" class="hidden cm-required cm-custom (check_cc_date)">{$lang.month}:</label><label for="cc_exp_year{$id_suffix}" class="hidden cm-required cm-custom (check_cc_date)">{$lang.year}</label>
			<input type="text" id="cc_exp_month{$id_suffix}" name="payment_info[expiry_month]" value="" size="2" maxlength="2" class="input-text-short" />&nbsp;/&nbsp;<input type="text" id="cc_exp_year{$id_suffix}" name="payment_info[expiry_year]" value="" size="2" maxlength="2" class="input-text-short" />&nbsp;({$lang.expiry_date_format})
		</div>

		<div class="form-field" id="display_cvv2{$id_suffix}">
			<label for="cc_cvv2{$id_suffix}" class="cm-required cm-integer cm-autocomplete-off">{$lang.cvv2}</label>
			<input id="cc_cvv2{$id_suffix}" type="text" name="payment_info[cvv2]" value="" size="4" maxlength="4" class="input-text-short" disabled="disabled" />

			{if $smarty.const.AREA == "C"}
			<div class="cvv2">{$lang.what_is_cvv2}
				<div class="cvv2-note">
					{include file="views/orders/components/payments/cvv2_info.tpl"}
				</div>
			</div>
			{/if}
		</div>

		<div class="form-field hidden" id="display_issue_number{$id_suffix}">
			<label for="cc_issue_number{$id_suffix}" class="cm-integer">{$lang.issue_number}</label>
			<input id="cc_issue_number{$id_suffix}" type="text" name="payment_info[issue_number]" value="" size="2" maxlength="2" class="input-text-short cm-autocomplete-off" disabled="disabled" />&nbsp;{$lang.if_printed_on_your_card}
		</div>
	</td>
	<td>
		<div id="cc_images{$id_suffix}">
		{foreach from=$credit_cards item="c" name="credit_card"}
			{if $c.icon}
				{if $smarty.foreach.credit_card.first}
					{assign var="img_class" value="cm-cc-item"}
				{else}
					{assign var="img_class" value="cm-cc-item hidden"}
				{/if}
				{include file="common_templates/image.tpl" images=$c.icon class=$img_class obj_id="`$c.param``$id_suffix`" object_type="credit_card" max_width="50" max_height="50" make_box=true proportional=true show_thumbnail="Y"}
			{/if}
		{/foreach}
		</div>
	</td>
</tr>
</table>

<script type="text/javascript" class="cm-ajax-force">
//<![CDATA[
	{if $smarty.capture.cc_script != 'Y'}
	lang.error_card_number_not_valid = '{$lang.error_card_number_not_valid|escape:javascript}';

	var cvv2_required = new Array();
	var start_date_required = new Array();
	var issue_number_required = new Array();
	{foreach from=$credit_cards item="c"}
		cvv2_required['{$c.param}'] = '{$c.param_2}';
		start_date_required['{$c.param}'] = '{$c.param_3}';
		issue_number_required['{$c.param}'] = '{$c.param_4}';
	{/foreach}

	{literal}
	function fn_check_cc_type(card, suffix)
	{
		if (cvv2_required[card] == 'Y') {
			$('#display_cvv2' + suffix).switchAvailability(false);
		} else {
			$('#display_cvv2' + suffix).switchAvailability(true);
		}

		if (start_date_required[card] == 'Y') {
			$('#display_start_date' + suffix).switchAvailability(false);
		} else {
			$('#display_start_date' + suffix).switchAvailability(true);
		}

		if (issue_number_required[card] == 'Y') {
			$('#display_issue_number' + suffix).switchAvailability(false);
		} else {
			$('#display_issue_number' + suffix).switchAvailability(true);
		}

		$('div#cc_images' + suffix).find('.cm-cc-item').hide();
		$('#det_img_' + card + suffix).show();
	}

	function fn_check_cc_date(id)
	{
		var elm = $('#' + id);

		if (!$.is.integer(elm.val())) {
			return lang.error_validator_integer;
		} else {
			if (elm.val().length == 1) {
				elm.val('0' + elm.val());
			}
		}

		return true;
	}
	{/literal}
	
	{capture name="cc_script"}Y{/capture}
	{/if}

	fn_check_cc_type($('#cc_type{$id_suffix}').val(), '{$id_suffix}');
//]]>
</script>
