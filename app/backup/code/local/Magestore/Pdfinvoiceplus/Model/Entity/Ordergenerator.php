<?php

class Magestore_Pdfinvoiceplus_Model_Entity_Ordergenerator extends Magestore_Pdfinvoiceplus_Model_Entity_Abstractextend {

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

    public function setTheSourceId($orderId)
    {
        $this->_sourceId = $orderId;
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
        if ($storeId = $this->getTheOrder()->getStore()->getId())
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

//        if ($dataTemplate)
//        {
//            return $dataTemplate;
//        }
        return false;
    }

    public function getFileName() {
        if ($fileName = $this->createTemplate()->getData('order_filename'))
        {
            $templateVars = $this->getVars();
            $headerTemplate = Mage::helper('pdfinvoiceplus')->setTheTemplateLayout($fileName);
            $processedTemplate = $headerTemplate->getProcessedTemplate($templateVars);

            $cleanString = Mage::helper('core/string')->cleanString($processedTemplate);
            $cleanString = str_replace(array(' ', '.', ':'), '-', $processedTemplate);
            return $cleanString;
        }
        return 'order - ';
    }
     public function getBody()
    {

        if ($body = $this->createTemplate()->getData('order_html'))
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
        $items = Mage::getModel('pdfinvoiceplus/entity_itemsorder')
                        ->setSource($this->getTheOrder())->setOrder($this->getTheOrder());
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
//            if($i%2==0){
//                $itemProcess = str_replace('<tr class="items-tr background-items">','<tr>',$itemProcess);
//            }
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
        $total_order = Mage::getModel('pdfinvoiceplus/entity_totalsRender_order');
        $total_order->setSource($this->getTheOrder())
            ->setHtml($templateText)->setTemplateId($this->createTemplate()->getId());
        $templateText = $total_order->renderHtml();
        
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
    /* Change by Jack 26/12 */
    public function isMassPDF(){
        $orderIds = Mage::app()->getRequest()->getPost('order_ids');
        if($orderIds)
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
    // End Change
    public function pdfPaperFormat() {
        $template = Mage::helper('pdfinvoiceplus/pdf')->getUsingTemplate();
        $format = $template->getFormat();
        $format = $format ? $format : 'A4';
        return $format;
    }

    public function loadPdf() {
        $template = Mage::helper('pdfinvoiceplus/pdf')->getUsingTemplate();
        $top = '5';
        $bottom = '0';
        $left = '0';
        $right = '0';
        $orientation = $this->getOrientation();
        $pdf = new Mpdf_Magestorepdf('', $this->pdfPaperFormat(), 8, '', $left, $right, $top, $bottom);
        $pdf->AddPage($orientation);
        //Change by Jack 29/12 - add page number
            $storeId = Mage::app()->getStore()->getStoreId();
            $isEnablePageNumbering = Mage::getStoreConfig('pdfinvoiceplus/general/page_numbering',$storeId);
            if($isEnablePageNumbering)
               if($isEnablePageNumbering)
                $pdf->SetHTMLFooter('<div style = "float:right;z-index:16000 !important; width:30px;">{PAGENO}/{nb}</div>');
        // End Change  
        return $pdf;
    }

    /* Packing Slip */
    // MS-1006
    public function getPackingSlipPdf($html = '') {
        $isMassPDF = $this->isMassPDF();
        $mailPdf = new Varien_Object;
        if($isMassPDF){
            $templateBody = $this->getTheProcessedTemplate();
            $mailPdf->setData('htmltemplate', $templateBody);
            $mailPdf->setData('filename', 'PackingSlip');
        }
        else{
            $pdf = $this->loadPackingSlipPdf();
            $templateBody = $html;
            $pdf->WriteHTML($this->getCss(null), 1);
            $pdf->WriteHTML($templateBody);

            $mailPdf->setData('htmltemplate', $templateBody);
            $output = $pdf->Output('PackingSlip', 'S');
            $mailPdf->setData('pdfbody', $output);
            $mailPdf->setData('filename', 'PackingSlip');
        }
        return $mailPdf;
    }

    public function loadPackingSlipPdf() {
        // $template = Mage::helper('pdfinvoiceplus/pdf')->getUsingTemplate();
        $top = '5';
        $bottom = '0';
        $left = '0';
        $right = '0';
        $orientation = $this->getOrientation();
        $pdf = new Mpdf_Magestorepdf('', 'A7', 8, '', $left, $right, $top, $bottom);
        $pdf->AddPage($orientation);
        //Change by Jack 29/12 - add page number
            $storeId = Mage::app()->getStore()->getStoreId();
            // $isEnablePageNumbering = Mage::getStoreConfig('pdfinvoiceplus/general/page_numbering',$storeId);
            // if($isEnablePageNumbering)
            //    if($isEnablePageNumbering)
            //     $pdf->SetHTMLFooter('<div style = "float:right;z-index:16000 !important; width:30px;">{PAGENO}/{nb}</div>');
        // End Change  
        return $pdf;
    }

    /* End Packing Slip */
}
