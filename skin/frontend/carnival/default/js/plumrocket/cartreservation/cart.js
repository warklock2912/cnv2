// Init countdown plugin
for (var i = 0, len = arrayCountDownInitFunctions.length; i < len; i++) {
	arrayCountDownInitFunctions[i](pjQuery_1_10_2);
}

function refreshCartReservationTimers(selectors)
{
	var $ = pjQuery_1_10_2;
	// Render time records on checkout page
	var processCartItem = function(){
		var txt = $(this).text();
		if (/\|\|reserved\:\:([0-9a-z]*)\|\|/.test(txt)) {
			arr = txt.match(/\|\|reserved\:\:([0-9a-z]*)\|\|/);
			if (arr[1]) {
				$(this).html('<div class="reserved-item"><span class="cntdown"></span><span class="cntdown-source" style="display: none;">' + arr[1] + '</span><span class="cntdown-ctime" style="display: none;">' + crCurrentTime + '</span></div>');
			}
			// fix for Magento 1.9CE - rewrite css rule by this class.
			$(this).addClass('cr-item');
		}
	};

	if (!selectors) selectors = [];
	selectors.push('dl.item-options dt');
	selectors.push('.mini-products-list div.item-options dl dt');

	for(var i = 0; i < selectors.length; i++) {
		$(selectors[i]).each(processCartItem);
	}

	// ---------  FOR COUNT DOWN -------------------	

	function _getCRTime(text)
	{
		var time = new Date().getTime();
		return new Date(time + parseInt(text) * 1000);
	}

	var rCTime = 0;
	var rCTimeLoading = false;
	var rCTimeSchedule = [];

	function _getRealCTime(f, e)
	{
		rCTimeSchedule.push({'f':f,'e':e});

		if (rCTimeLoading) return;
		rCTimeLoading = true;

		$.ajax({
			'url':crRealTimeUrl,
			'dataType':'json',
			'success': function(data) {
				if (data.rCTime) {
					rCTime = data.rCTime;

					for(var i=0;i<rCTimeSchedule.length;i++) {

						rCTimeSchedule[i].f(rCTimeSchedule[i].e);
						delete(rCTimeSchedule[i]);
					}
				}
			},
			'always': function(){
				rCTimeLoading = false;
			}
		});
	}

	function _initCntdownTimer(e)
	{
		var $e = $(e);
		var parent = $e.parent();
		var cTime = parent.find('.cntdown-ctime:first').text();
		if (cTime && !rCTime) {
			_getRealCTime(_initCntdownTimer, e);
			return;
		}

		var text = parent.find('.cntdown-source:first').text();
		var format = parent.find('.cntdown-format').html();
		if (!format) {
			format = $e.hasClass('cntdown-product') ? countDownProductFormat : countDownFormat;
		}

		if (text && parseInt(text) > 0) {
			if (cTime) {
				text = parseInt(text);
				text -= rCTime - cTime - 1;
			}
		}

		if (text && parseInt(text) > 0) {
			$e.plCountdown({
				until: _getCRTime(text),
				format: 'dHMS',
				layout: format,
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
		} else if (!$e.data('noempty')) {
			if (text == 'forever') {
				$e.text(foreverText);
			} else if (text != 'no') {
				$e.text(expiryText);
			}
		}
	}

	$('.cntdown, .cntdown-product').each(function(){
		_initCntdownTimer(this);
	});
}

function CartReservationCountDown(selectors) { // function for ajax cart
	refreshCartReservationTimers(selectors);
}

pjQuery_1_10_2(document).ready(function(){
	var $ = pjQuery_1_10_2;
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