jQuery(document).ready(function () {
  //When page loads...
  jQuery(".tab-content").hide(); //Hide all content
  jQuery("ul.tab-title li:first").addClass("active").show(); //Activate first tab
  jQuery(".tab-content:first").show(); //Show first tab content
   //Tab On Click Event
  jQuery("ul.toggle-tabs li").click(function() {
    jQuery(".tab-content").hide(); //Hide all content
    jQuery(".tab-container.current").children(":first").show();
  });

  ///end

  //On Click Event
  jQuery("ul.tab-title li").click(function() {

    jQuery("ul.tab-title li").removeClass("active"); //Remove any "active" class
    jQuery(this).addClass("active"); //Add "active" class to selected tab
    jQuery(".tab-content").hide(); //Hide all tab content

    var activeTab = jQuery(this).find("a").attr("href"); //Find the href attribute value to identify the active tab + content
    jQuery(activeTab).fadeIn(); //Fade in the active ID content
    return false;
  });
  
  // toggle
  jQuery('.title-toggle').click(function(){
    jQuery('.content-toggle').slideUp(0);
    if (!jQuery(this).hasClass('active')){
      jQuery(this).next().slideToggle(0);
      jQuery('.title-toggle').removeClass('active');
      jQuery(this).addClass('active');
    }
    else if (jQuery(this).hasClass('active')) {
      jQuery(this).removeClass('active');
    }
	});


  // custom menu

  jQuery('.menu-top li.li-parent').append( "<span class='arrow'></span>" );
  jQuery('.menu-top li.li-parent span.arrow').click(function(){
    if (!jQuery(this).hasClass('active')){
      jQuery(this).prev().slideToggle(300);
      jQuery(this).addClass('active');
    }
    else if (jQuery(this).hasClass('active')) {
      jQuery(this).removeClass('active');
      jQuery(this).prev().slideToggle(300);
    }
  });

  // custom menu

    if(jQuery('#cybersource_cc_type_cvv_div').length > 0){
        jQuery('#cybersource_cc_type_cvv_div .cvv-what-is-this').click(function() {
            jQuery(this).toggleClass('active');
        });
    }

  
});