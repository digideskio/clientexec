<?php

//permissions array
// index=> array(permission, dependency, customer_allowed, description)

$config = array(
    'mandatory'         => true,
    'recommended'       => true,
    'description'       => 'Support management module.',
    'navButtonLabel'    => lang('Support'),
    'dependencies'  => array(),
    'hasSchemaFile'     => true,
    'hasInitialData'    => true,
    'hasUninstallSQLScript' => false,
    'hasUninstallPHPScript' => false,
    'order'             => 5,
    'settingTypes'      => array(8),
    'hooks'         => array(
        'Menu'                          => 'Support_menu',
        'Settings'                      => array(   'Support_Settings'  => array(
                                                                'internalName'      => 'support',
                                                                'tabLabel'          => lang('General'),
                                                                'order'             => 6,
                                                                ),
                                                    'Knowledgebase_Settings'  => array(
                                                                            'internalName'            => 'knowledgebase',
                                                                            'tabLabel'                => lang('KB'),
                                                                            'order'                   => 5,
                                                                            ),
                                         ),
        'Events'                        => array('PipedEmail' => 'Support_Event_PipedEmail')
    ),
    'permissions' => array(
        1   => array('support_view',                                    0, true,   lang('View ticket list')),
        2   => array('support_view_assigned_department_tickets',        1, false,  lang('View tickets assigned to their departments')),
        3   => array('support_view_assigned_otherdepartment_tickets',   1, false,  lang('View tickets assigned to other departments, or unassigned')),
        4   => array('support_reply_any_open_ticket',                   1, false,  lang('Reply to any assigned ticket')),
        28  => array('support_view_closed_tickets',                     1, true,   lang('View closed tickets')),
        6   => array('support_submit_ticket',                           1, true,   lang('Submit ticket')),
        8   => array('support_edit_ticket',                             1, false,  lang('Edit ticket')),
        9   => array('support_delete_trouble_ticket',                   1, false,  lang('Delete ticket')),
        10  => array('support_assign_tickets',                          1, false,  lang('Assign tickets')),
        25  => array('support_close_tickets',                           1, true,   lang('Close tickets')),
        26  => array('support_view_rates',                              1, false,  lang('View closed tickets service rates')),
        27  => array('support_view_eventlog',                           1, true,   lang('View Event Log for Ticket events')),
        29  => array('support_view_live_chat',                          0, true, lang('View Live Chat'), 'accounts'),
    ),
    'hreftarget' => '#'
);

// language entries referred in this module, but that need to be loaded always
// (e.g. menu item labels)
$lang = array(
  lang('Unassigned Tickets'),
  lang('Awaiting Reply'),
  lang('All Open Tickets'),
  lang('All Tickets Closed Today'),
);

?>
