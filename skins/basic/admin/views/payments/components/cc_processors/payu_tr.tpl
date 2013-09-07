<!-- 
    This file is part of the PayU TR Payment Processor for CS Cart.
    Copyright (c) 2013, Yasin Kuyu @yasinkuyu    
-->
<h2 class="subheader">PayU TR</h2>

<div class="form-field">
	<label for="secret_word">PayU Key coding :</label>
	<input type="text" name="payment_data[processor_params][secure_hash]" id="secure_hash" value="{$processor_params.secure_hash}" class="input-text" size="60" />
</div>

<div class="form-field">
	<label for="account_number">PayU Vendor code :</label>
	<input type="text" name="payment_data[processor_params][gid]" id="gid" value="{$processor_params.gid}" class="input-text" size="60" />
</div>

<div class="form-field">
	<label for="mode">{$lang.test_live_mode}:</label>
	<select name="payment_data[processor_params][mode]" id="mode">
		<option value="test" {if $processor_params.mode == "test"}selected="selected"{/if}>{$lang.test}</option>
		<option value="live" {if $processor_params.mode == "live"}selected="selected"{/if}>{$lang.live}</option>
	</select>
</div>


<br />
<br />
<br />
<hr />
Yasin Kuyu @yasinkuyu