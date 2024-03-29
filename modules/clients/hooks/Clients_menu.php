<?php

/**
 * Clients Menu Hooks class
 *
 * @package Clients
 */
class Clients_menu extends NE_MenuHook
{

    var $width = "345px;";
    var $offset = "-250px;";
    var $direction = "left";
    var $snapin_key = "second";

    /**
     * function which display the client menus
     *
     * @param object &$user     user reference
     * @param object &$customer customer reference
     *
     * @return void
     */
    function Clients_menu($user)
    {
        if ($user->hasPermission("admin_edit_announcements") ||
            $user->hasPermission("admin_edit_notifications") )
         {
            //contact users
            $menuItem = new NE_MenuItem($user->lang("Contact Users"), "#");
            $menuItem->setKey("first");
            $menuItem->addViews(array('adminviewnotifications','adminviewannouncements'));
            $menuItem->addPermissions('clients_view_domains');
            $subMenu = new NE_MenuHook($user);

            if ($user->hasPermission("admin_edit_notifications")) {
                $submenuItem = new NE_MenuItem($user->lang("Notifications"),"index.php?fuse=admin&controller=notifications&view=adminviewnotifications");
                $subMenu->addItem($submenuItem);
            }

            if($user->hasPermission("admin_edit_announcements")){
                $submenuItem = new NE_MenuItem($user->lang('Announcements'),"index.php?fuse=admin&amp;view=adminviewannouncements&amp;controller=announcements");
                $subMenu->addItem($submenuItem);
            }

            $menuItem->addSubmenu($subMenu);
            $this->addItem($menuItem);
        }

        //Client List
        $menuItem = new NE_MenuItem($user->lang("Clients"), "index.php?fuse=clients&controller=user&view=viewusers");
        $menuItem->setKey("first");
        $menuItem->addViews(array('viewpending','profileevents','ShowEmail','viewusers','profilecontact','profileaccounts','profilepassword','profilebilling','profilerecurringcharges','profilenotes','profiledomains','profileproducts','profileproduct','profileinvoices','profileuninvoiced','ViewMergeClient'));
        $menuItem->addPermissions('clients_view_customers');

        $subMenu = new NE_MenuHook($user);
        include_once 'library/CE/NE_GroupsGateway.php';
        //get all customer groups
        $groupsGateway = new NE_GroupsGateway();
        $groupsIt = $groupsGateway->getCustomerGroups();
        $submenuItem = new NE_MenuItem($user->lang("Pending Orders"),"index.php?fuse=clients&amp;view=viewpending&controller=orders");
        $subMenu->addItem($submenuItem);
        $submenuItem = new NE_MenuItem($user->lang("All Clients"),"index.php?fuse=clients&controller=user&view=viewusers");
        $subMenu->addItem($submenuItem);
        while ($group = $groupsIt->fetch()) {
            $groupname = $group->getName();
            if (strlen($groupname) > 17) $groupname = substr($groupname, 0, 17)."...";
            $submenuItem = new NE_MenuItem($groupname,"index.php?fuse=clients&controller=user&view=viewusers&group_id=".$group->getId());

            $subMenu->addItem($submenuItem);
        }

        $menuItem->addSubmenu($subMenu);
        $this->addItem($menuItem);

        //packages
        $menuItem = new NE_MenuItem($user->lang("Packages"), "index.php?fuse=clients&controller=packages&view=hostingpackagelist");
        $menuItem->setKey("second");
        $menuItem->addViews(array('hostingpackagelist','domainslist','generalpackageslist', 'sslpackagelist'));
        $menuItem->addPermissions('clients_view_domains');

        $subMenu = new NE_MenuHook($user);
        $submenuItem = new NE_MenuItem($user->lang("Hosting Packages"),"index.php?fuse=clients&controller=packages&view=hostingpackagelist");
        $subMenu->addItem($submenuItem);

        $submenuItem = new NE_MenuItem($user->lang("Domain Packages"),"index.php?fuse=clients&controller=packages&view=domainslist");
        $subMenu->addItem($submenuItem);

        $submenuItem = new NE_MenuItem($user->lang("General Packages"),"index.php?fuse=clients&controller=packages&view=generalpackageslist");
        $subMenu->addItem($submenuItem);

        $submenuItem = new NE_MenuItem($user->lang("SSL Packages"),"index.php?fuse=clients&controller=packages&view=sslpackagelist");
        $subMenu->addItem($submenuItem);

        if ( $user->hasPermission('clients_cancel_packages') ) {
            $submenuItem = new NE_MenuItem($user->lang("Cancellations"),"index.php?fuse=clients&controller=packages&view=cancellations");
            $subMenu->addItem($submenuItem);
        }

        $menuItem->addSubmenu($subMenu);
        $this->addItem($menuItem);

    }
}