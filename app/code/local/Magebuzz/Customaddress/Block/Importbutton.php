<?php
class Magebuzz_Customaddress_Block_Importbutton extends Mage_Adminhtml_Block_System_Config_Form_Field {
  protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
		$this->setElement($element);
		$url = $this->getUrl('sinchimport/index'); //
		$this->setElement($element);

		$html = $this->_appendJs();

		$html .= '<div id="sinchimport_status_template" name="sinchimport_status_template" style="display:none">';//none
		$html .= $this->_getStatusTemplateHtml();
		$html .= '</div>';

		$start_import_button = $this->getLayout()->createBlock('adminhtml/widget_button')
				->setType('button')
				->setClass('scalable')
				->setLabel('Force Import now')
				->setOnClick("start_sinch_import()") 
				->toHtml();
		$safe_mode_set = ini_get('safe_mode');
		if($safe_mode_set){
				$html .="<p class='sinch-error'><b>You can't start import (safe_mode is 'On'. set safe_mode = Off in php.ini )<b></p>";
		}else{
				$html .= $start_import_button;    
		}

		$dataConf = Mage::getConfig();
		$import=Mage::getModel('sinchimport/sinch');
		$last_import=$import->getDataOfLatestImport();
		$last_imp_status=$last_import['global_status_import'];
		if($last_imp_status=='Failed'){
				$html.='<div id="sinchimport_current_status_message" name="sinchimport_current_status_message" style="display:true"><br><br><hr/><p class="sinch-error">The import has failed. Please ensure that you are using the correct settings. Last step was "'.$last_import['detail_status_import'].'"<br> Error reporting : "'.$last_import['error_report_message'].'"</p></div>';
		}elseif($last_imp_status=='Successful'){
				$html.='<div id="sinchimport_current_status_message" name="sinchimport_current_status_message" style="display:true"><br><br><hr/><p class="sinch-success">'.$last_import['number_of_products'].' products imported succesfully!</p></div>';
		}elseif($last_imp_status=='Run'){
				$html.='<div id="sinchimport_current_status_message" name="sinchimport_current_status_message" style="display:true"><br><br><hr/><p>Import is running now</p></div>';
		}else{
				$html.='<div id="sinchimport_current_status_message" name="sinchimport_current_status_message" style="display:true"></div>';
		}

		return $html;        
  }

    protected function _getStatusTemplateHtml()
    {
        $run_pic=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN)."adminhtml/default/default/images/sinchimport_run.gif";
        $html="
           <ul> 
            <li>
               Start Import
               &nbsp
               <span id='sinchimport_start_import'> 
                <img src='".$run_pic."'
                 alt='Sinch Import run' /> 
               </span> 
            </li>   
           </ul>
        ";
        return $html;
    }

    protected function _appendJs() {
        $post_url=$this->getUrl('sinchimport/ajax');
        $post_url_upd=$this->getUrl('sinchimport/ajax/UpdateStatus');
        $html = "
        <script>
            function start_sinch_import(){
		    set_run_icon();
                    status_div=document.getElementById('sinchimport_status_template');   
                    curr_status_div=document.getElementById('sinchimport_current_status_message'); 
                    curr_status_div.style.display='none';
                    status_div.style.display='';
//                    status_div.innerHTML='';
                    sinch = new Sinch('$post_url','$post_url_upd');
                    sinch.startSinchImport();

                    //
            }
	    function set_run_icon(){
		run_pic='<img src=\"".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN)."adminhtml/default/default/images/sinchimport_run.gif\""."/>';	
		document.getElementById('sinchimport_start_import').innerHTML=run_pic;
                document.getElementById('sinchimport_upload_files').innerHTML=run_pic;
                document.getElementById('sinchimport_parse_categories').innerHTML=run_pic;
                document.getElementById('sinchimport_parse_category_features').innerHTML=run_pic;
                document.getElementById('sinchimport_parse_distributors').innerHTML=run_pic;
                document.getElementById('sinchimport_parse_ean_codes').innerHTML=run_pic;
                document.getElementById('sinchimport_parse_manufacturers').innerHTML=run_pic;
                document.getElementById('sinchimport_parse_related_products').innerHTML=run_pic;
                document.getElementById('sinchimport_parse_product_features').innerHTML=run_pic;
                document.getElementById('sinchimport_parse_products').innerHTML=run_pic;
                document.getElementById('sinchimport_parse_pictures_gallery').innerHTML=run_pic;
                document.getElementById('sinchimport_parse_restricted_values').innerHTML=run_pic;
                document.getElementById('sinchimport_parse_stock_and_prices').innerHTML=run_pic;
                document.getElementById('sinchimport_generate_category_filters').innerHTML=run_pic;
                document.getElementById('sinchimport_indexing_data').innerHTML=run_pic;
                document.getElementById('sinchimport_import_finished').innerHTML=run_pic;
		
		

	    }	
 var Sinch = Class.create();
 Sinch.prototype = {

initialize: function(postUrl, postUrlUpd) {
                this.postUrl = postUrl; //'https://techatcost.com/purchases/ajax/';
                this.postUrlUpd = postUrlUpd;
                this.failureUrl = document.URL;
                // unique user session ID
                this.SID = null;
                // object with event message data
                this.objectMsg = null;
                this.prevMsg = '';
                // interval object
                this.updateTimer = null;
                // default shipping code. Display on errors

                 elem = 'checkoutSteps';
                 clickableEntity = '.head';

                // overwrite Accordion class method
                var headers = $$('#' + elem + ' .section ' + clickableEntity);
                headers.each(function(header) {
                        Event.observe(header,'click',this.sectionClicked.bindAsEventListener(this));
                        }.bind(this));
            },
startSinchImport: function () {
                 _this = this;
                 new Ajax.Request(this.postUrl,
                         {
method:'post',
parameters: '',
requestTimeout: 10,
/*
onLoading:function(){
  alert('onLoading');
  },
  onLoaded:function(){
  alert('onLoaded');
  },
*/
onSuccess: function(transport) {
var response = transport.responseText || null;
_this.SID = response;
if (_this.SID) {
_this.updateTimer = setInterval(function(){_this.updateEvent();},20000);
$('session_id').value = _this.SID;
} else {
alert('Can not get your session ID. Please reload the page!');
}
},
onTimeout: function() { alert('Can not get your session ID. Timeout!'); },
    onFailure: function() { alert('Something went wrong...') }
    });

},

updateEvent: function () {
                 _this = this;
                 new Ajax.Request(this.postUrlUpd,
                         {
method: 'post',
parameters: {session_id: this.SID},
onSuccess: function(transport) {
_this.objectMsg = transport.responseText.evalJSON();
_this.prevMsg = _this.objectMsg.message;
if(_this.prevMsg!=''){
   _this.updateStatusHtml();
}

if (_this.objectMsg.error == 1) {
// Do something on error
_this.clearUpdateInterval();
}

if (_this.objectMsg.finished == 1) {
 _this.objectMsg.message='Import finished';
 _this.updateStatusHtml();
_this.clearUpdateInterval();

}

},
onFailure: this.ajaxFailure.bind(),
    });
},

updateStatusHtml: function(){
    message=this.objectMsg.message.toLowerCase();
    mess_id='sinchimport_'+message.replace(/\s+/g, '_');    
    if(!document.getElementById(mess_id)){
    //     alert(mess_id+' - not exist');
    }     
    else{
    //    alert (mess_id+' - exist');
        $(mess_id).innerHTML='<img src=\"".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN)."adminhtml/default/default/images/sinchimport_yes.gif"."\"/>'
    }     
    htm=$('sinchimport_status_template').innerHTML;
//    $('sinchimport_status_template').innerHTML=htm+'<br>'+this.objectMsg.message;
},

ajaxFailure: function(){
                     this.clearUpdateInterval();     
                     location.href = this.failureUrl;
},

clearUpdateInterval: function () {
                             clearInterval(this.updateTimer);
},


 }
        </script>
        ";
        return $html;
    }

}