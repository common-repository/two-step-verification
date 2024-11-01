$ = new jQuery.noConflict();
$(document).ready(function(){
	// SkinTableTabs
	$(".tabs_holder").skinableTabs({
		effect: "basic_display",
		skin: "skin1",
		position: "top"
	});

	// toggle sms gateway config settings
	$("#sms_gateway").change(function(){
		var gw = $(this).val();
		$(".sms_gateway_config").hide();
		$(".class_"+gw).show();
	})

	// submit form automatically if method changed
	$("#verification_method").change(function(){
		if($(this).val() != ''){
			$(".submit input.button").click()
		}
	})

	// validate password
	$(".cb_option_form").submit(function(event){
		var clickatell_pw = $("#clickatell_pw").val();
		var clickatell_pw2 = $("#clickatell_pw2").val();

		var smsapi_pw = $("#smsapi_pw").val();
		var smsapi_pw2 = $("#smsapi_pw2").val();

		if (clickatell_pw != clickatell_pw2) {
			alert('Passowrds don\'t match. Please re-check password');
			event.preventDefault()
		}

		if (smsapi_pw != smsapi_pw2) {
			alert('Passowrds don\'t match. Please re-check password');
			event.preventDefault()
		}
	})

	// above this line
})