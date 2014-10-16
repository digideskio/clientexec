<?php
require_once 'library/CE/NE_Plugin.php';

/**
 * SnapinPlugin Model Class
 *
 * @category Model
 * @package  Admin
 * @license  ClientExec License
 * @link     http://www.clientexec.com
 */
class SnapinPlugin extends NE_Plugin
{
    /**
     * Additional notes for snapins which are visible during settings
     * @var string
     */
    var $settingsNotes = '';

    /**
     * System plugins are used to add functionality to Clientexec without
     * it appearing as though the feature is a plugin.  All settings will be
     * hidden and snapin will be enabled.
     * Note: When system plugin is true the global permissions will be false.  Meaning you will need to set which users have permission
     * to use this feature.   In this case it is advantages to set $this->permissionLocation to a location that best suits this plugin.
     * @var boolean
     */
    var $systemPlugin = false;

    /**
     * Location of settings for this snapin.   If not set snapin settings
     * will be located in Setup->Plugins->Snapins
     * @example
     * Available options users, support, style, signup, security, packages, email, company, billing
     * @var string
     */
    public $settingsLocation = '';

    /**
     * Location of permissions for this snapin.   If not set snapin permisions
     * will be grouped together with other non grouped snapins as Features (snapins) Permissions
     * @example
     * Available options: home, clients, domains, billing, support, knowledgebase, files, reports, admin
     * @var string
     */
    private $permissionLocation = '';

    /**
     * Location on main menu this snapin can be accessed from.  Ignored if passed via mappings array
     * @deprecated Use mapping array instead of menuLocation
     * @var string
     */
    var $menuLocation = 'tools';

    /**
     * Set to true when you want to control the logic of what views to render on your own
     * @var boolean
     */
    var $overrideTemplate = false;

    /**
     * Set to true if you don't want to have the layout of the site returned with the view (returns only content)
     * @var boolean
     */
    var $disableLayout = false;

    /**
     * Specify if this plugin should be enabled by default
     * @var boolean
     */
    private $enabledByDefault = false;

    /**
     * Used to tell CE not to make this plugin visible while in DEMO mode
     * @var boolean
     */
    var $hideOnDemo = false;

    /**
     * Details of the matching mapping used by base class
     * @var array
     */
    private $matched_mapping = array();


    /**
     * Define mappings supported by this plugin.  Will be used by CE to determine where to place plugin content
     *
     * @example
     * <br/>
     * <pre>
     * var $mapping = array(
     *     "topmenu" => array(
     *         "type"=>"admin",
     *         "loc"=>"clients",
     *         "title"=>"Transactions",
     *         "tpl"=>"globaltransactions",
     *     ),
     *     "hooks" => array(
     *         array(
     *             "loc" => "admin_profiletab",
     *             "title" => "Transactions",
     *             "tpl" => "profiletransactions",
     *         )
     *     )
     * );
     * </pre>
     * @var array
     */
    private $mappings = array();

    public $title = '';

    //Defined arrays that we have pre configured snapins
    //to work with.  As we expand CE we need to update
    //these arrays so that mappings can get enhanced
    private $arrOfAdminLocations = array("admin_profiletab","admin_profileproducttab","admin_top_active_panel","admin_bottom_active_panel");
    // private $arrOfAdminSelectors = array();
    private $arrOfPublicLocations = array();
    // private $arrOfPublicSelectors = array();

    /**
     * Output generated by the plugin (don't echo it, just return it)
     *
     * @abstract
     * @return string Plugin's HTML
     */
    public function view()
    {
    }

    /**
     * Helper methods to populate the top menu mapping for this snapin
     * Note: Should be called in init
     * Admin valid locations are clients,billing,support, tools (defaults to tools)
     * Public valid locations are products, profile, billing, support, tools (defaults to tools)
     *
     *
     * @param string $type  Type (admmin, public)
     * @param string $loc   Topmenu Location - leave blank for default location
     * @param string $tpl   Name of template that will be rendered as template.phtml
     * @param string $title Title to use in the top menu
     * @param string $description Description used in snapin settings for this functionality
     */
    protected function addMappingForTopMenu($type, $loc, $tpl, $title="", $description = "")
    {
        if ($description === "") $description = $this->user->lang("No Description");
        if ($title === "") $title = "No title";
        if ($loc === "") $loc = "tools";
        $this->mapping['topmenu'][] = array("type"=> $type, "loc" => $loc, "title" => $title, "tpl" => $tpl, "desc"=> $description);
        $this->addMappingView($tpl, $title);
    }

    /**
     * Adds a mapping for the public main page
     *
     * @param string $tpl       Tpl file
     * @param string $title     Title for plugin
     * @param string $desc      Description to be used under the title
     * @param string $icon      Icon of type fontawesome (ex: "icon-bullhorn")
     * @param string $iconstyle Style you want to apply to icon (ex: "margin:2px;")
     */
    protected function addMappingForPublicMain($tpl, $title = "", $desc = "", $icon = "", $iconstyle = "")
    {
        $this->mapping['publicmain'][] = array("tpl" => $tpl, "title" => $title, "desc" => $desc, "icon"=>$icon, "iconstyle"=>$iconstyle);
    }

    /**
     * Adds a mapping for the snapin settings view
     * Note: This will not appear for system settings
     *
     * @param string $method Method to execute on the snapin when button is clicked
     * @param string $title  String to show on button in settings view
     * @param string $description  String to show on button in settings view
     */
    protected function addMappingForSettings($method, $title, $description)
    {
        $this->mapping['setting'][] = array("method" => $method, "title"=>$title, "description"=>$description);
    }

    /**
     * Helper methods to populate hooks mappings for this snapin
     * Should be called in init
     * @example
     * Locations consist of<br/>
     * profileheader - Tab view will be added in active customer view<br/>
     * profileproduct - Tab view will be added to active customer's package view<br/>
     *
     * @param string $loc   Name of subview to add tab top
     * @param string $title Title to use in the top menu
     * @param string $tpl   Name of template that will be rendered as template.phtml
     * @param string $description Description used in snapin settings for this functionality
     */
    protected function addMappingHook($loc, $tpl, $title="", $description="")
    {
        if ($description === "") $description = $this->user->lang("No Description");
        if ($title === "") $title = $tpl;
        $this->mapping['hooks'][] = array("loc" => $loc, "title" => $title, "tpl" => $tpl, "desc"=> $description);
    }

    /**
     * Adds additional views to snapins
     *
     * @param string $tpl unique to each snapin
     * @param string $title To be displayed in title
     */
    protected function addMappingView($tpl, $title) {
        $this->mapping['views'][$tpl] = array("tpl" => $tpl, "title" => $title);
    }

    /**
     * Adds additional actions to snapins
     *
     * @param string $key unique to each snapin
     * @param string $tpl tpl holding view logic
     */
    protected function addMappingAction($key, $tpl) {
        $this->mapping['actions'][$key] = $tpl;
    }

    /**
     * let's process this mapped view
     *
     * @param string $loadassets reference var returning if we want to load assets
     * @param string $disableLayout do we want to disable layout
     * @return ZEND_VIEW
     */
    public function mapped_view(&$loadassets, &$disableLayout = null)
    {

        if (count($this->matched_mapping) > 0) {

            $args = array();
            if (method_exists($this, $this->matched_mapping['tpl'])) {
                $view = call_user_func_array(array($this, $this->matched_mapping['tpl']), $args);
                $disableLayout = $this->disableLayout;
                if (!$this->overrideTemplate) {
                    $loadassets = true;
                    return "<div class='snapin_view'>".$this->view->render($this->matched_mapping['tpl'].'.phtml')."</div>";
                } else {
                    $loadassets = false;
                    return "<div class='snapin_view'>".$view."</div>";
                }
            } else {
                $loadassets = true;
                return "<div class='snapin_view'>".$this->view->render($this->matched_mapping['tpl'].'.phtml')."</div>";
            }

        } else {
            CE_Lib::log(1, "Calling mapped view but we haven't passed any mapped data");
        }
    }

    /**
     * Sets matching variables.  Used by CE to populate the matching array
     * @internal
     */
    public function setMatching($arr)
    {
        $this->matched_mapping = $arr;
    }

    /**
     * Get setting notes
     *
     * @return string
     */
    public function getSettingsNotes()
    {
        return $this->settingsNotes;
    }

    /**
     * Return any mappings that the snapin settings view should take into account
     * @return array of setting mappings with method and button names
     */
    public function getSettingsMappings()
    {
        if (isset($this->mapping['setting'])) {
            return $this->mapping['setting'];
        } else {
            return array();
        }
    }

    /**
     * Determine if this snapin is going to be enabled by default
     *
     * @param bool $enabled
     */
    public function setEnabledByDefault($enabled)
    {
        $this->enabledByDefault = $enabled;
    }

    /**
     * Determine if this snapin is a system snapin (hide from all settings)
     * @param bool $system
     */
    public function setSystemPlugin($system)
    {
        $this->systemPlugin = $system;
    }

    /**
     * Determine if plugin is a system plugin
     * @return boolean $system
     */
    public function isSystemPlugin()
    {
        return $this->systemPlugin;
    }

    /**
     * Return if this snapin is going to be enabled by default
     *
     * @return bool
     */
    public function getEnabledByDefault()
    {
        return $this->enabledByDefault;
    }

    /**
     * Matches public mappings (publicmain and topmenu by hash)
     * @internal
     * @param  string $hash
     * @return array
     */
    public function matchViewByHash($hash) {
        $hash = base64_decode($hash);
        $hash = explode(":",$hash);
        if ( $hash[0] == 'view' ) {
            $view = $this->mapping['views'][$hash[1]];
        } else if ($hash[0] == "publicmain") {
            $view = $this->mapping['publicmain'][$hash[1]];
        } else if ($hash[0] == "topmenu") {
            $view = $this->mapping['topmenu'][$hash[1]];
        }
        return $view;
    }



    /**
     * Menu location
     *
     * @return array
     */
    public function getMenuLocation($passed_location,$admin = true)
    {

        //let's return menuLocation if set
        //otherwise let's use the Admin property to return the mapped topmenu
        if (!isset($this->mapping['topmenu'])) return array();

        foreach($this->mapping['topmenu'] as $key => $mapping) {
            if ( ($admin) && ($mapping['type'] == "admin") ) {
                if ($mapping['loc'] == $passed_location) {
                    $mapping['hash'] = base64_encode("topmenu:".$key);
                    $retArray[] = $mapping;
                }
            } else if ( (!$admin) && ($mapping['type'] == "public") ) {
                if ($mapping['loc'] == $passed_location) {
                    $mapping['hash'] = base64_encode("topmenu:".$key);
                    $retArray[] = $mapping;
                }
            }
        }

        return $retArray;
    }

    /**
     * Obtain the location this snapin permissions will be located if staff
     * has selected to not allow all of a certain type (customer, staff) to
     * use plugin features
     * @return string
     */
    public function getPermissionLocation()
    {
        return $this->permissionLocation;
    }

    /**
     * Sets the location to view permissions for this snapin.   If not set snapin permisions
     * will be grouped together with other non grouped snapins as Features (snapins) Permissions
     * @example
     * Available options: home, clients, domains, billing, support, knowledgebase, files, reports, admin
     */
    public function setPermissionLocation($permission)
    {
        $this->permissionLocation = $permission;
    }

    /**
     * Hide on Demo
     *
     * @return string
     */
    public function getHideOnDemo()
    {
        return $this->hideOnDemo;
    }

    /**
     * Get settings location
     * @internal
     * @return string keyword used to determine where settings can be placed at
     */
    public function getSettingsLocation()
    {
        return $this->settingsLocation;
    }

    /**
     * Sets the location to view settings for this snapin.   If not set snapin settings
     * will be located in Setup->Plugins->Snapins
     * @example
     * Available options clients, users, support, style, signup, security, packages, email, company, billing
     * @var string
     */
    public function setSettingLocation($setting)
    {
        $this->settingsLocation = $setting;
    }

    /**
     * We only want to show permissions if this snapin has admin / customer views
     * that the admin chose not to give access to all.  In which case we want to
     * return as a valid permissionable snapin (excuse grammer - made up a word)
     *
     * @internal
     * @param  [type] $customerPermissions [description]
     * @return [type]                      [description]
     */
    public function doWeShowPermissions($customerPermissions)
    {

        $features = $this->getFeaturesItemGrouped();

        $adminFeaturesItem = $features['admin'];
        $publicFeaturesItem = $features['public'];

        if ($customerPermissions) {
            //Viewable by all customers
           if ($publicFeaturesItem != "") {
                //only return true if we have selected not to be viewed by all
                $viewable = $this->getVariable("Viewable by all customers");
                if (!$viewable || $this->systemPlugin) return true;
            }
        } else {
            //Viewable by all staff
           if ($adminFeaturesItem != "") {
                //only return true if we have selected not to be viewed by all
                $viewable = $this->getVariable("Viewable by all staff");
                if (!$viewable || $this->systemPlugin) return true;
            }
        }

        return false;
    }

    /**
     * Returns array of feature content by admin or public
     * Can be used to check if certain snapin has features for
     * a given customer type (admin, customer)
     *
     * @internal
     * @return array
     */
    public function getFeaturesItemGrouped()
    {

        $adminFeaturesItem = "";
        //check if plugin has admin views
        $publicFeaturesItem = "";

        if (!is_array($this->mapping)) $this->mapping = array();

        foreach ($this->mapping as $type => $mapping) {
            foreach ($mapping as $key => $map) {

                switch($type) {
                    case "publicmain":
                        $publicFeaturesItem .= "<li>".$map['desc']."";
                        break;
                    case "topmenu":
                        if ($map['type'] == "admin") {
                            $adminFeaturesItem .= "<li>".$map['desc']."";
                        }
                        if ($map['type'] == "public") {
                            $publicFeaturesItem .= "<li>".$map['desc']."";
                        }
                        break;
                    case "hooks":
                        if (in_array($map['loc'], $this->arrOfAdminLocations)) {
                            $adminFeaturesItem .= "<li>".$map['desc']."";
                        }
                        if (in_array($map['loc'], $this->arrOfPublicLocations)) {
                            $publicFeaturesItem .= "<li>".$map['desc']."";
                        }
                        break;
                }

            }
        }

        return array("admin"=>$adminFeaturesItem,"public"=>$publicFeaturesItem);
    }

    /**
     * Get permission variables
     *
     * @internal
     * @access protected
     * @return array
     */
    public function getPermissionVars($isAdminOnly = false, $isPublicOnly = false)
    {
        include_once 'library/CE/NE_GroupsGateway.php';

        $groupsGateway = new NE_GroupsGateway();
        $groupsIt      = $groupsGateway->getAdminGroupsAndCustomerGroup();

        $vars = array();

        $vars['Enabled'] = array(
            'type' => 'yesno',
            'description' => 'Select Yes if you want this functionality enabled in your installation',
            'value' => $this->getEnabledByDefault()
        );

        $features = $this->getFeaturesItemGrouped();
        $adminFeaturesItem = $features['admin'];
        $publicFeaturesItem = $features['public'];

        if ($adminFeaturesItem != ""){

            $adminDescription = "<strong>".$this->user->lang("Note: Selecting no will allow you to specify permissions per")." <a href='index.php?fuse=admin&controller=staff&view=adminlist'>".$this->user->lang("staff group")."</a></strong><br/>";
            $adminFeatures = "<br><strong><font color='#f90'>".$this->user->lang("Admin Panel Features")."</font></strong><br/><ul>".$adminFeaturesItem."</ul>";

            $vars['Viewable by all staff'] = array(
                'type' => 'yesno',
                'description' => $adminDescription.$this->user->lang('Enable admin panel functionality, listed below, to all staff members').$adminFeatures,
                'value' => true
            );

        }

        if ($publicFeaturesItem != "") {

            $customerDescription = "<strong>".$this->user->lang("Note: Selecting no will allow you to specify permissions per")." <a href='index.php?fuse=admin&controller=groups&view=viewgroups'>".$this->user->lang("customer group")."</a></strong><br/>";
            $publicFeatures = "<br><strong><font color='#f90'>".$this->user->lang("Public Panel Features")."</font></strong><br/><ul>".$publicFeaturesItem."</ul>";

            $vars['Viewable by all customers'] = array(
                'type' => 'yesno',
                'description' => $customerDescription.$this->user->lang('Determine if you wish all customers to have this functionality').$publicFeatures,
                'value' => true
            );

        }

        return $vars;
    }

   /**
    * Process a language request
    *
    * @return string Translation of text passed to function
    * @access protected
    */
    protected function varLang()
    {
        $args = func_get_args();
        return call_user_func_array(array($this, 'lang'), $args);
    }

   /**
    * Process a language request
    *
    * @param string $phrase Language phrase to process
    * @param string $args   Arguments to pass to the laguage module
    *
    * @return string Translation of text passed to function
    * @access protected
    */
    public function lang($phrase, $args = false)
    {
        $args = func_get_args();
        array_shift($args);

        return $this->user->langModule($phrase, 'admin', $args);
    }

     /**
     * Wrapper to http objects getParam param
     * Retrieve a member of the $_REQUEST superglobal.  If filter is passed
     * and fails validation getParam will return the default value
     *
     * If no $key is passed, returns the entire $_REQUEST array.
     *
     * @param string $key
     * @param mixed  $filter PHP filter constants to be used on var
     * @param mixed  $default Default value to use if key not found
     * @param bool   $raiseExceptionOnNull raises exception if any value returns null
     * @param array  $filter_options array of additional options to be passed with the filter paramter
     * @return mixed Returns null if key does not exist
     */
    public function getParam($key = null, $filter = null, $default = null, $raiseExceptionOnNull = true, $filterOptions = null) {

        $request = Zend_Controller_Front::getInstance()->getRequest();


        if ($key === null) { return $request->getParams(); }

        $data = $request->getParam($key, $default);

        if ($filter != null && $data != null) {
            $data = $this->checkParam($data, $filter, $default, $raiseExceptionOnNull, $filterOptions, $key);
        } else if ($data === null && $raiseExceptionOnNull) {
            $errorMessage = "Required variable: " . $key . " was not passed";
            throw new Exception($errorMessage);
            CE_Lib::log(3, $errorMessage);
        }

        return $data;

    }

    /**
     * Function to check variables against filters. If filter is passed and
     * fails validation, will return default value.
     *
     * @param mixed   $var the supplied value
     * @param integer $filter the php filter constant to apply to the variable
     * @param mixed   $default the value to to return if the supplied variable fails validation
     * @param boolean $raise_exception_on_null whether or not to throw an exception if the resulting value is null (either through default or received value)
     * @param array   $filter_options an array of additional filter options & flags to apply with the filter
     * @return mixed  Returns value
     */
    protected function checkParam($data, $filter, $default = null, $raiseExceptionOnNull = true, $filterOptions = null, $keyName = null) {

            if ($filterOptions === null) {
                $data = filter_var($data, $filter);
            } else {
                array_unshift($filterOptions, $data, $filter);
                $data = call_user_func_array('filter_var', $filterOptions);
            }
            if ((($filter != FILTER_VALIDATE_BOOLEAN && $data === false) || ($filter == FILTER_VALIDATE_BOOLEAN && $data === null)) && $default === null && $raiseExceptionOnNull) {
                if ($keyName != null) {
                    $errorMessage = 'Requested variable "' . $keyName . '" failed validation';
                } else {
                    $errorMessage = 'Checked variable failed validation';
                }
                throw new Exception($errorMessage);
                CE_Lib::log(3, $errorMessage);
            } else if ($data === false) {
                $data = $default;
            }

        return $data;

    }
}