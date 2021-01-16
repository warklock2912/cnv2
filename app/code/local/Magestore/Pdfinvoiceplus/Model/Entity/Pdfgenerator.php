<?php

class Magestore_Pdfinvoiceplus_Model_Entity_Pdfgenerator extends Magestore_Pdfinvoiceplus_Model_Entity_Abstractextend {

    const THE_START = '##productlist_start##';
    const THE_END = '##productlist_end##';
    public $pdfCollection;

    /**
     * The pdf proceesed template
     * @var string
     */
    protected $_pdfProcessedTemplate;

    /**
     * Load the pdf system
     * @return Mpdf Object - lib
     */
    protected function _construct()
    {
        $this->setPdfCollection();
        parent::_construct();
    }

    public function setTheSourceId($invoiceId)
    {
        $this->_sourceId = $invoiceId;
        return $this->_sourceId;
    }

    public function getTheSourceId()
    {
        return $this->_sourceId;
    }
     public function setPdfCollection()
    {
        $this->pdfCollection = Mage::getModel('pdfinvoiceplus/template')->getCollection();
        return $this;
    }

    public function getPdfCollection()
    {
        return $this->pdfCollection;
    }

    public function getTheStoreId()
    {
        if ($storeId = $this->getTheInvoice()->getOrder()->getStore()->getId())
        {
            return array(0, $storeId);
        }
        return array(0);
    }

    public function getFilteredCollection()
    {
        try {
            $pdfGeneratorTemplate = Mage::helper('pdfinvoiceplus/pdf')->getUsingTemplate();
//            if ($this->templateId)
//            {
//                $pdfGeneratorTemplate = $this->getPdfCollection()
//                        ->addFieldToSelect('*')
//                        ->addFieldToFilter('template_id',$this->templateId);
//            }
//            else
//            {
//                $pdfGeneratorTemplate = $this->getPdfCollection()
//                        ->addFieldToSelect('*')
//                        ->addFieldToFilter('status', 1);
////                        ->addFieldToFilter('template_store_id', $this->getTheStoreId());
//            }
        } catch (Exception $e) {
            Mage::log($e->getMessage());
            return null;
        }
        return $pdfGeneratorTemplate;
    }

    public function createTemplate()
    {
        $pdfCollection = $this->getFilteredCollection();
        if ($pdfCollection)
        {
            return $pdfCollection;
        }
//        foreach ($pdfCollection as $pdf)
//        {
//            $dataTemplate = $pdf;
//        }
//
//        if ($dataTemplate)
//        {
//            return $dataTemplate;
//        }
        return false;
    }

    public function getFileName() {
//        $template = Mage::helper('pdfinvoiceplus/pdf')->getUsingTemplate();
//        if(Mage::app()->getRequest()->getParam('invoice_id')){
//            $filename = $template->getInvoiceFilename();
//            //zend_Debug::dump($filename);die();
//        }elseif(Mage::app()->getRequest()->getParam('creditmemo_id')){
//            $filename = $template->getCreditmemoFilename();
//        }else{
//            $filename = $template->getOrderFilename();
//            die('111');
//        }
//        zend_debug::dump($filename);
//        die('dtt');
//        $vars = Mage::helper('pdfinvoiceplus')->processAllVars(Mage::helper('pdfinvoiceplus/pdf')->collectVars());
////        zend_Debug::dump($vars);die('213');
//        $filter->setVariables($vars);
//        $filename = $filter->filter($filename);
       
//        return '$filename';
        if ($fileName = $this->createTemplate()->getData('invoice_filename'))
        {
            $templateVars = $this->getVars();
            $headerTemplate = Mage::helper('pdfinvoiceplus')->setTheTemplateLayout($fileName);
            $processedTemplate = $headerTemplate->getProcessedTemplate($templateVars);

            $cleanString = Mage::helper('core/string')->cleanString($processedTemplate);
            $cleanString = str_replace(array(' ', '.', ':'), '-', $processedTemplate);
            return $cleanString;
        }
        return 'invoice - ';
    }
    
    public function getBarcodeValueInvoice() {
        if ($barcode = $this->createTemplate()->getData('barcode_order'))
        {
            $templateVars = $this->getVars();
            $headerTemplate = Mage::helper('pdfinvoiceplus')->setTheTemplateLayout($barcode);
            $processedTemplate = $headerTemplate->getProcessedTemplate($templateVars);

            $cleanString = Mage::helper('core/string')->cleanString($processedTemplate);
            $cleanString = str_replace(array(' ', '.', ':'), '-', $processedTemplate);
            return $cleanString;
        }
        return 'barcode - ';
    }
     public function getBody()
    {

        if ($body = $this->createTemplate()->getData('invoice_html'))
        {
            return $body;
            
        }
        return false;
    }

    /**
     * Get the template body from used in the backend with the varables and add the item variables.
     * @return string
     */
    public function getTheTemplateBodyWithItems()
    {
        $templateToProcessForItems = $this->getBody();
        /* Change by Zeus 08/12 */
        $finalItems = NULL;
        /* End change */
        $items = Mage::getModel('pdfinvoiceplus/entity_items')
                        ->setSource($this->getTheInvoice())->setOrder($this->getTheInvoice()->getOrder());
        $itemsData = $items->processAllVars();
        
        $result = Mage::helper('pdfinvoiceplus/items')
                ->getTheItemsFromBetwin($templateToProcessForItems,self::THE_START, self::THE_END);
        $i = 1;
        foreach ($itemsData as $templateVars)
        {
            $itemPosition = array('items_position' => $i++);
            $templateVars = array_merge($itemPosition, $templateVars);

            $pdfProcessTemplate = Mage::getModel('core/email_template');
            $itemProcess = $pdfProcessTemplate->setTemplateText($result)->getProcessedTemplate($templateVars);
            if($i%2==0){
                $itemProcess = str_replace('<tr>','<tr class="items-tr">',$itemProcess);
            }
            $finalItems .= $itemProcess . '<br>';
        }
        $templateWithItemsProcessed = str_replace($result, $finalItems, $templateToProcessForItems);


        $tempmplateForHtmlProcess = '<html>' . $templateWithItemsProcessed . '</html>';

        //$htmlTemplateWithItems = Mage::helper('pdfinvoiceplus/items')->processHtml($tempmplateForHtmlProcess);
        return $tempmplateForHtmlProcess;
    }

    /**
     * Load the default information for the template processing
     * @return object Mail template object
     */
    public function mainVariableProcess()
    {
        $templateText = $this->getTheTemplateBodyWithItems();
        //auto insert totals
        $total_invoice = Mage::getModel('pdfinvoiceplus/entity_totalsRender_invoice');
        $total_invoice->setSource($this->getTheInvoice())
            ->setHtml($templateText)->setTemplateId($this->createTemplate()->getId());
        $templateText = $total_invoice->renderHtml();
        
        //Zend_Debug::dump($templateText);die;
        
        $theVariableProcessor = Mage::helper('pdfinvoiceplus')->setTheTemplateLayout($templateText);
        return $theVariableProcessor;
    }

    /**
     * The vars for the entity
     * @return type
     */
    public function getTheProcessedTemplate()
    {
        $templateVars = $this->getVars();
        $processedTemplate = $this->mainVariableProcess()->getProcessedTemplate($templateVars);
        return $processedTemplate;
    }

    /**
     * get system template in use
     * @return system template
     */
    public function getSystemTemplate() {
        $activeTemplate = Mage::getModel('pdfinvoiceplus/template')->getCollection()
            ->addFieldToFilter('is_active', 1)
            ->getFirstItem();
        $systemTemplate = Mage::getModel('pdfinvoiceplus/systemtemplate')
            ->load($activeTemplate->getSystemTemplateId());
        return $systemTemplate;
    }

    public function getOrientation() {
        $template = Mage::helper('pdfinvoiceplus/pdf')->getUsingTemplate();
        $orientation = $template->getOrientation();
        if ($orientation == 1)
            return 'L';
        return 'P';
    }
    public function isMassPDF(){
        $invoiceIds = Mage::app()->getRequest()->getPost('invoice_ids');
        if($invoiceIds)
            return true;
        return false;
    }
    public function getPdf($html = '') {
        $isMassPDF = $this->isMassPDF();
        $mailPdf = new Varien_Object;
        if($isMassPDF){
            $templateBody = $this->getTheProcessedTemplate();
            $mailPdf->setData('htmltemplate', $templateBody);
            $mailPdf->setData('filename', $this->getFileName());
        }
        else{
            $pdf = $this->loadPdf();
            $templateBody = $this->getTheProcessedTemplate();
            $pdf->WriteHTML($this->getCss(null), 1);
            $pdf->WriteHTML($templateBody);

            $mailPdf->setData('htmltemplate', $templateBody);
            $output = $pdf->Output($this->getFileName(), 'S');
            $mailPdf->setData('pdfbody', $output);
            $mailPdf->setData('filename', $this->getFileName());
        }
        return $mailPdf;
    }

    public function pdfPaperFormat() {
        $template = Mage::helper('pdfinvoiceplus/pdf')->getUsingTemplate();
        $format = $template->getFormat();
        $format = $format ? $format : 'A4';
        return $format;
    }

    public function loadPdf() {
		$invoiceId =Mage::app()->getRequest()->getParam('invoice_id');
		if(!$invoiceId){
			$postData = Mage::app()->getRequest()->getPost('invoice_ids');
			$invoiceId = $postData[0]; 
		}
		if(isset($postData) && count($postData) == 1 || Mage::app()->getRequest()->getParam('invoice_id')){
			$invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);
			$incrementId = $invoice->getIncrementId();
			$status = $invoice->getState();
			if ($status == 1) {
				$status = 'Pending';
			} else if ($status == 2) {
				$status = 'Paid';
			} else {
				$status = 'Closed';
			}
			$template = Mage::helper('pdfinvoiceplus/pdf')->getUsingTemplate();
			$createdAt = Mage::helper('core')->formatDate($invoice->getCreatedAt(), 'short', true);
			$top = '90';
			$bottom = '50';
			$left = '0';
			$right = '0';
			$orientation = $this->getOrientation();
			$pdf = new Mpdf_Magestorepdf('', $this->pdfPaperFormat(), 8, '', $left, $right, $top, $bottom);
			$pdf->AddPage($orientation);
			$pdf->SetY(5);
			$pdf->SetHTMLHeader('<div class="template04"><div class="myheader-iv" style="padding-top: 20px; padding-bottom: 20px;">
			<div class="top-header-iv">
			<div class="title-page-iv1">
			<div class="order1 title-color contenteditable color-theme" contextmenu-type="main" title="Click to edit, right-click to insert variable" contenteditable="true">Receipt / Tax Invoice</div>
			<div class="barcode"></div>
			</div>
			<div title="Click to upload..." class="box-logo ajaxupload" style="margin-top: 0px; text-align: right; float: right;" info-img="company_logo"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADYAAAAiCAYAAAAUAipQAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA9VJREFUeNrcWVtIFkEU3t9fEytTk6LSQLIisCAprF7CfKgIIsEKiYLCIIJuFBFURJEhVIKBUUERCVEQJkRB2kUTeqiXFLLQEI3sYlhUKtnF7Ax8I4dlZnd2/3lQD3zu/DuzO/udc+bMOWPEcZyIo5YhZxRLZJQTeEfoVXXE41pGqLOorBTLBJIJUcOx8wiHpMsVOmNH8gWn+BH4YemE2YRUwiDhC6GN0B/kJTpilwmLNX0/MVkLoZbQQPinGLeQUIF2MeGTx3dMJ+wkbIAruUUQfEa4Tqgi9JmQU7liA+6b4AUhV+cSQJZm7nGEUsKA651CeZ2ELsIfV99nwtZYXfEu4QraKQgMifjQ9XAZYZlGWLg1gLdMJdxjniEsWkmoxntktE4gLILVdxCmEK4SCgjbCb/DWKzCx43PMk0+DmCxyXBl2S8IjTdQRibhAXuuWhEt5byhiUlpZBOlGBK7w/p2BQwuUaw1+fwRFbE4zcMJASa6xdoZBuOLCGvRvgRrBRERSLYRnuP3McIs1h83/EchEwJM1Bvww46zNXUw5JYg1lUJLCYC0GHWN8mLWBDJYwTbfMYuIcxHuzKEUri8RHCT20mS4zZbjKRK0D5P+OszfjVr11hQ6m3mYctjJSZCfjYW7SO4Qi1zMS/JZW74ygKxJ4p3e2YeUvYCOmkn7CFc02QfPFsRMhPXt5bSr06sswi2AmuuKCxXTjjtk9H/wjUR16+WiAlSP9CeGITYOWiDQ+wj0wgrYSlB6ADhKSHNIJo5SHBtlUjJaPfHajHhct3IAETOtgZ7Sw7hjM+z71kGYUMyGYcPNl3RQeCoQnuLT2rUzNbabEu1l5Qm28QkOZmtLzAYJ2SdhXllKjiA1M6Y2KDhBN9Y28tiYh2+YRE3MQZSc5lyqt2bvR8x08wgx7UFeK3PUuaOJ0KSiiLPjEL5p3Qhs8B1rwn3TTZdEeE6ML5Vk92nuiJZPevbHCISXmTPlyncc7hsyQ9JLBcVtJykmPWt0hCTRwFSGcKKJw0rinRUE/K9dYrnCk0q6KWEfYrMfwZhmSuNEbXbTfY7yeO9H6HM+zjjOAqllCOH7HaNn0PYRNgNcg6q7404OtDu3jqLmaADNZajcYkhjw1ZZAsXYDX+zi58QzMOjnifWPf7PeKDp8XqkYd55X7tiHIPNVrrYUmqLuvvw+lUBaxRhKwmQ1G0ivV7A1VEj8lCFFpYgeOAkSDZCOVpiHiCxGt3ZuGzt9WMxAPTdp8tw0jinDEq8ayyzQpwmNJr+Tu+O/b+65Mn11iLZkAyKwxHnUTGgNcpOfwXYABIdhL+wXdliAAAAABJRU5ErkJggg==" width="160"></div>
			</div>
			<div class="bottom-header-iv" contextmenu-type="main">
			<div class="id-invoice-iv">
			<div contextmenu-type="main" style="font-family: Ubuntu Medium; font-size: 26px;" class="content contenteditable" contenteditable="true"><span class="color-text contenteditable" title="Click to edit, right-click to insert variable" style="font-family: Ubuntu Medium; font-size: 26px;" contenteditable="true">#'.$incrementId.'</span><br> <span class="color-text contenteditable" title="Click to edit, right-click to insert variable" style="color: #010101; font-size: 18px;" contenteditable="true">'.$createdAt.'</span></div>
			<div class="status color-text contenteditable" contextmenu-type="main" title="Click to edit, right-click to insert variable" style="margin-top: 5px;" contenteditable="true"><span style="font-weight: bold;">Status: </span> '.$status.'</div>
			</div>
			<div contextmenu-type="main" title="Click to edit..." class="box-infomations autogrow info-iv contenteditable" contenteditable="true"><span class="title-color" title="Click to edit, right-click to insert variable" style="display: block; font-weight: bold; font-size: 18px; color: #000000; width: 100%; float: left; font-family: Ubuntu; text-transform: uppercase;" info-text="company_name">Buy It Online Co., Ltd.</span><br> <span class="color-text" title="Click to edit, right-click to insert variable" style="display: block; font-family: Ubuntu Light; font-size: 14px; width: 100%; float: left;"><strong>Address: </strong><info info-text="company_address">8 Sukhumvit 29, Sukhumvit Road, <br>Klongtoey Nua, Wattana, Bangkok 10110</info></span><br> <span class="color-text" title="Click to edit, right-click to insert variable" style="display: block; width: 100%; float: left;"><strong> Email: </strong> <info info-text="company_email">contact@bio.co.th</info></span><br> <span class="color-text" title="Click to edit, right-click to insert variable" style="display: block; width: 100%; float: left;"><strong>Customer Care: </strong><info info-text="company_telephone">0-2021-5557</info></span><br> <span class="color-text" title="Click to edit, right-click to insert variable" style="display: block; width: 100%; float: left;"><strong>Fax: </strong><info info-text="company_fax">0-2224-9552</info></span><br> <span class="color-text" title="Click to edit, right-click to insert variable" style="display: block; width: 100%; float: left;"><strong>TAX ID: </strong><info info-text="vat_number">0-1055-58019-10-6</info></span></div>
			</div>
			</div></div>');
			$pdf->SetHTMLFooter('<div id="footer" class="color-text style-color theme-color" name="myfooter" style="height: 50px;">
			<div class="upper-footer"><strong>THANK YOU FOR SHOPPING WITH US!</strong></div>
			<div contextmenu-type="main" title="Click to edit, right-click to insert variable" class="color-text contenteditable" style="padding-left: 3.5%; padding-top: 10px; width: 100%; text-align: center;" info-text="footer" contenteditable="true">For more information please contact our Customer Care:<br> Tel. 0-2021-5557 Monday – Friday from 9AM to 6PM, Email: contact@bio.co.th, Website: www.bio.co.th</div>
			</div>');
		}
		else{
			$top = '5';
			$bottom = '5';
			$left = '0';
			$right = '0';
			$orientation = $this->getOrientation();
			$pdf = new Mpdf_Magestorepdf('', $this->pdfPaperFormat(), 8, '', $left, $right, $top, $bottom);
			$pdf->AddPage($orientation);
			//Change by Jack 29/12 - add page number
				$storeId = Mage::app()->getStore()->getStoreId();
				$isEnablePageNumbering = Mage::getStoreConfig('pdfinvoiceplus/general/page_numbering',$storeId);
				if($isEnablePageNumbering)
					$pdf->SetHTMLFooter('<div style = "float:right;z-index:16000 !important; width:30px;">{PAGENO}/{nb}</div>');
			// End Change  
		}
        return $pdf;
    }
}
