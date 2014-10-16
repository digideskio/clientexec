<?php

$config = array(
    'mandatory'         => true,
    'description'       => 'Billing functionality.',
    'navButtonLabel'    => lang('Billing'),
    'dependencies'  => array(),
    'hasSchemaFile'     => false,
    'hasInitialData'    => true,
    'hasUninstallSQLScript' => false,
    'hasUninstallPHPScript' => false,
    'order'             => 4,
    'settingTypes'      => array(4),
    'hooks'         => array(
        'Menu'                              =>  'Billing_menu',
        'Settings'                              => array(   'Billing_Settings'  => array(
                                                                'internalName'      => 'billing',
                                                                'tabLabel'          => lang('Billing'),
                                                                'order'             => 5,
                                                                ),
                                                            'Invoicing_Settings'  => array(
                                                                'internalName'      => 'invoicing',
                                                                'tabLabel'          => lang('Billing'),
                                                                'order'             => 5,
                                                                )
                                                        )
    ),
    'permissions' => array(
        17   => array('billing_view',                        0,  true, lang('View active invoices')),
        30   => array('billing_create',                      17, false, lang('Edit, delete and add invoices')),
        31   => array('billing_recurring_overview',          17, true, lang('View "Recurring Overview"')),
        32   => array('billing_edit_recurring',              31, false, lang('Edit recurring invoice')),
        33   => array('billing_delete_recurring',            31, false, lang('Delete recurring charge')),
        34   => array('billing_mark_invoice_paid',           17, false, lang('Mark invoice paid or unpaid')),
        35   => array('billing_send_invoices',               17, true, lang('Send invoices and receipts')),
        39   => array('billing_process_invoices',            17, false, lang('Process invoices')),
        40   => array('billing_generate_invoices',           17, false, lang('Generate pending invoices and run batch payments')),
        41   => array('billing_refund_invoices',             17, false, lang('Refund invoices')),
        42   => array('billing_void_invoices',               17, false, lang('Void invoices')),
        45   => array('billing_add_variable_payment',        17, false, lang('Add variable payment to an invoice')),
        46   => array('billing_apply_account_credit',        17, true, lang('Apply account credit to an invoice')),
        47   => array('billing_credit_invoices',             17, false, lang('Credit invoices to credit balance'))
    ),
    'hreftarget' => '#'
);

?>
