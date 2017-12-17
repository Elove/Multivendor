<div style="text-align: center; width: 400px; border-radius: 5px; margin-left: 400px; margin-top: 100px; border: 1px solid rgb(139, 139, 139); padding: 10px;">
	  <p>Click here to proceed....</p>
	  <form action="https://sandbox.paypal.com/cgi-bin/webscr" method="post" id="PayPalForm" 
		name="PayPalForm"  target="_top">
		<input type="hidden" name="cmd" value="_xclick">
		<input type="hidden" name="business" value="<?php echo $email;?>">
		<input type="hidden" name="amount" value="<?php echo $amount;?>" id="amount">
		<input type="hidden" name="item_name" value="Payment">
		<input type="hidden" name="item_number" value="1">
		<input type="hidden" name="custom" value="<?php echo $customs;?>#<?php echo $gid;?>" id="custom">
		<input type="hidden" name="currency_code" value="<?php echo $config_currency; ?>">
		<input type="hidden" name="cancel_return" value="<?php echo $cancelURL;?>">
		<input type="hidden" name="return" value="<?php echo $notify_url; ?>">
		<input type="hidden" name="notify_url" value="<?php echo $notify_url; ?>"> 
		<input type="button" class="button" value="Paypal" onclick="paypal()"/> 
		</form>
</div>
<script>
  function paypal(){
	document.PayPalForm.submit();
	}
   </script>