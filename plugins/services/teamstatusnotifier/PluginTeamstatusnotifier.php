<?php

require_once 'library/CE/NE_MailGateway.php';

require_once 'modules/clients/models/UserGateway.php';
require_once 'modules/admin/models/ServicePlugin.php';
require_once 'modules/support/models/AutoresponderTemplateGateway.php';
/**
* @package Plugins
*/
class PluginTeamstatusnotifier extends ServicePlugin
{
    public $hasPendingItems = false;

    function getVariables()
    {
        $variables = array(
            lang('Plugin Name')   => array(
                'type'          => 'hidden',
                'description'   => '',
                'value'         => lang('Team Status Notifier'),
            ),
            lang('Enabled')       => array(
                'type'          => 'yesno',
                'description'   => lang('When enabled, Team Status Notifications are sent by e-mail when this service is triggered.'),
                'value'         => '0',
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

    function execute()
    {

        $messages = array();

        $numMailsSent = 0;
        $failedAddressees = array();
        $mailGateway = new NE_MailGateway();

        $templategateway = new AutoresponderTemplateGateway();
        $template = $templategateway->getEmailTemplateByName("Team Status Activity Template");

        $basicSubject               = $template->getSubject();
        $basicBody                  = $template->getContents();

        $template = $templategateway->getEmailTemplateByName("Team Status Activity Dynamic Block Template");
        $basicBodyDynamicBlock      = $template->getContents();

        $template = $templategateway->getEmailTemplateByName("Team Status Activity Reply Template");
        $basicBodyReply             = $template->getContents();

        $lastRun = $this->settings->get('plugin_teamstatusnotifier_lastrun');
        $userGateway = new UserGateway();
        $result2 = $userGateway->getAdminIds(1);

        while (list($uid) = $result2->fetch()) {
            $user = new user($uid);

            $HaveTeamStatus = false;

            $conditions = "";
            if(isset($lastRun)){
                $conditions .= " WHERE (ts.status_datetime >= '".$lastRun."')";
            }

            $subject = $basicSubject;
            $body    = $basicBody;
            $tempBodyDynamicBlock = $body;
            $tempBodyDynamicBlock = '';

            $query4 =  "SELECT ts.userid, ts.userstatus,ts.status_datetime,ts.replyid ";
            $query4 .= "FROM team_status ts left join users u on ts.userid=u.id ";
            $query4 .= $conditions." ORDER BY ts.status_datetime DESC";
            $result4 = $this->db->query($query4);
            while(list($tsuserid, $tsuserstatus, $tsstatus_datetime, $tsreplyid) = $result4->fetch()){
                $HaveTeamStatus = true;

                $bodyReply = $basicBodyReply;
                if(isset($tsreplyid)){
                    $query5 =  "SELECT userid FROM team_status WHERE id = ? ";
                    $result5 = $this->db->query($query5, $tsreplyid);
                    list($useridreplied) = $result5->fetch();

                    if(isset($useridreplied)){
                        $userreplied = new user($useridreplied);
                        $bodyReply = $basicBodyReply;
                        $bodyReply = str_replace("[REPLIEDTEAMSTATUSUSERNAME]", $userreplied->getFullName(), $bodyReply);
                    }else{
                        $bodyReply = '';
                    }
                }else{
                    $bodyReply = '';
                }

                $tsuser = new user($tsuserid);
                $bodyDynamicBlock = $basicBodyDynamicBlock;
                $bodyDynamicBlock = str_replace("[TEAMSTATUSUSERNAME]", $tsuser->getFullName(), $bodyDynamicBlock);
                $bodyDynamicBlock = str_replace("[TEAMSTATUS]", nl2br($tsuserstatus), $bodyDynamicBlock);
                $bodyDynamicBlock = str_replace("[TEAMSTATUSDATE]", $tsstatus_datetime, $bodyDynamicBlock);
                $bodyDynamicBlock = str_replace("[TEAMSTATUSREPLYINFO]", $bodyReply, $bodyDynamicBlock);

                $tempBodyDynamicBlock .= $bodyDynamicBlock;
            }

            if($HaveTeamStatus){
                $body = str_replace("[TEAMSTATUSDYNAMICBLOCK]", $tempBodyDynamicBlock, $body);

                $from = $this->settings->get('Support E-mail');
                $fromName = $this->settings->get('Company Name');

                $userid = $user->getId();

                try {
                    $mailGateway->sendMailMessage($body,
                        $from,
                        $fromName,
                        $userid,
                        '',
                        $subject,
                        3,
                        0,
                        'notifications',
                        '',
                        '',
                        MAILGATEWAY_CONTENTTYPE_HTML
                    );
                } catch ( Exception $e ) {
                    $failedAddressees[] = $uid;
                }
            }
        }

        if ($failedAddressees) {
            $users = implode(', ', $failedAddressees);
            CE_Lib::log(1, "Error trying to E-mail Team Status Activity to user(s) $users");
            $messages[] = new CE_Error($this->user->lang('Error trying to E-mail Team Status Activity to user(s) %s', $users));
        }

        $lastRun = date('Y-m-d H:i:s');
        $this->settings->updateValue('plugin_teamstatusnotifier_lastrun', $lastRun);
        array_unshift($messages, $this->user->lang('%s message(s) sent', $numMailsSent));

        return $messages;
    }

    function output() {}

    function dashboard() {}
}
