// Init countdown plugin
for (var i = 0, len = arrayCountDownInitFunctions.length; i < len; i++) {
	arrayCountDownInitFunctions[i](pjQuery_1_10_2);
}

pjQuery_1_10_2(document).ready(function(){
	var $ = pjQuery_1_10_2;
	// Render time records on checkout page
	var processCartItem = function(){
		var txt = $(this).text();
		if (/\|\|reserved\:\:([0-9]*)\|\|/.test(txt)) {
			arr = txt.match(/\|\|reserved\:\:([0-9]*)\|\|/);
			if (arr[1]) {
				$(this).html('<div class="reserved-item"><span class="cntdown"></span><span class="cntdown-source" style="display: none;">' + arr[1] + '</span></div>');
			}
			// fix for Magento 1.9CE - rewrite css rule by this class.
			$(this).addClass('cr-item');
		}
	};

	$('dl.item-options dt').each(processCartItem);
	$('.mini-products-list div.item-options dl dt').each(processCartItem);
	
	// ---------  FOR COUNT DOWN -------------------	
	refreshCartReservationTimers();
	
	if ((reminderTime !== 'forever') && (reminderTime > 0)) {
		var timer = setTimeout(function run() {
			if (reminderTime == 0) {
				$('#cartreservation_popup').show(function () {
					resizeCorrection();
				});
				$.get(lockPath);
			} else {
				reminderTime = reminderTime - 1;
	    		timer = setTimeout(run, 1000);
	    	}
	  	}, 1000);
	}

	$('.cartreservation_popup_close').click(function() {
		resizeCloseStyle();
		$('#cartreservation_popup').hide();
	});

	var resizeCloseStyle = function () {
		$('body').css('overflow', 'visible');
		$('#cartreservation_popup').removeClass('overflow');
	}

	var resizeCorrection = function () {
		var wh = $(window).height();
		var ph = $('#cartreservation_popup .holder').height() + (wh * 0.05);

		if (ph > wh) {
			$('body').css('overflow', 'hidden');
			$('#cartreservation_popup').addClass('overflow');
		} else {
			resizeCloseStyle();	
		}
	}; 

	$(window).resize(resizeCorrection);
});

function refreshCartReservationTimers()
{
	var $ = pjQuery_1_10_2;
	function _getCRTime(text)
	{
		var time = new Date().getTime();
		return new Date(time + parseInt(text) * 1000);
	}

	$('.cntdown').each(function(){
		var text = $(this).next('.cntdown-source:first').text();

		if (text && parseInt(text) > 0) {
			$(this).plCountdown({
				until: _getCRTime(text), 
				format: 'dHMS', 
				layout: countDownFormat,
				labels: ['years', 'months', 'weeks', 'days', 'hours', 'minutes', 'seconds'],
				// The display texts for the counters if only one
				labels1: ['year', 'month', 'week', 'day', 'hour', 'minute', 'second'],
				expiryText: expiryText,
				onExpiry: function () {
					if (needReloadPage && (typeof onExpiryCartCallback !== 'undefined')) {
						onExpiryCartCallback();
					}
				}
			});
		} else if (text == 'forever') {
			$(this).text(foreverText);
		} else {
			$(this).text(expiryText);
		}
	});
}