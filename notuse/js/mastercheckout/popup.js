//SETTING UP OUR POPUP
//0 means disabled; 1 means enabled;
var popupStatus = 0;

//loading popup with jQuery magic!
function loadPopup(popup_id){
	//loads popup only if it is disabled
	if(popupStatus==0){
		jQuery("#backgroundPopup").css({
			"opacity": "0.7"
		});
		jQuery("#backgroundPopup").fadeIn("slow");
		jQuery("#" + popup_id).fadeIn("slow");
		popupStatus = 1;
	}
}

//disabling popup with jQuery magic!
function disablePopup(popup_id){
	//disables popup only if it is enabled
	if(popupStatus==1){
		jQuery("#backgroundPopup").fadeOut("slow");
		jQuery("#" + popup_id).fadeOut("slow");
		popupStatus = 0;
	}
};

//centering popup
function centerPopup(popup_id){
	//request data for centering
	var windowWidth = document.documentElement.clientWidth;
	var windowHeight = document.documentElement.clientHeight;
	var popupHeight = jQuery("#" + popup_id).height();
	var popupWidth = jQuery("#" + popup_id).width();
	//centering
	jQuery("#" + popup_id).css({
		"position": "absolute",
		"top": windowHeight/2-popupHeight/2 + 100,
		"left": windowWidth/2-popupWidth/2
	});
	//only need force for IE6

	jQuery("#backgroundPopup").css({
		"height": windowHeight
	});

};