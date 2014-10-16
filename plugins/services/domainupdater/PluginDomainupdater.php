<?php
require_once 'modules/admin/models/ServicePlugin.php';
require_once 'modules/admin/models/Package.php';
require_once 'modules/admin/models/TopLevelDomainGateway.php';

/**
* @package Plugins
*/
class PluginDomainupdater extends ServicePlugin
{
    protected $featureSet = 'products';
    public $hasPendingItems = false;

    /**
     * All plugin variables/settings to be used for this particular service.
     *
     * @return array The plugin variables.
     */
    function getVariables()
    {
        $variables = array(
            lang('Plugin Name')   => array(
                'type'          => 'hidden',
                'description'   => '',
                'value'         => lang('Domain Updater'),
            ),
            lang('Enabled')       => array(
                'type'          => 'yesno',
                'description'   => lang('When enabled, this service will check all active domains and update the internal expiration date of the domains.  This date is used to send expiration notices among other things.'),
                'value'         => '0',
            ),
            lang('Sync Due Date?')       => array(
                'type'          => 'yesno',
                'description'   => lang('When enabled, a domain will have its next due date updated to the expiration date at the registrar.'),
                'value'         => '0',
            ),
            lang('Cancel Domains?')       => array(
                'type'          => 'yesno',
                'description'   => lang('When enabled, a domain in ClientExec that does not exist at the registrar will be marked as cancelled.'),
                'value'         => '0',
            ),
            lang('Force Recurring?')       => array(
                'type'          => 'yesno',
                'description'   => lang('When enabled, a domain will always have the recurring fee turned on and enabled.'),
                'value'         => '1',
            ),
            lang('E-mail Notifications')       => array(
                'type'          => 'textarea',
                'description'   => lang('If domains are updated when this service is run, a summary E-mail will be sent to this address.'),
                'value'         => '',
            ),
            lang('Run schedule - Minute')  => array(
                'type'          => 'text',
                'description'   => lang('Enter number, range, list or steps'),
                'value'         => '30',
                'helpid'        => '8',
            ),
            lang('Run schedule - Hour')  => array(
                'type'          => 'text',
                'description'   => lang('Enter number, range, list or steps'),
                'value'         => '*',
            ),
            lang('Run schedule - Day')  => array(
                'type'          => 'text',
                'description'   => lang('Enter number, range, list or steps'),
                'value'         => '*',
            ),
            lang('Run schedule - Month')  => array(
                'type'          => 'text',
                'description'   => lang('Enter number, range, list or steps'),
                'value'         => '*',
            ),
            lang('Run schedule - Day of the week')  => array(
                'type'          => 'text',
                'description'   => lang('Enter number in range 0-6 (0 is Sunday) or a 3 letter shortcut (e.g. sun)'),
                'value'         => '*',
            ),
        );

        return $variables;
    }


    /**
     * Execute the domain update.
     * This service will update all domains internal status to show when a domain is going to expire.
     *
     * @return void
     */
    function execute() {
        include_once 'modules/admin/models/StatusAliasGateway.php' ;

        $domainNameGateway = new DomainNameGateway($this->user);
        $messages = array();

        // get all active domains
        $statuses = StatusAliasGateway::getInstance($this->user)->getPackageStatusIdsFor(PACKAGE_STATUS_ACTIVE);
        $query = "SELECT d.id FROM domains d, package p, promotion pr WHERE d.status IN(".implode(', ', $statuses).") AND d.Plan=p.id AND p.planid=pr.id AND pr.type=3";

        $result = $this->db->query($query);
        while ( $row = $result->fetch() ) {
            $userPackage = new UserPackage($row['id']);
            $domainName = $userPackage->getCustomField('Domain Name');
            $registrar = $userPackage->getCustomField('Registrar');
            // no registrar, so skip this entry
            if ( $registrar == '' || $registrar == null ) {
                continue;
            }

            // only run if registered by host, or transfered and completed
            if ( ($userPackage->getCustomField('Registration Option') == 0) ||
                ($userPackage->getCustomField('Registration Option') == 1 && $userPackage->getCustomField('Transfer Status') == 'Completed') ) {

                try {
                    $domainInfo = $domainNameGateway->getGeneralInfoViaPlugin($userPackage);
                } catch ( MethodNotImplemented $e ) {
                } catch ( Exception $e ) {
                    // connection issue so stop running the service.
                    if ( $e->getCode() == EXCEPTION_CODE_CONNECTION_ISSUE ) {
                        $messages[] = 'A connection issue has occured, stopping domain updater.';
                        $this->sendSummaryEmail($messages);
                        return;
                    }

                    if ( $this->settings->get('plugin_domainupdater_Cancel Domains?') == 1 ) {
                        $messages[] = $domainName . ' does not seem to be in your registrar account, marking as cancelled in ClientExec.';
                        $userPackage->cancel(false);
                        continue;
                    }
                }



                // check if we're in redemption status
                // ToDo: We should not check RGP here, but set a variable in domainInfo instead
                if ( $this->settings->get('plugin_domainupdater_Cancel Domains?') == 1 ) {
                    if ( $domainInfo['registration'] == 'RGP' ) {
                        $messages[] = $domainName . ' is in redemption, marking as cancelled in ClientExec.';
                        $userPackage->cancel(false);
                        continue;
                    }
                }

                if ( $this->settings->get('plugin_domainupdater_Sync Due Date?') == 1 ) {
                    $recurringFee = $userPackage->getRecurringFeeEntry();
                    $date = date('Y-m-d', strtotime($domainInfo['expires']));

                    if ( $this->settings->get('plugin_domainupdater_Force Recurring?') == 1 ) {
                        // RecurringFee didn't exist. Set the values.
                        if (!$recurringFee->GetID()) {
                            $recurringFee->SetRecurring(1);
                            $messages[] = 'Enabled recurring billing for ' . $domainName . '.';

                            $recurringFee->setNextBillDate($date);
                            $messages[] = 'Updated next bill date for ' . $domainName . ' to ' . $date . '.';

                            $recurringFee->SetCustomerID($userPackage->CustomerId);
                            $recurringFee->SetBillingTypeID(-1);
                            $recurringFee->setAppliesToId($userPackage->getId());
                            $recurringFee->setPaymentTerm($this->getPaymentTerm($userPackage));
                            $recurringFee->setTaxable($userPackage->isTaxable());
                            $recurringFee->SetAmount(0);
                            $recurringFee->Update();
                            $recurringFee->SetAmount($userPackage->getPrice(false));
                        }


                        // all domains should be recurring.
                        if ( $recurringFee->GetRecurring() == 0 ) {
                            $recurringFee->SetRecurring(1);
                            // if the domain isn't recurring, we should default to a payment term of one year.
                            $recurringFee->setPaymentTerm($this->getPaymentTerm($userPackage));
                            $messages[] = 'Enabled recurring billing for ' . $domainName . '.';
                        }
                    }

                    if ( $recurringFee->getNextBillDate() != $date ) {
                        $updateNextDueDate = false;
                        // Only update the next due date if:
                        // - there are not invoices for the package
                        // OR
                        // ( - the difference between the next due date and the domain expiration date is lower than 6 months (180 days)
                        //   AND
                        //   - the difference between the last invoice for the package and the domain expiration date is greater than 6 months (180 days)
                        // )
                        $lastInvoiceDate = $userPackage->getLastInvoiceDate();
                        if($lastInvoiceDate === false){
                            $updateNextDueDate = true;
                        }else{
                            $nextBillDateDiff = CE_Lib::date_diff($recurringFee->getNextBillDate(), $date);
                            $lastInvoiceDateDiff = CE_Lib::date_diff($userPackage->getLastInvoiceDate(), $date);
                            if ((abs($nextBillDateDiff["d"]) < 180)
                              &&(abs($lastInvoiceDateDiff["d"]) > 180)) {
                                $updateNextDueDate = true;
                            }
                        }

                        if($updateNextDueDate) {
                            $recurringFee->setNextBillDate($date);
                            $messages[] = 'Updated next bill date for ' . $domainName . ' to ' . $date . '.';
                        }
                    }
                    $recurringFee->Update();
                }

                $numberOfDaysTillExpires = (int)$domainNameGateway->getExpiresInDays($domainInfo['expires']);
                $userPackage->setCustomField('Plugin Status', $domainNameGateway->getPluginStatusByDays($numberOfDaysTillExpires));
                $userPackage->setCustomField('Expiration Date', strtotime($domainInfo['expires']));
                $userPackage->setCustomField('Auto Renew', $domainInfo['auto_renew']);
            }
        }
        if ( count($messages) > 0 ) {
            $this->sendSummaryEmail($messages);
        }
    }

    function sendSummaryEmail($messages) {
        if ( $this->settings->get('plugin_domainupdater_E-mail Notifications') != '' ) {
            $body = "Domain Updater Summary:\n\n";
            $body .= implode("\n", $messages);
            $mailGateway = new NE_MailGateway();
            $destinataries = explode("\r\n", $this->settings->get('plugin_domainupdater_E-mail Notifications'));
            foreach ($destinataries as $destinatary) {
                if ( $destinatary != '' ) {
                    $mailGateway->mailMessageEmail( $body,
                        $this->settings->get('Support E-mail'),
                        $this->settings->get('Support E-mail'),
                        $destinatary,
                        false,
                        $this->user->lang("Domain Updater Service Summary"));
                }
            }
        }
    }


     /**
     * Function to get the first valid payment term of a domain.
     *
     * @return int payment term
     */
    private function getPaymentTerm($userPackage)
    {
        $tldGateway = new TopLevelDomainGateway($this->user);

        for ( $i=1; $i<=10; $i++ ) {
            $price = $tldGateway->getPeriodPricesForTld($userPackage->Plan, $i);
            if ( isset($price['price']) && $price['price'] > 0 ) {
                return $i * 12;
            }
        }
    }


    function output() { }
    function dashboard() { }
}
