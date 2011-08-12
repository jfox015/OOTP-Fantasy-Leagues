	<script type="text/javascript" src="http://www.google.com/recaptcha/api/js/recaptcha_ajax.js"></script>
	<script type="text/javascript">
	var ajaxWait = '<img src="<?php echo($fantasy_web_root); ?>images/icons/ajax-loader.gif" width="28" height="28" border="0" align="absmiddle" />&nbsp;Operation in progress. Please wait...';
	var responseError = '<img src="<?php echo($fantasy_web_root); ?>images/icons/icon_fail.png" width="24" height="24" border="0" align="absmiddle" />&nbsp;';
	var buttonHTML = '<input class="sendNow" type="button" value="Send Now" id="btnSubmit" />';
    
    function showRecaptcha(element) {
		Recaptcha.create('<?php print($recapthca_publickey); ?>', element, {
		theme: '<?php print($recaptcha_theme); ?>',
		callback: null});
    } // END function
	
    function testCaptcha(clg,resp,form,buttonDiv,waitDiv) {
		var btnDiv = (buttonDiv != null) ? buttonDiv : 'buttonDiv';
		var waitingDiv = (waitDiv != null) ? waitDiv : 'waitDiv';
    	$('div#'+btnDiv).css('display','none');
		$('div#'+waitingDiv).css('display','block');
		var checksum = Math.floor(Math.random()*125);
        var url = "<?php echo($fantasy_web_root); ?>user/captchaTest/chlg/"+clg+"/resp/"+resp+"/checksum/"+checksum;
		$.getJSON(url, function(data){
			if (data.code.indexOf("200") != -1) {
				$("#"+form).submit();
			} else {
				$('#captcha_resp').html('<br clear="all" /><span class="error">' +data.result+'</span>');
				Recaptcha.reload();
				$('#focus_response_field').focus();
				$('div#'+btnDiv).css('display','block');
				$('div#'+waitingDiv).css('display','none');
			} // END if
		}); // getJSON
    } // END if
    </script>