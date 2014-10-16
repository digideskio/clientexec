<?php
/**
 * @category Models
 * @package  Billing
 * @author   Alberto Vasquez <alberto@clientexec.com>
 * @license  ClientExec License
 * @link     http://www.clientexec.com
 */

require_once 'library/mpdf/mpdf.php';
require_once 'modules/billing/models/InvoiceEntry.php';
require_once 'modules/billing/models/Invoice.php';
require_once 'modules/billing/models/Currency.php';
require_once 'modules/clients/models/UserPackage.php';

class PDFInvoice extends NE_Model
{

    public $user;
    public $invoice;
    public $pdf;
    public $html;
    public $view;
    protected $template = "";
    private $invoice_customer = null;

    function __construct($tuser, $invoiceID)
    {
        parent::__construct();

        $this->view = new CE_View();
        $settings = new CE_Settings();

        $this->invoice = new Invoice($invoiceID);
        $this->invoice_customer = new User($this->invoice->getUserID());
        //get path and add to path

        $this->template = $this->invoice_customer->getInvoiceTemplate();
        if ($this->template == "") {
            $this->template = $settings->get('Invoice Template');
        }

        if (is_dir(APPLICATION_PATH.'/../plugins/invoices/'.$this->template)) {
            $tplDir = APPLICATION_PATH.'/../plugins/invoices/'.$this->template;
        } else {
            $tplDir = APPLICATION_PATH.'/../plugins/invoices/default';
        }


        // keep old paths, so that templates in controllers still work
        $paths = $this->view->getScriptPaths();
        $paths[] = $tplDir;
        $this->view->setScriptPath($paths);

        $this->user = $tuser;
        $this->generateHtml();
        $this->generatePdf();

    }

    function generatePDF()
    {
        // create new PDF document
        // $this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        ini_set("memory_limit","128M");
        $this->pdf =new mPDF('c');
        $this->pdf->useSubstitutions=false;
        $this->pdf->simpleTables = true;

        if (file_exists('plugins/invoices/'.$this->template.'/style.css')) {
            $stylesheet = file_get_contents('plugins/invoices/'.$this->template.'/style.css');
            $this->pdf->WriteHTML($stylesheet,1);
            $this->pdf->WriteHTML($this->html,2);
        } else {
            $this->pdf->WriteHTML($this->html);
        }


        // set document information
        $this->pdf->SetCreator(PDF_CREATOR);
        $this->pdf->SetAuthor('ClientExec');
        $this->pdf->SetTitle('Invoice #' . $this->invoice->getId());
        $this->pdf->SetSubject('Invoice #' . $this->invoice->getId());
        $this->pdf->SetKeywords('ClientExec, Invoice, Billing');
        /*
        // set header and footer fonts
        $this->pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $this->pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $this->pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        //set margins
        $this->pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $this->pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        //set auto page breaks
        $this->pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        //set image scale factor
        $this->pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        //set some language-dependent strings
        //$this->pdf->setLanguageArray($l);

        // ---------------------------------------------------------

        // set font
        //$this->pdf->SetFont('dejavusans', '', 10);
        //$this->pdf->SetFont('arialunicid0', '', 10);
        $this->pdf->SetFont('helvetica', '', 10);

        // add a page
        $this->pdf->AddPage();

        // output the HTML content
        $this->pdf->writeHTML($this->html, true, 0, true, 0);

        // reset pointer to the last page
        $this->pdf->lastPage();
        */

        // ---------------------------------------------------------
    }

    function generateHtml()
    {

        $currency = new Currency($this->user);

        session_cache_limiter('none');

        // restore database connection encoding to latin1 if necessary
        /*if (isset($this->settings->configuration['application']['dbEncoding']) && $this->settings->configuration['application']['dbEncoding'] == 'utf8') {
            $this->db->query("SET NAMES 'latin1'");
        }*/

        $UserCurrency = $this->invoice_customer->getCurrency();
        $paidLabel = "";
        $paidDate = "";
        $paymentLabel = "";
        $paymentMethod = "";
        $pmtRef = "";
        $vatNumber = "";
        if ($this->invoice->isPaid() && $this->invoice->getDatePaid("timestamp") != ''){
            $paidDate = date($this->settings->get('Date Format'),$this->invoice->getDatePaid("timestamp"));
        }
        $newLine = "\n";
        if ($this->invoice->isPaid() && $this->invoice->m_PluginUsed != 'none'){
            $newLine = '';
            $paymentLabel = $this->invoice_customer->lang('Payment Method')." : ";
            $paymentMethod = $this->invoice->m_PluginUsed;
        }
        if ($this->invoice->isPaid() && $this->invoice->m_CheckNum != ''){
            $pmtRef = $this->invoice->m_CheckNum;
        }

        //Add spaces based on logo size
        //if user has logo add spacer
        $invoicelogo = "";
        if (file_exists('images/invoicelogo.jpg')) {
            $invoicelogo = 'images/invoicelogo.jpg';
        } else if (file_exists('images/invoicelogo.png')) {
            $invoicelogo = 'images/invoicelogo.png';
        }

        //If invoice is paid, void, refunded or credited then add stamp image
        $this->view->status = "";
        if ($this->invoice->isPaid()) {
            $this->view->status = "paid";
        } elseif ($this->invoice->isVoid()) {
            $this->view->status = "void";
        } elseif ($this->invoice->isRefunded()) {
            $this->view->status = "refund";
        } elseif ($this->invoice->isCredited()) {
            $this->view->status = "credited";
        } elseif ($this->invoice->isDraft()) {
            $this->view->status = "draft";
        }

        $customerAddress = array();
        if ($this->invoice_customer->getAddress() != "") $customerAddress[] = $this->invoice_customer->getAddress();
        if ($this->invoice_customer->getCity() != "" && $this->invoice_customer->getState() != "") $customerAddress[] = $this->invoice_customer->getCity() .", " .$this->invoice_customer->getState();
        else if ($this->invoice_customer->getCity() != "") $customerAddress[] = $this->invoice_customer->getCity();
        else if ($this->invoice_customer->getState() != "") $customerAddress[] = $this->invoice_customer->getState();
		if ($this->invoice_customer->getZipCode() != "") $customerAddress[] = $this->invoice_customer->getZipCode();
        if ($this->invoice_customer->getCountry(true) != "") $customerAddress[] = $this->invoice_customer->getCountry(true);
        $cAddr = implode("<br />", $customerAddress);

        $customerOrg = "";
        if(trim($this->invoice_customer->getOrganization()) != ""){
            $customerOrg = $this->invoice_customer->getOrganization();
        }
        if ($this->invoice_customer->withinVATCountry() && $this->invoice_customer->getVatValidation() != ''){
            $vatNumber = $this->invoice_customer->getVatValidation();
        }

        $this->view->companyEmail = $this->settings->get("Billing E-mail");
        $this->view->user = $this->invoice_customer;
        $this->view->companyName        = $this->settings->get("Company Name");
        $this->view->companyAddress       = trim($this->settings->get("Company Address"));
        $this->view->companyURL           = $this->settings->get("Company URL");
        $this->view->invoice            = $this->invoice_customer->withinVATCountry()? $this->invoice_customer->lang('VAT Invoice') : $this->invoice_customer->lang('Invoice');
        $this->view->invoiceNum          = $this->invoice->getId();
        $this->view->duedate              = date($this->settings->get('Date Format'),$this->invoice->getDate("timestamp"));
        $this->view->datecreated          = date($this->settings->get('Date Format'),$this->invoice->getDateCreated("timestamp"));
        $this->view->invoicelogo          = $invoicelogo;
        $this->view->paidDate             = $paidDate;
        $this->view->paymentLabel         = $paymentLabel;
        $this->view->paymentMethod        = $paymentMethod;
        $this->view->pmtRef               = $pmtRef;
        $this->view->pmtTransactions      = $this->ParseInvoiceTransactions($this->invoice->getId());
        $this->view->vatNumber            = $vatNumber;
        $this->view->customerNum          = $this->invoice->getUserID();
        $this->view->customerOrg          = $customerOrg;
        $this->view->customerName         = $this->invoice_customer->getFirstName().' '.$this->invoice_customer->getLastName();
        $this->view->customerAddress      = $cAddr;
        $this->view->customerPhone        = $this->invoice_customer->getPhone();
        $this->view->customerEmail       = $this->invoice_customer->getEmail();
        $this->view->footerContent       = trim($this->settings->get("Invoice Footer"));
        $this->view->disclaimerContent   = trim($this->settings->get("Invoice Disclaimer"));
        $additionalnotes = trim($this->invoice->getNote());
        if($additionalnotes == ''){
            $additionalnotes = trim($this->settings->get('Additional Notes For Invoices'));
        }else{
            $additionalnotes .= "\n".trim($this->settings->get('Additional Notes For Invoices'));
        }
        $this->view->additionalnotes      = $additionalnotes;

        // If the customer is taxable show the tax header
        $taxName = $this->invoice->getTaxName();
        if($taxName == ''){
            $taxName = $this->invoice_customer->lang('Tax');
        }
        $tax2Name = $this->invoice->getTax2Name();
        if($tax2Name == ''){
            $tax2Name = $this->invoice_customer->lang('Tax');
        }
        $invoiceheaders = array();
        $columnWidth = array();
        $totalHeaders = 2;
        if ($this->invoice_customer->isTaxable()) {
            if($this->invoice->GetTax() > 0 && $this->invoice->GetTax2() > 0){
                $invoiceheaders = array($this->invoice_customer->lang('Invoice Items'),$this->invoice_customer->lang('Price'),$taxName,$tax2Name);
                $columnWidth = array("55%","15%","15%","15%");
                $totalHeaders = 4;
            }elseif($this->invoice->GetTax2() > 0){
                $invoiceheaders = array($this->invoice_customer->lang('Invoice Items'),$this->invoice_customer->lang('Price'),$tax2Name);
                $columnWidth = array("70%","15%","15%");
                $totalHeaders = 3;
            }else{
                $invoiceheaders = array($this->invoice_customer->lang('Invoice Items'),$this->invoice_customer->lang('Price'),$taxName);
                $columnWidth = array("70%","15%","15%");
                $totalHeaders = 3;
            }
        } else {
            $invoiceheaders = array($this->invoice_customer->lang('Invoice Items'),$this->invoice_customer->lang('Price'));
            $columnWidth = array("85%","15%");
            $totalHeaders = 2;
        }
        $this->view->invoiceheaders = array();
        for ($i = 0; $i < count($invoiceheaders); $i++) {
            $width = $columnWidth[$i];
            if($columnWidth[$i] == "15%"){
                $align = "right";
            }else{
                $align = "left";
            }
            $header = array();
            $header['width'] = $width;
            $header['align'] = $align;
            $header['text'] = $invoiceheaders[$i];
            $this->view->invoiceheaders[] = $header;

        }
        $totalTaxed = 0;
        $totalTaxed2 = 0;

        $this->view->invoiceEntries = array();

        foreach ($this->invoice->getInvoiceEntries() as $invoiceEntry) {
            $entry = array();
            $entry['data'] = array();

            $packageID = "";
            //get full identifier
            if ($invoiceEntry->AppliesTo() > 0) {

                $UserPackage = new UserPackage($invoiceEntry->AppliesTo());
                if ($UserPackage->existsInDB()) {
                    //lets get type of product .. if it is hosting or domain then lets not add full identifier
                    if ( $UserPackage->getCustomField('Server Id') > 0 || $UserPackage->getProductType() == 3 ) {
                        $packageID = "#".$invoiceEntry->AppliesTo()." - ";
                    } else {
                        $packageID = $UserPackage->getReference(true). " - ";
                    }
                }
            }
            $tPrice =  $invoiceEntry->getPrice();
            // If the customer is taxable show the tax pricing

            //let's create the data labels
            $invoice_label = $packageID.$invoiceEntry->getDescription();
            $details = nl2br($invoiceEntry->getDetail());

            $invoice_description = $this->getPeriod($invoiceEntry, $this->invoice_customer) . (($details != '')? $details : '');
            $invoice_description = str_replace( "<br>","<br/>", $invoice_description);
            $dataLabel = array(trim($invoice_label), trim($invoice_description));

            if ($this->invoice_customer->isTaxable()) {

                $taxPrice = 0;
                if ($invoiceEntry->getTaxable()) {
                    $taxPrice = $invoiceEntry->getPrice() * $this->invoice->getTax() / 100;

                    if($invoiceEntry->isCoupon()){
                        $taxPrice = $this->invoice->getTaxesDiscount($invoiceEntry->AppliesTo());
                    }

                    $totalTaxed += $taxPrice;

                    $strTaxPrice = $currency->format($UserCurrency, $taxPrice, true, "NONE", false, true);
                } else {
                    $strTaxPrice = $this->invoice_customer->lang('Not Taxable');
                }

                $tax2Price = 0;
                if ($invoiceEntry->getTaxable() && $this->invoice->GetTax2() > 0) {

                    if($taxPrice > 0 && $this->invoice->isTax2Compound()){
                        $tax2Price = ($invoiceEntry->getPrice() + $taxPrice) * $this->invoice->GetTax2() / 100;
                    }else{
                        $tax2Price = $invoiceEntry->getPrice() * $this->invoice->GetTax2() / 100;
                    }

                    if($invoiceEntry->isCoupon()){
                        $tax2Price = $this->invoice->getTaxesDiscount($invoiceEntry->AppliesTo(), 2);
                    }

                    $totalTaxed2 += $tax2Price;

                    $strTax2Price = $currency->format($UserCurrency, $tax2Price, true, "NONE", false, true);
                } else {
                    $strTax2Price = $this->invoice_customer->lang('Not Taxable');
                }

                if($this->invoice->GetTax() > 0 && $this->invoice->GetTax2() > 0){

                    $price = $currency->format($UserCurrency, $tPrice, true, "NONE", false, true);
                    $data = array($dataLabel,$price,$strTaxPrice,$strTax2Price);
                    $columnWidth = array("55%","15%","15%","15%");

                    for ($i = 0; $i < count($data); $i++) {
                        $innerData = array();
                        $innerData['width'] = $columnWidth[$i];
                        $innerData['data'] = $data[$i];

                        if($columnWidth[$i] == "15%"){
                            $innerData['align'] = 'right';
                        }else{
                            $innerData['align'] = 'left';
                        }
                        $entry['data'][] = $innerData;
                    }
                }elseif($this->invoice->GetTax2() > 0){

                    $price = $currency->format($UserCurrency, $tPrice, true, "NONE", false, true);
                    $data = array($dataLabel,$price,$strTax2Price);
                    $columnWidth = array("70%","15%","15%");
                    for ($i = 0; $i < count($data); $i++) {
                        $innerData = array();
                        if($columnWidth[$i] == "15%"){
                            $innerData['align'] = 'right';
                        }else{
                            $innerData['align'] = 'left';
                        }
                        $innerData['width'] = $columnWidth[$i];
                        $innerData['data'] = $data[$i];
                        $entry['data'][] = $innerData;
                    }
                }else{

                    $price = $currency->format($UserCurrency, $tPrice, true, "NONE", false, true);
                    $data = array();
                    $columnWidth = array();
                    $data = array($dataLabel,$price,$strTaxPrice);
                    $columnWidth = array("70%","15%","15%");
                    for ($i = 0; $i < count($columnWidth); $i++) {
                        $innerData = array();
                        if($columnWidth[$i] == "15%"){
                            $innerData['align'] = 'right';
                        }else{
                            $innerData['align'] = 'left';
                        }
                        $innerData['width'] = $columnWidth[$i];
                        $innerData['data'] = $data[$i];
                        $entry['data'][] = $innerData;
                    }
                }

            } else {

                $price = $currency->format($UserCurrency, $tPrice, true, "NONE", false, true);
                $data = array();
                $data = array($dataLabel,$price);
                $columnWidth = array("85%","15%");
                    for ($i = 0; $i < count($data); $i++) {
                        $innerData = array();
                        if($columnWidth[$i] == "15%"){
                            $innerData['align'] = 'right';
                        }else{
                            $innerData['align'] = 'left';
                        }
                        $innerData['width'] = $columnWidth[$i];
                        $innerData['data'] = $data[$i];
                        $entry['data'][] = $innerData;
                    }

            }

            $this->view->invoiceEntries[] = $entry;

        }


        $totalLabels = array();
        $totalPrices = array();


        $tTaxTotal = $totalTaxed;
        $tTax2Total = $totalTaxed2;
        $tDisplaySubTotal =  $this->invoice->getSubTotal();
        $tTax = $this->invoice->GetTax();
        $tTax2 = $this->invoice->GetTax2();
        $tPrice =  $this->invoice->getPrice();

        // If the customer is taxable show the tax information
        if ($this->invoice_customer->isTaxable() && ($this->invoice->GetTax() > 0 || $this->invoice->GetTax2() > 0)){
            $totalLabels[] = $this->invoice_customer->lang('Subtotal').':';
            $totalPrices[] = $currency->format($UserCurrency, $tDisplaySubTotal, true, "NONE", false, true);
            if ($this->invoice->GetTax() > 0){
                $totalLabels[] = $taxName.' '.$tTax.'%:';
                $totalPrices[] = $currency->format($UserCurrency, $tTaxTotal, true, "NONE", false, true);
            }
            if ($this->invoice->GetTax2() > 0){
                $totalLabels[] = $tax2Name.' '.$tTax2.'%:';
                $totalPrices[] = $currency->format($UserCurrency, $tTax2Total, true, "NONE", false, true);
            }
        }

        if ($this->invoice->isPaid()) {
            $totalLabels[] = $this->invoice_customer->lang('Total Paid').':';
        }else{
            $totalLabels[] = $this->invoice_customer->lang('Total Due').':';
        }
        $totalPrices[] = $currency->format($UserCurrency, $tPrice, true, "NONE", false, true);


        //  ADDED FOR: VARIABLE_PAYMENTS - Balance Due  //
        if($this->invoice->isPartiallyPaid()){
            $tBalanceDue = $this->invoice->getBalanceDue();
            $PartiallyPaid = $tPrice - $tBalanceDue;
            $totalLabels[] = $this->invoice_customer->lang('Paid').':';
            $totalPrices[] = $currency->format($UserCurrency, $PartiallyPaid, true, "NONE", false, true);

            $totalLabels[] = $this->invoice_customer->lang('Balance Due').':';
            $totalPrices[] = $currency->format($UserCurrency, $tBalanceDue, true, "NONE", false, true);

        }

        $this->view->totalLabels = array();

        for ($i = 0; $i < count($totalLabels); $i++) {
            $total = array();
            if($totalHeaders == 4){
                $total["colspan"] = "colspan='3' ";
            }elseif ($totalHeaders == 3) {
                $total["colspan"] = "colspan='2' ";
            }else {
                $total["colspan"] = " ";
            }

            $total["totalLabel"] = $totalLabels[$i];
            $total["totalPrice"] = $totalPrices[$i];
            $this->view->totalLabels[] = $total;
        }

        $this->html = $this->view->render('invoice.phtml');
    }

    function ParseInvoiceTransactions($invoiceid) {
        $invoicetransactions = "";

        if ($invoiceid != 0) {
        //get all transactions and loop thru to show them
            $query = "SELECT id, accepted, response, UNIX_TIMESTAMP(transactiondate) AS transdate FROM invoicetransaction WHERE invoiceid=? ORDER BY transactiondate DESC";
            $result = $this->db->query($query, $invoiceid);
            $num = $result->getNumRows();

            while(list($transid,$accepted,$response,$transdate) = $result->fetch()) {

                $invoicetransactions .= date($this->settings->get('Date Format'),$transdate)." ".date ("h:i:s A",$transdate)." - Transaction: ".$transid." -";

                $classResponse = '';
                if($accepted == 0) {
                    $classResponse = 'red';
                }

                //$response = NE_Lib::convertCurrencyToHTML($response);
                $invoicetransactions .= "<font class='.$classResponse.'>".$response."</font>";

                $invoicetransactions .= "<br/>";
            }

            if ($num==0) {
                $invoicetransactions = "<font class=red>".$this->user->lang("There are no recorded transactions for this invoice")."</font>";
            }

            $invoicetransactions = "<p style='padding-left:25px;'>".$invoicetransactions."</p>";

        }
        return $invoicetransactions;
    }

    function show()
    {
        $filename = 'invoice-'.$this->invoice->getId().'.pdf';
        $this->pdf->Output($filename,'I');
    }

    function get()
    {
        // return $this->pdf->getPDFData();
        return $this->pdf->Output('','S');
    }

    private function getPeriod($invoiceEntry, $user)
    {
        $period = '';
        $daterangearray = unserialize($this->settings->get('Invoice Entry Date Range Format'));
        if ($invoiceEntry->getPeriodStart() && $daterangearray[0] != '') {
            $period = '- ' . CE_Lib::formatDateWithPHPFormat($invoiceEntry->getPeriodStart(), $daterangearray[0]);
            if($invoiceEntry->getPeriodEnd() && $daterangearray[1] != ''){
                $period .= ' '.$user->lang('thru').' ';
                $period .=  CE_Lib::formatDateWithPHPFormat($invoiceEntry->getPeriodEnd(), $daterangearray[1]);
            }
            $period .= '<br />';
        }

        return $period;
    }
}

?>
