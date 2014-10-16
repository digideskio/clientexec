<?php
require_once 'modules/admin/models/ActiveOrderGateway.php';

class Admin_SignuppublicController extends CE_Controller_Action
{

    var $moduleName = "admin";

    private function initialize_view()
    {
        // Verify if there are Payment Processors configured
        $plugins = new NE_PluginCollection("gateways", $this->user);
        $thereAreBillingPlugins = false;
        while ($tplugin = $plugins->getNext()) {
            //we have to make sure it isn't grabbing from logged in Admin
            if ( $tplugin->getVariable("In Signup") || (($this->user->getPaymentType() == $tplugin->getInternalName()) && $this->user->getPaymentType() != "0" && !$this->user->IsAdmin())  ) {
                $thereAreBillingPlugins = true;
            }
        }
        if(!$thereAreBillingPlugins) {
            CE_Lib::redirectPage('index.php?fuse=home&view=main', $this->user->lang('There are no Payment Processors configured!'));
        }

        include_once 'modules/admin/models/PackageType.php';

        $aogateway = new ActiveOrderGateway($this->user);

		// Override the cart
        if(@$_GET['cleanCart']) {
            $aogateway->destroyTempItem(true);
            $aogateway->destroyCart();
            $aogateway->destroyCurrency();
            $aogateway->destroyInvoiceInformation();
            CE_Lib::redirectPage('order.php');
        }

    	$ret_view = array();
        $ret_view['step'] = $this->getParam('step',FILTER_SANITIZE_STRING,"0");

        //we need to pull the style here based on what the group is
        $ret_view['productgroup'] = $this->getParam('productGroup',FILTER_SANITIZE_NUMBER_INT,0);
        $ret_view['product'] = $this->getParam('product',FILTER_SANITIZE_NUMBER_INT,0);
        $ret_view['bundled'] = $this->getParam('bundled',FILTER_SANITIZE_NUMBER_INT,0);

        // if we have 0 bundled products, then we need to force bundled to be 0, so we don't allow the domain to be ordered without the hosting package.
        if ( count($this->session->bundledProducts) == 0 ) {
            $ret_view['bundled'] = 0;
        }

		$ret_view['paymentterm'] = $this->getParam('paymentterm',FILTER_SANITIZE_NUMBER_INT,1);
        $ret_view['tempInformation'] = array();
        //CE_Lib::debug($ret_view['tempItem']);

        //if productgroup 0 then just get first one that is available on signup and set that as the selected one
        //if productgroup 0 but product is set grap the productgroup so we don't need to pass both ids
        //lets get the productgroup if we were passed a product without the productgroup
        if( $ret_view['product'] > 0 && $ret_view['productgroup'] == 0 ){
            $tPackage = new Package($ret_view['product']);
            if ($tPackage->planname == "") {
            	CE_Lib::addErrorMessage($this->user->lang("The selected product does not exist"));
            	CE_Lib::redirectPage("order.php?step=1");
            }

            $ret_view['productgroup'] = $tPackage->planid;

        } else if ( $ret_view['product'] > 0) {
        	$tPackage = new Package($ret_view['product']);
        	if ($tPackage->planname == "") {
        		CE_Lib::addErrorMessage($this->user->lang("The selected product does not exist"));
            	CE_Lib::redirectPage("order.php?step=1");
            }
        }

		$ret_view['summary'] = $aogateway->getCartSummary();
        $packageType = new PackageType($ret_view['productgroup']);
        $ret_view['packageType'] = $packageType->getType();

		return $ret_view;
    }

    protected function cart1Action()
    {
        $this->title = 'Step 1';
    	$this->cssPages = array("templates/common/views/admin/signuppublic/cart.css","templates/common/views/admin/signuppublic/cart_pricing.css");
    	$this->jsLibs = array("templates/common/views/admin/signuppublic/cart.js","javascript/customfields.js");

    	$aogateway = new ActiveOrderGateway($this->user);
    	$this->view->assign($this->initialize_view());

        if ($this->view->bundled == 0) {
            unset($this->session->bundledProducts);
        }

     	//unset($this->session->avoidaddpackage);

    	// Has somebody specified a domain name in the URL?
        if(isset($_GET['domainName']) && isset($_GET['tld'])) {
        	$this->session->domainName = $this->getParam('domainName',FILTER_SANITIZE_STRING);
            $this->session->tld = $this->getParam('tld',FILTER_SANITIZE_STRING);
            $this->session->autoSearchType = 'register';
            if ( isset($_GET['autoSearchType']) ) {
                $this->session->autoSearchType = $_GET['autoSearchType'];
            }
        }

        //First Time through step 0
        require_once 'modules/admin/models/PackageTypeGateway.php';
        $packageTypes = PackageTypeGateway::getDefaultPackageTypes('');
        $plancount = $packageTypes->getNumItems();

        $this->view->productGroups = array();

        //Loop through all the packagetypes
        if ($this->view->productgroup==0) {
        	$firstgroup = 0;
        } else {
        	$firstgroup = $this->view->productgroup;
        }

        while($packageType = $packageTypes->fetch()) {

        	if ($firstgroup == 0) {
                if ( $this->view->packageType == null ) {
                    $this->view->packageType = $packageType->fields['type'];
                }
        		$firstgroup = $packageType->fields['id'];
        	}

        	$this->view->productGroups[] = array(
        		'id' => $packageType->fields['id'],
                'name' => $packageType->fields['name'],
	            'description' => $packageType->fields['description'],
	            'type' => $packageType->fields['type'],
	            'style' => $packageType->fields['style'],
                'advanced' => unserialize($packageType->fields['advanced']),
                'insignup' => $packageType->fields['insignup'],
	        );
        }

        //lets see if there are any products that are stock controled
        //that need to be cleared
		$aogateway->clearAllOrdersForProductId();

        //$productGroup = PackageTypeGateway::getPackageTypes($this->view->productgroup);
        //$productGroup = $productGroup->fetch();

        $this->view->packages = $aogateway->getPackageForSelectedGroup( array(
        	"productGroup" => $firstgroup,
        	"selectedProduct" => $this->view->product,
        	"paymentterm" => $this->view->paymentterm
    	));

        $this->view->tempInformation['bundledProducts'] = $this->session->bundledProducts;

        if ( $this->view->packageType == PACKAGE_TYPE_DOMAIN ) {
            include_once 'modules/admin/models/TopLevelDomainGateway.php';
            $TopLevelDomainGateway = new TopLevelDomainGateway();
            // ensure that we have valid pricing before showing the tld
            foreach ( $this->view->packages as $key => $p ) {
                $data = $TopLevelDomainGateway->getTLDData($p['id']);

                // remove the registrar and taxable index if there is one.
                if ( isset($data['registrar']) ) {
                    unset($data['registrar']);
                }
                if ( isset($data['taxable']) ) {
                    unset($data['taxable']);
                }

                if ( count($data) < 1 ) {
                    unset($this->view->packages[$key]);
                }
            }

            // allow passing domain name and tld to domain packages
            $this->view->domainName = '';
            $this->view->tld = '';
            if ( isset($this->session->domainName) && isset($this->session->tld) ) {
                $this->view->domainName = $this->session->domainName;
                $this->view->tld = $this->session->tld;
                $this->view->autoSearchType = $this->session->autoSearchType;

                unset($this->session->domainName);
                unset($this->session->tld);
                unset($this->session->autoSearchType);
            }

            // used for hosting package sub domains
            $this->view->subdomains = array();

            // check if we have sub domains, we only want to show sub-domains if we have a parent package (which means this is a bundle).
            if ( isset($this->session->cartParentPackage) ) {
                $cartItems = unserialize(base64_decode(@$this->session->cartContents));
                // get the product id of the parent package
                $productId = $cartItems[$this->session->cartParentPackage]['productId'];
                $package = new Package($productId);
                $advanced = unserialize($package->advanced);
                if ( isset($advanced['subdomain']) && $advanced['subdomain'] != '' ) {
                    $this->view->subdomains = explode(';', $advanced['subdomain']);
                }
            }
        }
        $this->view->cartParentPackage = null;
        if ( isset($this->session->cartParentPackage) ) {
            $this->view->cartParentPackage = $this->session->cartParentPackage;
        }

        // check if we have savings to show.
        $hasSavings = $aogateway->doWeHaveAnySavings($this->view->packages);
        $this->view->showSaved = ($this->settings->get('Include Saved Percentage') && $hasSavings == true) ? 1 : 0;

        $this->view->hideSetupFees = $this->settings->get('Hide Setup Fees');
    }

    protected function cart2Action()
    {
        // if we are on step 2, and have this set, we need to unset it, or it causes issues.
        if ( isset($this->session->on_back_send_to_invoice_id) ) {
            unset($this->session->on_back_send_to_invoice_id);
        }

        $this->title = 'Step 2';
    	$this->cssPages = array("templates/common/views/admin/signuppublic/cart.css");
    	$this->jsLibs = array("templates/common/views/admin/signuppublic/cart.js","javascript/customfields.js");

    	$aogateway = new ActiveOrderGateway($this->user);
    	$this->view->assign($this->initialize_view());

    	//let's check if we are not configuring anything then let's just move on from step2
        $package = new Package($this->view->product);
        $customFields = $aogateway->getCustomFields('package', "", $package, $this->view->productgroup);
        $this->view->customFields = $customFields['customFields'];

        //let's see if we have custom fields
        if ( is_array($this->view->customFields) ) {
        	$customFieldCount = count($this->view->customFields);
        } else {
	        $customFieldCount = 0;
        }

		$this->view->packageAddons = $aogateway->getAddons($this->view->product, $this->view->paymentterm);

		if ( is_array($this->view->packageAddons) ) {
			$addonCount = count($this->view->packageAddons);
		} else {
			$addonCount = 0;
		}

        //Something to check here is if I'm working on a bundled product (count > 0)
        //If we are working on a bundled product don't check to see if hasbundled products
        //We only allow bundled off of the main product and not child products
        //So if the session is set for bundled products and this product isn't it do not check if bundled.
        if ( !isset($this->session->bundledProducts) ) {

            $this->session->bundledProducts == array();
            $this->session->bundledProducts['bundlesleft'] = $package->getBundledProducts();
            if (!$this->session->bundledProducts['bundlesleft']) {
                $this->session->bundledProducts['bundlesleft'] = array();
                $hasbundledproducts = false;
            } else {
                $hasbundledproducts = true;
            }


        } else if (count($this->session->bundledProducts['bundlesleft']) > 0) {
            $hasbundledproducts = true;
        } else {
            $hasbundledproducts = false;
        }

        // Handle the form post
		if(!$addonCount && !$customFieldCount && !$hasbundledproducts) {
            $cartItemId = $aogateway->processFormPost($_REQUEST, 'selectandprocess');
            //we don't have addons, custom fields or bundled products so let's move forward to checkout
			CE_Lib::redirectPage("order.php?step=3");

	    } elseif($hasbundledproducts && (!$addonCount && !$customFieldCount)) {

            $cartItemId = $aogateway->processFormPost($_REQUEST, 'selectandprocess');
            $this->session->cartParentPackage = $cartItemId;

            $forward_to_bundled_id = array_shift($this->session->bundledProducts['bundlesleft']);
            $bundlePackage = new Package($forward_to_bundled_id);
            CE_Lib::redirectPage("order.php?step=1&productGroup=".$bundlePackage->id."&bundled=1");

	    } elseif($addonCount || $customFieldCount) {
            $cartItemId = $aogateway->processFormPost($_REQUEST, 'select');
            //this view //let's show some custom fields

        } else {
            CE_Lib::debug(1,"Error in signup.  Not sure how to handle product selection");
	    	// Went wrong somewhere.
	    	CE_Lib::redirectPage("order.php?step=3");
	    }

        $this->view->cartItemId = $cartItemId;

        // Do we have a manually specified domain
        if( (isset($_GET['domainName']) && isset($_GET['tld'])) || (isset($this->session->domainName) && isset($this->session->tld))) {
            $foundDomainField = false;
            foreach ( $this->view->customFields as $id => $customField ) {
                if ( @$customField['isDomain'] == 1 ) {
                    $foundDomainField = true;
                    $this->view->customFields[$id]['savedValue'] = (isset($_GET['domainName']))?$_GET['domainName']:$this->session->domainName;
                    $this->view->customFields[$id]['value'] = (isset($_GET['domainName']))?$_GET['domainName']:$this->session->domainName;
                    // PHP kept erroring unless we did this if the long way...
                    if ( isset($_GET['tld']) ) {
                        $this->view->customFields[$id]['savedValue'] .= '.' . $_GET['tld'];
                        $this->view->customFields[$id]['value'] .= '.' . $_GET['tld'];
                    } else {
                        $this->view->customFields[$id]['savedValue'] .= '.' . $this->session->tld;
                        $this->view->customFields[$id]['value'] .= '.' . $this->session->tld;
                    }
                    break;
                }
            }
            // only clear the session variable if we've found a domain field.
            // If we haven't found a domain field, it means we're bundling, so we need to save it for the next call to cart2
            if ( $foundDomainField == true ) {
                unset($this->session->domainName);
                unset($this->session->tld);
                unset($this->session->autoSearchType);
            }
        }

        //Adding temp information to view
        //Debug information
        $this->view->tempInformation['bundledProducts'] = $this->session->bundledProducts;

        if ( $this->settings->get('Enforce Password Strength') ) {
            $this->view->passwordFields = array();
            foreach ( $this->view->customFields as $customField ) {
                if ( $customField['fieldtype'] == TYPEPASSWORD ) {
                    $this->view->passwordFields[] = $customField['id'];
                }
            }
            $this->view->enforcePassword = true;
        }
    }

    protected function cart3Action()
    {
        $this->title = 'Step 3';
    	$this->cssPages = array("templates/common/views/admin/signuppublic/cart.css", "templates/common/css/customfields_public.css");
    	$this->jsLibs = array("javascript/customfields.js","templates/common/views/admin/signuppublic/cart.js","templates/common/views/admin/signuppublic/cart3.js",'templates/admin/views/clients/profile/VAT.js');

        //if we have $this->session->on_back_send_to_invoice_id that means we came back
        //unset and go to invoice
        if(isset($this->session->on_back_send_to_invoice_id)) {
            $invoice_id = $this->session->on_back_send_to_invoice_id;
            unset($this->session->on_back_send_to_invoice_id);
            CE_Lib::addErrorMessage($this->user->lang("The following invoice was not paid.")."  Please submit a <a href='index.php?fuse=support&controller=ticket&view=submitticket'>support ticket</a> if you need assistance.");
            CE_Lib::redirectPage('index.php?fuse=billing&controller=invoice&view=invoice&id='.$invoice_id);
        }

    	$aogateway = new ActiveOrderGateway($this->user);
    	$this->view->assign($this->initialize_view());
        $this->session->bundledProducts = array();

        // Allow Coupons?
        if(@$this->settings->get("Accept Coupons") == 1) {
            $this->view->acceptCoupons = true;
        }

        $this->view->assign($this->view->summary);

        if ( $this->view->summary['cartCount'] == 0 ) {
    		CE_Lib::redirectPage('order.php?cleanCart=true');
        	return;
    	}

    	//$this->session->avoidaddpackage = true;

    	// Do we have an error from processing?
        if(isset($_REQUEST['errorReason']) && trim($_REQUEST['errorReason']) != "") {
            CE_Lib::addErrorMessage(trim($_REQUEST['errorReason']));
        }

        //customfields
        $customFields = $aogateway->getCustomFields('profile',$this->session->oldFields);
        $this->view->customFields = $customFields['customFields'];
        $this->view->state_var_id = $customFields['state_var_id'];
        $this->view->country_var_id = $customFields['country_var_id'];
        $this->view->vat_var_id = $customFields['vat_var_id'];

        if ($this->user->getEmail() != '' && !$this->user->isAdmin()) {
            $this->view->loggedIn = true;
            $countryCode = $this->user->getCountry();
            $stateCode = $this->user->getState();
            $vatNumber = $this->user->getVatNumber();
            $isTaxable = $this->user->isTaxable();
            $userID = $this->user->getId();
        } else {
            $this->view->loggedIn = false;
            $countryCode = null;
            $stateCode = null;
            $vatNumber = null;
            $isTaxable = true;
            $userID = false;
        }

        //CE_Lib::debug($this->view->summary);
        //$this->view->tax = $aogateway->processCartTax($this->view->summary, $countryCode, $stateCode, $vatNumber, $isTaxable, $userID);

        // Start billing / payment processors
        $plugins = new NE_PluginCollection("gateways", $this->user);

        // Get a list of valid payment processors
        $pluginsArray = array();
        while ($tplugin = $plugins->getNext()) {
            $tvars = $tplugin->getVariables();
            $tvalue = $this->user->lang($tvars['Plugin Name']['value']);
            $pluginsArray[$tvalue] = $tplugin;
        }
        uksort($pluginsArray, "strnatcasecmp");

        // Start making the array
        $this->view->paymentmethods = array();

        // Loop the processors
        $new_values = $aogateway->yourinfo_gateway_info($pluginsArray);
        $this->view->assign($new_values);

        // Handle T&C's
        if (@$this->settings->get('Show Terms and Conditions') == 1) {

             // Site URL for T&Cs
             if (@$this->settings->get('Terms and Conditions URL')) {
                 $this->view->termsConditions = '-1';
                 $this->view->termsConditionsUrl = $this->settings->get('Terms and Conditions URL');
             } else {
                 $this->view->termsConditions = 1;

                 $termsAndConditions = $this->settings->get('Terms and Conditions');
                 $termsAndConditions = str_replace('&quot;','"',$termsAndConditions);
                 $termsAndConditions = str_replace('&#039;','\'',$termsAndConditions);
                 $this->view->termsConditionsText = $termsAndConditions;
             }
         }

         // Handle Captcha
        $this->view->showCaptcha = false;
        if ($this->settings->get('Request Access Code') == 1) {
            $this->view->showCaptcha = true;
            include_once 'library/CE/3rdparty/reCAPTCHA/recaptchalib.php';
            if ( $this->settings->get('ReCaptcha Public Key') != '' ) {
                $this->view->publicKey = $this->settings->get('ReCaptcha Public Key');
            } else {
                $this->view->publicKey = '6LdjHLwSAAAAAAdauQwa1sD678Sxxj14JXtItd06';
            }
        }

        // Set the password strength information
        if($this->settings->get('Enforce Password Strength')) {
            $this->view->enforcePassword = true;
            $this->view->minPassLength = $this->settings->get('Minimum Password Length');
        }

        $this->view->cartCancel = true;
        $this->view->cartTotal = $this->view->summary['cartTotal']['price'];

		// Set the cancel order URL
		if($this->settings->get('Cancel Order URL') != '') {
			$this->view->cancelOrderURL = $this->settings->get('Cancel Order URL');
		} else {
			$this->view->cancelOrderURL = 'order.php?cleanCart=true';
		}

    }

    protected function phoneverificationAction()
    {
        $this->cssPages = array("templates/common/views/admin/signuppublic/cart.css", "templates/common/css/customfields_public.css");
    	  $this->jsLibs = array("templates/common/views/admin/signuppublic/cart.js");

        $aogateway = new ActiveOrderGateway($this->user);
    	  $this->view->assign($this->initialize_view());
        $cartSummary = $aogateway->getCartSummary();

        $phoneVerificationPlugins = new NE_PluginCollection('phoneverification', $this->user);
        while ($phoneVerificationPlugin = $phoneVerificationPlugins->getNext()) {

            // if plugin is not enabled, continue with next one
            if (!$this->settings->get("plugin_".$phoneVerificationPlugin->getInternalName()."_Enabled")) {
                continue;
            }

            if ($this->settings->get("plugin_".$phoneVerificationPlugin->getInternalName()."_Minimum Bill Amount to Trigger Telephone Verification") > @$cartSummary['cartTotal']['truePrice']
                  || (@$this->session->fraudScore && $this->settings->get("plugin_".$phoneVerificationPlugin->getInternalName()."_Minimum Fraud Score to Trigger Telephone Verification") > $this->session->fraudScore)) {
                break;
            }

            $phoneId = $this->user->getCustomFieldsObj()->_getCustomFieldIdByType(typePHONENUMBER);
            $langId = $this->user->getCustomFieldsObj()->_getCustomFieldIdByType(typeCOUNTRY);
            $customFields = $aogateway->getCustomFields('profile',$this->session->oldFields);
            foreach ($customFields['customFields'] as $customField) {
              if ($customField['id'] == $phoneId) {
                $phoneNum = $customField['value'];
              }
              if ($customField['id'] == $langId) {
                $lang = $customField['value'];
              }
            }

            $alreadyCalled = (isset($this->session->phoneNum) && $this->session->phoneNum == $phoneNum);

            // Do we have a session set already with the number?
            if(isset($this->session->phoneCode) && $alreadyCalled) {

                // Stops people re-loading the page by accident and having a new code generated
                $this->view->isCalled = true;

            } else {
                $this->session->phoneCode = rand(1000, 9999);

                $this->view->isCalled = false;

                $phoneVerificationPlugin->setPhoneNumber($phoneNum);
                $this->session->phoneNum = $phoneNUm;
                $phoneVerificationPlugin->setLanguage($lang);
                $phoneVerificationPlugin->execute($this->session->phoneCode);
                $this->view->phoneNumber = $phoneNum;
            }

            break;
        }
    }

    protected function phoneverificationcheckAction()
    {
        $aogateway = new ActiveOrderGateway($this->user);

        if($_POST['code'] == $this->session->phoneCode) {
            unset($this->session->phoneverification_tries);
            unset($this->session->phoneCode);
            unset($this->session->phoneNum);
            $_REQUEST = array_merge($_REQUEST, $this->session->oldFields);
            $aogateway->create_new_account();
        } else {
            $this->session->phoneverification_tries = @$this->session->phoneverification_tries + 1;
            if ($this->session->phoneverification_tries >= 10) {
              CE_Lib::addErrorMessage($this->user->lang('Sorry, you have attempted to enter the code incorrectly too many times.'));
    		      CE_Lib::redirectPage('order.php?cleanCart=true');
            } else {
              CE_Lib::addErrorMessage($this->user->lang('Sorry, the verification code you entered is invalid. Please try again.'));
              CE_Lib::redirectPage("order.php?step=phone-verification");
            }
        }
    }

    protected function updateparentpackageAction()
    {
        $aogateway = new ActiveOrderGateway($this->user);
        $aogateway->updateCartItem($this->session->cartParentPackage, array('bundledDomain' => $_POST['domainname']));

        $nextURL = 'order.php?step=3';
        $this->send(array("nexturl"=>$nextURL));
    }

    protected function savedomainfieldsAction()
    {
        $products = $_REQUEST['products'];
        if (!is_array($products)) {
            throw new CE_Exception("There was an error with the submitted data");
        }

        $aogateway = new ActiveOrderGateway($this->user);
        foreach($products as $product) {
            $aogateway->processFormPost($product, 'selectandprocess');
        }

        //let's see if we still have other bundled products we need to do
        if (isset($this->session->bundledProducts) && count($this->session->bundledProducts['bundlesleft']) > 0) {
            $forward_to_bundled_id = array_shift($this->session->bundledProducts['bundlesleft']);
            $bundlePackage = new Package($forward_to_bundled_id);
            $nextURL = "order.php?step=1&productGroup=".$bundlePackage->id."&bundled=1";
        } else {
            $nextURL = 'order.php?step=3';
        }

        $this->send(array("nexturl"=>$nextURL));
    }

    protected function saveproductfieldsAction()
    {

    	$this->cssPages = array("templates/common/views/admin/signuppublic/cart.css");
    	$this->jsLibs = array("templates/common/views/admin/signuppublic/cart.js");
        $product_id = $this->getParam('product',FILTER_SANITIZE_NUMBER_INT);

    	$aogateway = new ActiveOrderGateway($this->user);
        // store the parent package here, in case we need to use it to store the domain from the next step
        $this->session->cartParentPackage = $aogateway->processFormPost($_REQUEST, 'process');

        $package = new Package($product_id);
        if ( !isset($this->session->bundledProducts) ) {
            $this->session->bundledProducts == array();
            $this->session->bundledProducts['bundlesleft'] = $package->getBundledProducts();
            if (!$this->session->bundledProducts['bundlesleft']) {
                $this->session->bundledProducts['bundlesleft'] = array();
                $hasbundledproducts = false;
            } else {
                $hasbundledproducts = true;
            }

        } else if (count($this->session->bundledProducts['bundlesleft']) > 0) {
            $hasbundledproducts = true;
        } else {
            $hasbundledproducts = false;
        }

        if ($hasbundledproducts) {
            $forward_to_bundled_id = array_shift($this->session->bundledProducts['bundlesleft']);
            $bundlePackage = new Package($forward_to_bundled_id);
            CE_Lib::redirectPage("order.php?step=1&productGroup=".$bundlePackage->id."&bundled=1");
        } else {
            // no bundles, so drop the parent package id
            $this->session->cartParentPackage = null;
            unset($this->session->cartParentPackage);
        }

    	CE_Lib::redirectPage('order.php?step=3');

    }

    protected function validatecouponAction()
    {
    	require_once 'modules/billing/models/Coupon.php';

    	// Check the item
        if(@$_REQUEST['itemID']) {

            // As we can't call the view ITEMS we have to work the session manually
            if(CE_Lib::generateSignupCartHash() == @$this->session->cartHash && @$this->session->cartHash != null) {

                // Get the cart items
                $cartItems = unserialize(base64_decode(@$this->session->cartContents));

                // Get the cart item
                if(@is_array($cartItems[$_REQUEST['itemID']])) {

                    // Do we have a coupon?
                    if(@$_REQUEST['couponCode']) {

                        $couponCode = $_REQUEST['couponCode'];

                        // Get the product's information
                        $package = new Package($cartItems[$_REQUEST['itemID']]['productId']);
                        $package->getProductPricing();

                        // Get the product group information
                        if(!@$productGroupInfo[$package->planid]) {
                            $productGroup = PackageTypeGateway::getPackageTypes($package->planid);
                            $productGroupInfo[$package->planid] = $productGroup->fetch();
                        }

                        $billingCycle = $cartItems[$_REQUEST['itemID']]['params']['term'];

                        // If it is a Domain, the billing cycle is set in years, so need to convert it to months.
                        if(@$cartItems[$_REQUEST['itemID']]['params']['isDomain']){
                            $billingCycle = $billingCycle * 12;
                        }

                        // Check the coupon code
                        $return = Coupon::validate(@$_REQUEST['couponCode'], $productGroupInfo[$package->planid]->fields['id'], $package->id, $billingCycle);

                        // Is coupon valid?
                        if(@is_array($return)) {

                            // Push the coupon code to the product
                            $cartItems[$_REQUEST['itemID']]['couponCode'] = $return['id'];

                            // Add the coupon code the session
                            $cartItems['couponCodes'][$return['id']] = $return;

                            // Update session
                            $this->session->cartContents = base64_encode(serialize($cartItems));

                            // Save the new hash
                            $this->session->cartHash = CE_Lib::generateSignupCartHash();

                            // Hvae to send blank or the JS wont validate
                            $this->send();

                        } else {
                            throw new CE_Exception($this->user->lang("The coupon code '%s' is invalid.", $couponCode), EXCEPTION_CODE_NO_EMAIL);
                        }

                    } elseif(@!$_REQUEST['couponCode'] && @$cartItems[$_REQUEST['itemID']]['couponCode']) {

                        // Push the coupon code to the product
                        $cartItems[$_REQUEST['itemID']]['couponCode'] = null;

                        // Update session
                        $this->session->cartContents = base64_encode(serialize($cartItems));

                        // Save the new hash
                        $this->session->cartHash = CE_Lib::generateSignupCartHash();

                        // Hvae to send blank or the JS wont validate
                        $this->send();

                    } else {
                       throw new CE_Exception($this->user->lang("The coupon code '%s' is invalid.", $couponCode), EXCEPTION_CODE_NO_EMAIL);
                    }
                } else {
                    throw new CE_Exception($this->user->lang("The coupon code '%s' is invalid.", $couponCode), EXCEPTION_CODE_NO_EMAIL);
                }
            } else {
               throw new Exception($this->user->lang("Unknown error."), EXCEPTION_CODE_NO_EMAIL);
            }
        } else {
            throw new CE_Exception($this->user->lang("The coupon code '%s' is invalid.", $couponCode), EXCEPTION_CODE_NO_EMAIL);
        }
    }

    /**
     * Action dispatch method
     *
     * @return void
     */
    protected function deletecartitemAction()
    {

    	$aogateway = new ActiveOrderGateway($this->user);

        // Check the item
        if(@$_REQUEST['cartItem']) {

            // The problem here is that we are in an action but need to call functions
            // From a View file. So rather than initialing the class, just call the functions
            // This means we need to set the hash again manually as $this-> doesnt work inside
            // The removeFromCart function.

            // Remove the item
            $aogateway->removeFromCart($_REQUEST['cartItem']);

            // Take a look if we have a bundle item to remove too
            if(isset($_REQUEST['bundleCartItem'])) {
               $aogateway->removeFromCartViaDomain($_REQUEST['bundleCartItem']);
            }

            // Save the new hash
            $this->session->cartHash = CE_Lib::generateSignupCartHash();

        } else {
            throw new Exception("No Cart Item Specified");
        }

        $this->send();

    }

    protected function getfinalpricinginfoAction()
    {

        $aogateway = new ActiveOrderGateway($this->user);
        $cartSummary = $aogateway->getCartSummary();

        if ($this->user->getEmail() != '' && !$this->user->isAdmin()) {
            $countryCode = $this->user->getCountry();
            $stateCode = $this->user->getState();
            $vatNumber = $this->user->getVatNumber();
            $isTaxable = $this->user->isTaxable();
            $userID = $this->user->getId();
        } else {
            $countryCode = $this->getParam('country',FILTER_SANITIZE_STRING,"");
            $stateCode = $this->getParam('state',FILTER_SANITIZE_STRING,"");
            $vatNumber = $this->getParam('vatNumber',FILTER_SANITIZE_STRING,"");
            $isTaxable = true;
            $userID = false;
        }

        // Sort out the tax
        $totals = $aogateway->processCartTax($cartSummary, $countryCode, $stateCode, $vatNumber, $isTaxable, $userID);
        $itemcount = $cartSummary['cartCount'];

        $this->send(array("totals"=>$totals,"itemcount"=>$itemcount));
    }

    protected function searchdomainAction()
    {

        $search_type = $this->getParam('searchType',FILTER_SANITIZE_STRING);
        $domain_name = $this->getParam('name',FILTER_SANITIZE_STRING);
        $domain_tld = $this->getParam('tld',FILTER_SANITIZE_STRING);
        $product_id = $this->getParam('product',FILTER_SANITIZE_NUMBER_INT);
        $full_domain_name = $domain_name.".".$domain_tld;

        $return_array = array();
        // Preset some global TPL vars
        $return_array['domainName'] = htmlentities($full_domain_name);
        $return_array['domainNameSuggest'] = false;
        $return_array['transferCheckList'] = $this->settings->get('Force Transfer Checklist');

        include "modules/admin/models/TopLevelDomainGateway.php";
        $tldgateway = new TopLevelDomainGateway($this->user);

        $return_array2 = $tldgateway->search_domain($domain_name,$domain_tld, $product_id, $search_type);
        $return_array = array_merge($return_array,$return_array2);
        $this->send(array("search_results"=>$return_array) );
    }

    /**
    * Process new order
    */
    protected function processAction()
    {

        $aogateway = new ActiveOrderGateway($this->user);
        $init_vars = $this->initialize_view();

        //let's save old fields in the event we need to redirect
        $this->session->oldFields = $_REQUEST;
        $aogateway->process_new_order();
        $this->session->oldFields = null;

        $this->send();
    }

    protected function successAction()
    {
        $this->title = 'Completed';
        $aogateway = new ActiveOrderGateway($this->user);
        // This information can be required for affiliates URL
        $invoice_information = unserialize(base64_decode($this->session->invoice_information));
        if(is_array($invoice_information)
          && isset($invoice_information['invoice_id'])
          && isset($invoice_information['invoice_amount_no_tax'])
          && isset($invoice_information['invoice_amount'])){
            $this->view->invoice_id            = $invoice_information['invoice_id'];
            $this->view->invoice_amount_no_tax = $invoice_information['invoice_amount_no_tax'];
            $this->view->invoice_amount        = $invoice_information['invoice_amount'];
        }else{
            $this->view->invoice_id            = 0;
            $this->view->invoice_amount_no_tax = 0;
            $this->view->invoice_amount        = 0;
        }

        // Now we are here, destroy the cart session
        $aogateway->destroyCart();

        // Destroy also the currency
        $aogateway->destroyCurrency();

        // Destroy also the invoice information
        $aogateway->destroyInvoiceInformation();

        $this->view->shareTwitter = $this->settings->get('Show Twitter Button');
        $this->view->tweet = $this->settings->get('Default Tweet');
        $this->view->shareGPlus = $this->settings->get('Show Google+ Button');
        $this->view->companyName = $this->settings->get('Company Name');
        $this->view->companyUrl = $this->settings->get('Company URL');
        $this->view->fbAppId = $this->settings->get('Facebook App ID');$this->settings->get('Facebook App ID');
        $this->view->shareFB = $this->settings->get('Show Facebook Button') && $this->view->fbAppId;
    }

    protected function testpasswordstrengthAction()
    {
        $returnArray = array();
        $returnArray['valid'] = true;
        $returnArray['errorMessage'] = "";

        $password = $this->getParam('password');

        include_once 'modules/admin/models/PasswordStrength.php';
        $passwordStrength = new PasswordStrength($this->settings, $this->user);

        $passwordStrength->setPassword($password);
        if ( !$passwordStrength->validate() ) {
            $errorMessage = '';
            foreach ($passwordStrength->getMessages() as $message) {
                $errorMessage .= $message . '<br/>';
            }
            $returnArray['valid'] = false;
            $returnArray['errorMessage'] = $errorMessage;
        }

        $this->send($returnArray);

    }
}
