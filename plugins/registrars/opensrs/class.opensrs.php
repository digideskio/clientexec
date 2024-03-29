<?php
/**
 * OpenSRS Domain Registrar Plugin Communication Class
 *
 * Expanded from the orginal class written by Mike Mallinson
 *
 * @category Plugin
 * @package  ClientExec
 * @author   Jason Yates <jason@clientexec.com>
 * @license  ClientExec License
 * @version  2
 * @link     http://www.clientexec.com
 */

require_once dirname(__FILE__).'/../../../library/CE/XmlFunctions.php';

/**
 * OpenSRS Class
 *
 * @category Plugin
 * @package  ClientExec
 * @author   Jason Yates <jason@clientexec.com>
 * @license  ClientExec License
 * @version  2
 * @link     http://www.clientexec.com
 */
class OpenSRS
{
    var $apiVersion = '0.9';

    var $port;
    var $host;
    var $username;
    var $key;

    /**
     * Constructs a new OpenSRS object.
     *
     * @return OpenSRS An OpenSRS object
     */
    function OpenSRS($host, $port, $username, $key, $user)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->key = $key;
        $this->user = $user;
    }

    /**
     * Checks the availibility of a domain.
     *
     * UPDATED: To use name_suggest as OpenSRS say it is faster
     *
     * @param string $domain The domain name to lookup
     * @return an xmlized array result - use print_r() to view the structure
     */
    function lookup_domain($domain, $tld, $namesuggest)
    {

        // Build the namesuggest items
        $i = 1;
        $namesuggestXml = "";
        foreach($namesuggest AS $key) {
            $namesuggestXml .= "<item key='".$i."'>".$key."</item>";
            ++$i;
        }

        // Build the request
        $request = "<data_block>
                      <dt_assoc>
                        <item key='protocol'>XCP</item>
                        <item key='action'>name_suggest</item>
                        <item key='object'>domain</item>
                        <item key='attributes'>
                          <dt_assoc>
                            <item key='services'>
                                <dt_array>
                                    <item key='0'>lookup</item>
                                </dt_array>
                            </item>
                            <item key='searchstring'>".$domain."</item>
                            <item key='tlds'>
                              <dt_array>
                                <item key='0'>".$tld."</item>
                                ".$namesuggestXml."
                              </dt_array>
                            </item>
                          </dt_assoc>
                        </item>
                        <item key='lookup'>
                          <dt_assoc>
                             <item key='no_cache_tlds'>1</item>
                          </dt_assoc>
                        </item>
                      </dt_assoc>
                    </data_block>";

        // Send the request to OpenSRS
        $response = $this->_buildAndSendRequest($request);

        // Check the response
        if ($response == null) {
            return null;
        }

        // XMLize the reply
        $response = XmlFunctions::xmlize($response);

        $int = 0;
        foreach ( $response['OPS_envelope']['#']['body'][0]['#']['data_block'][0]['#']['dt_assoc'][0]['#']['item'] as $key => $item ) {
          if ( $item['@']['key'] == 'attributes' ) {
              $int = $key;
          }
        }
        if ( $int == 0 ) {
          return null;
        }

        // Get the response
        $response = @$response['OPS_envelope']['#']['body'][0]['#']['data_block'][0]['#']['dt_assoc'][0]['#']['item'][$int]['#']['dt_assoc'][0]['#']['item'][0]['#']['dt_assoc'][0]['#']['item'];

        // Check the response again, due to some possible auth failures
        if ($response == null) {
            return null;
        }

        $finalArray = array();
        $finalArray['status'] = array("response_text" => $response[1]['#'], "response_code" => $response[2]['#']);

        // Loop it
        foreach ($response[3]['#']['dt_array'][0]['#']['item'] AS $key) {

            // Make a working array
            $workingArray = array();
            $key = $key['#']['dt_assoc'][0]['#']['item'];

            // Add the domain
            $workingArray['domain'] = $key[0]['#'];

            // Explode the domain into parts
            $domainExplode = @explode(".", $workingArray['domain']);
            if(@$domainExplode[2]) {
                $workingArray['tld'] = $domainExplode[1].".".$domainExplode[2];
            } else {
                $workingArray['tld'] = $domainExplode[1];
            }

            $workingArray['sld'] = $domainExplode[0];

            // Validate the SLD because oPenSRS returns duplicate domains with -'s taken out.
            if($workingArray['sld'] != $domain) {
                continue;
            }

            // Re-work the status
            if($key[1]['#'] == 'available') {
                $domainStatus = 0;
            } else {
                $domainStatus = 1;
            }

            $workingArray['status'] = $domainStatus;

            $finalArray['result'][] = $workingArray;
        }
        //return the final array of checked domains
        return $finalArray;
    }

    /**
     * Renew a domain name with OpenSRS
     *
     * @param array $params An array of parameters.
     * @return an xmlized array result - use print_r() to view the structure
     */
    function renew_domain($params)
    {

        $attributes = "<dt_assoc>
                        <item key='auto_renew'>".$params['renewname']."</item>
                        <item key='handle'>process</item>
                        <item key='domain'>".$params['domain']."</item>
                        <item key='currentexpirationyear'>".$params['expirationyear']."</item>
                        <item key='period'>" . $params['NumYears'] ."</item>
                    </dt_assoc>";

        $request = "<data_block>
                     <dt_assoc>
                      <item key='action'>RENEW</item>
                      <item key='object'>DOMAIN</item>
                      <item key='protocol'>XCP</item>
                      <item key='attributes'>
                       ".$attributes."
                      </item>
                     </dt_assoc>
                    </data_block>";

        $response = $this->_buildAndSendRequest($request);
        if ($response == null) {
            return null;
        }
        $response = XmlFunctions::xmlize($response);
        return $response['OPS_envelope']['#']['body'][0]['#']['data_block'][0]['#']['dt_assoc'][0]['#']['item'];
    }


    function check_transfer_status($params)
    {
        $request = "<data_block>
         <dt_assoc>
            <item key='protocol'>XCP</item>
            <item key='action'>CHECK_TRANSFER</item>
            <item key='object'>DOMAIN</item>
            <item key='attributes'>
               <dt_assoc>
                  <item key='domain'>{$params['domain']}</item>
                  <item key='check_status'>1</item>
                  <item key='get_request_address'>0</item>
               </dt_assoc>
            </item>
         </dt_assoc>
      </data_block>";

        $response = $this->_buildAndSendRequest($request);
        if ($response == null) {
            return null;
        }
        $response = XmlFunctions::xmlize($response);

        return $response['OPS_envelope']['#']['body'][0]['#']['data_block'][0]['#']['dt_assoc'][0]['#']['item'];
    }

    function initiate_transfer($params)
    {
        $contactblock = "  <dt_assoc>
                            <item key='state'>".$params['RegistrantStateProvince']."</item>
                            <item key='first_name'>".$params['RegistrantFirstName']."</item>
                            <item key='country'>".$params['RegistrantCountry']."</item>
                            <item key='address1'>".$params['RegistrantAddress1']."</item>
                            <item key='last_name'>".$params['RegistrantLastName']."</item>
                            <item key='address2'></item>
                            <item key='address3'></item>
                            <item key='postal_code'>".$params['RegistrantPostalCode']."</item>
                            <item key='fax'></item>
                            <item key='city'>".$params['RegistrantCity']."</item>
                            <item key='phone'>".$params['RegistrantPhone']."</item>
                            <item key='email'>".$params['RegistrantEmailAddress']."</item>
                            <item key='org_name'>".htmlentities($params['RegistrantOrganizationName'])."</item>
                            <item key='lang_pref'>".$params['RegistrantLanguage']."</item>
                           </dt_assoc>";


        $request = "
            <data_block>
                <dt_assoc>
                    <item key='protocol'>XCP</item>
                    <item key='action'>SW_REGISTER</item>
                    <item key='object'>DOMAIN</item>
                    <item key='attributes'>
                       <dt_assoc>
                          <item key='auto_renew'/>
                        <item key='contact_set'>
                         <dt_assoc>
                          <item key='admin'>
                           ".$contactblock."
                          </item>
                          <item key='billing'>
                           ".$contactblock."
                          </item>
                          <item key='owner'>
                           ".$contactblock."
                          </item>
                          <item key='tech'>
                           ".$contactblock."
                          </item>
                         </dt_assoc>
                        </item>

                          <item key='link_domains'>0</item>
                          <item key='f_parkp'>N</item>
                          <item key='custom_tech_contact'>0</item>
                          <item key='reg_domain'>{$params['domain']}</item>
                          <item key='domain'>{$params['domain']}</item>
                          <item key='period'>1</item>
                          <item key='reg_type'>transfer</item>
                          <item key='reg_username'>{$params['DomainUsername']}</item>
                          <item key='reg_password'>{$params['DomainPassword']}</item>
                          <item key='encoding_type'/>
                          <item key='custom_transfer_nameservers'>0</item>
                       </dt_assoc>
                    </item>
                 </dt_assoc>
              </data_block>";

        $response = $this->_buildAndSendRequest($request);
        if ($response == null) {
            return null;
        }
        $response = XmlFunctions::xmlize($response);
        return $response['OPS_envelope']['#']['body'][0]['#']['data_block'][0]['#']['dt_assoc'][0]['#']['item'];

    }

    function enable_whois_privacy($params)
    {
        $request = "
         <data_block>
             <dt_assoc>
                <item key='protocol'>XCP</item>
                <item key='action'>SW_REGISTER</item>
                <item key='object'>DOMAIN</item>
                <item key='attributes'>
                   <dt_assoc>
                      <item key='reg_type'>whois_privacy</item>
                      <item key='reg_password'>{$params['DomainPassword']}</item>
                      <item key='domain'>{$params['domain']}</item>
                      <item key='reg_username'>{$params['DomainUsername']}</item>
                   </dt_assoc>
                </item>
             </dt_assoc>
        </data_block>";

        $response = $this->_buildAndSendRequest($request);
        if ($response == null) {
            return null;
        }
        $response = XmlFunctions::xmlize($response);
        return $response['OPS_envelope']['#']['body'][0]['#']['data_block'][0]['#']['dt_assoc'][0]['#']['item'];
    }

    /**
     * Registers a domain name with OpenSRS.
     *
     * @param array $params An array of parameters.
     * @return an xmlized array result - use print_r() to view the structure
     */
    function register_domain($params)
    {
        $contactblock = "  <dt_assoc>
                            <item key='state'>".$params['RegistrantStateProvince']."</item>
                            <item key='first_name'>".$params['RegistrantFirstName']."</item>
                            <item key='country'>".$params['RegistrantCountry']."</item>
                            <item key='address1'>".$params['RegistrantAddress1']."</item>
                            <item key='last_name'>".$params['RegistrantLastName']."</item>
                            <item key='address2'></item>
                            <item key='address3'></item>
                            <item key='postal_code'>".$params['RegistrantPostalCode']."</item>
                            <item key='fax'></item>
                            <item key='city'>".$params['RegistrantCity']."</item>
                            <item key='phone'>".$params['RegistrantPhone']."</item>
                            <item key='email'>".$params['RegistrantEmailAddress']."</item>
                            <item key='org_name'>".htmlentities($params['RegistrantOrganizationName'])."</item>
                            <item key='lang_pref'>".$params['RegistrantLanguage']."</item>
                           </dt_assoc>";

        /* tld specific items */
        switch($params['tld']) {
            case 'ca':
                $tldspecific = "<item key='isa_trademark'>".$params['ExtendedAttributes']['cira-isa-trademark']."</item>
                 <item key='legal_type'>".$params['ExtendedAttributes']['cira_legal_type']."</item>";
                break;
            case 'us':
                $tldspecific = "<item key='tld_data'>
                        <dt_assoc>
                          <item key='nexus'>
                           <dt_assoc>
                            <item key='category'>".$params['ExtendedAttributes']['us_nexus']."</item>
                            <item key='app_purpose'>".$params['ExtendedAttributes']['us_purpose']."</item>
                            <item key='validator'>".$params['RegistrantCountry']."</item>
                           </dt_assoc>
                          </item>
                         </dt_assoc>
                        </item>";
                break;
            case 'name':
                $tldspecific = "<item key='tld_data'>
                      <dt_assoc>
                       <item key='forwarding_email'>".$params['RegistrantEmailAddress']."</item>
                      </dt_assoc>
                     </item>";
                break;
            default:
                $tldspecific = "";
                break;
        }

        /* custom name servers */
        $ns = "";
        if ($params['Custom NS']) {
            $ns = "<item key='nameserver_list'>
                    <dt_array>";
            for ($i = 1; $i <= 12; $i++) {
                if (isset($params["NS$i"])) {
                    $ns.= "<item key='".($i-1)."'>
                          <dt_assoc>
                           <item key='sortorder'>".$i."</item>
                           <item key='name'>".$params['NS'.$i]['hostname']."</item>
                          </dt_assoc>
                         </item>";
                } else {
                    break;
                }
            }
            $ns .= " </dt_array>
                    </item>";
        }

        $request = "<data_block>
                     <dt_assoc>
                      <item key='action'>SW_REGISTER</item>
                      <item key='object'>DOMAIN</item>
                      <item key='protocol'>XCP</item>
                      <item key='attributes'>
                       <dt_assoc>
                        <item key='auto_renew'>".$params['renewname']."</item>
                        <item key='contact_set'>
                         <dt_assoc>
                          <item key='admin'>
                           ".$contactblock."
                          </item>
                          <item key='billing'>
                           ".$contactblock."
                          </item>
                          <item key='owner'>
                           ".$contactblock."
                          </item>
                          <item key='tech'>
                           ".$contactblock."
                          </item>
                         </dt_assoc>
                        </item>
                        <item key='custom_nameservers'>".$params['Custom NS']."</item>
                        <item key='custom_tech_contact'>1</item>
                        <item key='domain'>".$params['domain']."</item>
                        <item key='f_lock_domain'>1</item>
                        <item key='handle'>process</item>
                        ".$tldspecific."
                        ".$ns."
                        <item key='period'>".$params['NumYears']."</item>
                        <item key='reg_username'>".$params['DomainUsername']."</item>
                        <item key='reg_password'>".$params['DomainPassword']."</item>
                        <item key='type'>new</item>
                       </dt_assoc>
                      </item>
                     </dt_assoc>
                    </data_block>";

        $response = $this->_buildAndSendRequest($request);
        if ($response == null) {
            return null;
        }
        $response = XmlFunctions::xmlize($response);
        return $response['OPS_envelope']['#']['body'][0]['#']['data_block'][0]['#']['dt_assoc'][0]['#']['item'];
    }


    /**
     * Sets the renew status of a domain
     *
     * @param string $domain The domain name to lookup
     * @return an xmlized array result - use print_r() to view the structure
     */
    function set_autorenew($domain, $tld, $autorenew, $cookie = null)
    {

        // Fix to stop null $autorenew values
        if($autorenew == null) {
            $autorenew = 0;
        }

        // Build the request
        $request = "<data_block>
                      <dt_assoc>
                        <item key='protocol'>XCP</item>
                        <item key='action'>modify</item>
                        <item key='object'>domain</item>
                        <item key='domain'>".$domain.".".$tld."</item>
                        <item key='attributes'>
                          <dt_assoc>
                            <item key='affect_domains'>0</item>
                            <item key='auto_renew'>".$autorenew."</item>
                            <item key='let_expire'>0</item>
                            <item key='data'>expire_action</item>
                          </dt_assoc>
                        </item>
                      </dt_assoc>
                    </data_block>";

        // Send the request to OpenSRS
        $response = $this->_buildAndSendRequest($request);

        // Check the response
        if ($response == null) {
            return null;
        }

        // XMLize the reply
        $response = XmlFunctions::xmlize($response);

        // Get the response
        $response = $response['OPS_envelope']['#']['body'][0]['#']['data_block'][0]['#']['dt_assoc'][0]['#']['item'];

        // Process the response
        $finalArray = array();
        if(isset($response[4]['#'])) {
            // Success
            $finalArray['status'] = array("response_text" => $response[1]['#'], "response_code" => $response[4]['#']);
        } else {
            // Probably failed
            $finalArray['status'] = array("response_text" => $response[1]['#'], "response_code" => $response[2]['#']);
        }

        return $finalArray;
    }


    function get_lock($domain, $tld)
    {
        // Build the request
        $request = "<data_block>
                      <dt_assoc>
                        <item key='protocol'>XCP</item>
                        <item key='action'>get</item>
                        <item key='object'>domain</item>
                        <item key='domain'>$domain.$tld</item>
                        <item key='attributes'>
                          <dt_assoc>
                            <item key='domain_name'>$domain.$tld</item>
                            <item key='type'>status</item>
                            <item key='limit'>10</item>>
                          </dt_assoc>
                        </item>
                      </dt_assoc>
                    </data_block>";

        // Send the request to OpenSRS
        $response = $this->_buildAndSendRequest($request);



        // Check the response
        if ($response == null) {
            return null;
        }

        // XMLize the reply
        $response = XmlFunctions::xmlize($response);

        // Get the first part of the response
        $response = $response['OPS_envelope']['#']['body'][0]['#']['data_block'][0]['#']['dt_assoc'][0]['#']['item'];

        return $response;
    }

    function set_lock($domain, $tld, $lock)
    {
        // Build the request
        $request = "<data_block>
                      <dt_assoc>
                        <item key='protocol'>XCP</item>
                        <item key='action'>modify</item>
                        <item key='object'>domain</item>
                        <item key='attributes'>
                          <dt_assoc>
                            <item key='affect_domains'>0</item>
                            <item key='lock_state'>".$lock."</item>
                            <item key='data'>status</item>
                            <item key='domain_name'>".$domain.".".$tld."</item>
                          </dt_assoc>
                        </item>
                      </dt_assoc>
                    </data_block>";

        // Send the request to OpenSRS
        $response = $this->_buildAndSendRequest($request);



        // Check the response
        if ($response == null) {
            return null;
        }

        // XMLize the reply
        $response = XmlFunctions::xmlize($response);

        // Get the first part of the response
        $response = $response['OPS_envelope']['#']['body'][0]['#']['data_block'][0]['#']['dt_assoc'][0]['#']['item'];

        return $response;
    }

    /**
     * Sets the renew status of a domain
     *
     * @param string $domain The domain name to lookup
     * @return an xmlized array result - use print_r() to view the structure
     */
    function get_domain_info($domain, $tld, $type)
    {
        // Build the request
        $request = "<data_block>
                      <dt_assoc>
                        <item key='protocol'>XCP</item>
                        <item key='action'>get</item>
                        <item key='object'>domain</item>
                        <item key='domain'>$domain.$tld</item>
                        <item key='attributes'>
                          <dt_assoc>
                            <item key='type'>all_info</item>
                            <item key='limit'>10</item>>
                          </dt_assoc>
                        </item>
                      </dt_assoc>
                    </data_block>";

        // Send the request to OpenSRS
        $response = $this->_buildAndSendRequest($request);



        // Check the response
        if ($response == null) {
            return null;
        }

        // XMLize the reply
        $response = XmlFunctions::xmlize($response);

        // Get the first part of the response
        $response = $response['OPS_envelope']['#']['body'][0]['#']['data_block'][0]['#']['dt_assoc'][0]['#']['item'];
        // Process the response
        $finalArray = array();
        $finalArray['status'] = array("response_text" => $response[2]['#'], "response_code" => $response[5]['#']);

        if ( $response[4]['#'] == 'Authentication Error.' ) {
            // This function is called and sometime causes errors when viewing a domain, but we do not want to throw an exception each time.
            CE_Lib::log(4, "Authentication Error with OpenSRS.", EXCEPTION_CODE_CONNECTION_ISSUE);
            return null;
        }

        if ( $type == 'nameserver' ) {
            $nameservers = array();
            $response = $response[4]['#']['dt_assoc'][0]['#']['item'];

            foreach ( $response as $key => $value ) {
                if ( $value['@']['key'] == 'nameserver_list') {
                    $nameServerId = $key;
                }
            }

            foreach ( $response[$nameServerId]['#']['dt_array'][0]['#']['item'] as $item ) {
                $nameservers[] = $item['#']['dt_assoc'][0]['#']['item'][0]['#'];
            }
            return $nameservers;

        // Determine what we are outputting
        } else if($type == 'general') {

            // Set the next block of the response
            $response = $response[4]['#']['dt_assoc'][0]['#']['item'];

             foreach ( $response as $key => $value ) {
                CE_Lib::log(4, $value['@']['key']);
                if ( $value['@']['key'] == 'auto_renew') {
                    $autoRenewId = $key;
                }
                if ( $value['@']['key'] == 'registry_createdate') {
                    $createDateId = $key;
                }
                if ( $value['@']['key'] == 'registry_expiredate') {
                    $expireDateId = $key;
                }
            }

            // Process the standard info
            $finalArray['generalInfo'] = array('auto_renew' => $response[$autoRenewId]['#'],
                                               'registry_createdate' => $response[$createDateId]['#'],
                                               'registry_expiredate' => $response[$expireDateId]['#']);

        } elseif($type == 'contact') {

            // Set the next block of the response
            $response = $response[4]['#']['dt_assoc'][0]['#']['item'][1]['#']['dt_assoc'][0]['#']['item'];

            $i = 0;
            $info = array();
            foreach (array('Admin', 'Registrant', 'Tech', 'Billing') as $type) {

                $data =  $response[$i]['#']['dt_assoc'][0]['#']['item'];
                if (is_array($data)) {
                    $info[$type]['OrganizationName']  = array('Organization', $data[2]['#']);
                    $info[$type]['JobTitle']  = array('Job Title', '');
                    $info[$type]['FirstName'] = array('First Name', $data[12]['#']);
                    $info[$type]['LastName']  = array('Last Name', $data[5]['#']);
                    $info[$type]['Address1']  = array('Address 1', $data[11]['#']);
                    $info[$type]['Address2']  = array('Address 2', $data[6]['#']);
                    $info[$type]['City']      = array('City', $data[8]['#']);
                    $info[$type]['StateProvChoice']  = array('State or Province', '');
                    $info[$type]['StateProv']  = array('Province / State', $data[4]['#']);
                    $info[$type]['Country']   = array('Country', $data[0]['#']);
                    $info[$type]['PostalCode']  = array('Postal Code', $data[9]['#']);
                    $info[$type]['EmailAddress']     = array('E-mail', $data[7]['#']);
                    $info[$type]['Phone']  = array('Phone', $data[3]['#']);
                    $info[$type]['PhoneExt']  = array('Phone Ext', '');
                    $info[$type]['Fax']       = array('Fax', $data[10]['#']);
                } else {
                    $info[$type] = array(
                        'OrganizationName'  => array($this->user->lang('Organization'), ''),
                        'JobTitle'          => array($this->user->lang('Job Title'), ''),
                        'FirstName'         => array($this->user->lang('First Name'), ''),
                        'LastName'          => array($this->user->lang('Last Name'), ''),
                        'Address1'          => array($this->user->lang('Address').' 1', ''),
                        'Address2'          => array($this->user->lang('Address').' 2', ''),
                        'City'              => array($this->user->lang('City'), ''),
                        'StateProvChoice'   => array($this->user->lang('State or Province'), ''),
                        'StateProv'         => array($this->user->lang('Province').'/'.$this->user->lang('State'), ''),
                        'Country'           => array($this->user->lang('Country'), ''),
                        'PostalCode'        => array($this->user->lang('Postal Code'), ''),
                        'EmailAddress'      => array($this->user->lang('E-mail'), ''),
                        'Phone'             => array($this->user->lang('Phone'), ''),
                        'PhoneExt'          => array($this->user->lang('Phone Ext'), ''),
                        'Fax'               => array($this->user->lang('Fax'), ''),
                    );
                }


                ++$i;
            }

            // Have to return here as the classes dont support status
            return $info;
        }

        return $finalArray;
    }


    /**
     * Get a list of domains from OpenSRS
     *
     * @param string $domain The domain name to lookup
     * @return an xmlized array result - use print_r() to view the structure
     */
    function get_domains_list($page)
    {

        $previousYear = date("Y")-1;
        $nextYears = date("Y")+11;
        $limit = 100;

        // Build the request
        $request = "<data_block>
                      <dt_assoc>
                        <item key='protocol'>XCP</item>
                        <item key='action'>get_domains_by_expiredate</item>
                        <item key='object'>domain</item>
                        <item key='attributes'>
                          <dt_assoc>
                            <item key='limit'>$limit</item>
                            <item key='exp_from'>".$previousYear."-01-01</item>
                            <item key='exp_to'>".$nextYears."-01-01</item>
                            <item key='page'>".$page."</item>
                          </dt_assoc>
                        </item>
                      </dt_assoc>
                    </data_block>";

        // Send the request to OpenSRS
        $response = $this->_buildAndSendRequest($request);

        // Check the response
        if ($response == null) {
            return null;
        }

        // XMLize the reply
        $response = XmlFunctions::xmlize($response);

        // Get the first part of the response
        $response = @$response['OPS_envelope']['#']['body'][0]['#']['data_block'][0]['#']['dt_assoc'][0]['#']['item'];

        $domainsList = array();
        if (@$response[4]['#']['dt_assoc'][0]['#']['item'][3]['#'] > 0) {
            $i = 0;
            foreach ($response[4]['#']['dt_assoc'][0]['#']['item'][0]['#']['dt_array'][0]['#']['item'] as $domain) {
                $domain = $domain['#']['dt_assoc'][0]['#']['item'];

                $splitDomain = $this->splitDomain($domain[1]['#']);

                $data['id'] = ++$i;
                $data['sld'] = $splitDomain[0];
                $data['tld'] = $splitDomain[1];
                $data['exp'] = $domain[2]['#'];
                $domainsList[] = $data;
            }
        }

        $metaData = array();
        $metaData['total'] = @$response[4]['#']['dt_assoc'][0]['#']['item'][3]['#'];
        $metaData['start'] = ($page * $limit) - $limit;
        $metaData['end'] = $page * $limit;
        $metaData['next'] = ++$page;
        $metaData['numPerPage'] = $limit;

        return array($domainsList, $metaData);
    }

    /**
     * Sets the renew status of a domain
     *
     * @param string $domain The domain name to lookup
     * @return an xmlized array result - use print_r() to view the structure
     */

    function get_cookie($params) {

        // Build the request
        $request = "<data_block>
                      <dt_assoc>
                        <item key='protocol'>XCP</item>
                        <item key='action'>set</item>
                        <item key='object'>cookie</item>
                        <item key='attributes'>
                          <dt_assoc>
                            <item key='reg_username'>".$params['domainUsername']."</item>
                            <item key='reg_password'>".$params['domainPassword']."</item>
                            <item key='domain'>".$params['sld'].".".$params['tld']."</item>
                          </dt_assoc>
                        </item>
                      </dt_assoc>
                    </data_block>";

        // Send the request to OpenSRS
        $response = $this->_buildAndSendRequest($request);

        // Check the response
        if ($response == null) {
            throw new Exception("OpenSRS API Error: Unable to communicate with OpenSRS.");
        }

        // XMLize the reply
        $response = XmlFunctions::xmlize($response);

        // Get the response
        $response = $response['OPS_envelope']['#']['body'][0]['#']['data_block'][0]['#']['dt_assoc'][0]['#']['item'];

        // Check the response code
        if(@$response[4]['#'] == '415') {

            // Throw error
            throw new Exception("OpenSRS API Error: Unable to authenticate using the Domain Username & Password.");
        }

        // Get the cookie
        $response = $response[4]['#']['dt_assoc'][0]['#']['item'][1]['#'];

        // return it
        return $response;
    }

    function set_nameservers($params)
    {
        // get our new name servers to add to the domain
        $nameServerKeys = "";
        foreach ( $params['ns'] as $key => $nameServer ) {
            $nameServerKeys .= "<item key='" . ($key-1) . "'>$nameServer</item>\n";
        }

        $request = "
        <data_block>
            <dt_assoc>
                <item key='protocol'>XCP</item>
                <item key='action'>advanced_update_nameservers</item>
                <item key='object'>domain</item>
                <item key='domain'>{$params['sld']}.{$params['tld']}</item>
                <item key='attributes'>
                    <dt_assoc>
                        <item key='assign_ns'>
                            <dt_array>
                                $nameServerKeys
                            </dt_array>
                        </item>
                        <item key='op_type'>assign</item>
                    </dt_assoc>
                </item>
                </dt_assoc>
                </item>
            </dt_assoc>
        </data_block>";


        $response = $this->_buildAndSendRequest($request);

        // Check the response
        if ($response == null) {
            throw new Exception("OpenSRS API Error: Unable to communicate with OpenSRS.");
        }

        // XMLize the reply
        $response = XmlFunctions::xmlize($response);

        // Get the response
        $response = $response['OPS_envelope']['#']['body'][0]['#']['data_block'][0]['#']['dt_assoc'][0]['#']['item'];

        return $response;

    }

    function update_contact($params)
    {
         $contactblock = "<dt_assoc>
                            <item key='state'>".$params['Registrant_StateProv']."</item>
                            <item key='first_name'>".$params['Registrant_FirstName']."</item>
                            <item key='country'>".$params['Registrant_Country']."</item>
                            <item key='address1'>".$params['Registrant_Address1']."</item>
                            <item key='last_name'>".$params['Registrant_LastName']."</item>
                            <item key='address2'></item>
                            <item key='address3'></item>
                            <item key='postal_code'>".$params['Registrant_PostalCode']."</item>
                            <item key='fax'></item>
                            <item key='city'>".$params['Registrant_City']."</item>
                            <item key='phone'>".$params['Registrant_Phone']."</item>
                            <item key='email'>".$params['Registrant_EmailAddress']."</item>
                            <item key='org_name'>".htmlentities($params['Registrant_OrganizationName'])."</item>
                           </dt_assoc>";

        $request = "<data_block>
                      <dt_assoc>
                        <item key='protocol'>XCP</item>
                        <item key='action'>UPDATE_CONTACTS</item>
                        <item key='attributes'>
                          <dt_assoc>
                            <item key='domain'>{$params['sld']}.{$params['tld']}</item>
                            <item key='types'>
                                <dt_array>
                                     <item key='0'>admin</item>
                                     <item key='1'>billing</item>
                                     <item key='2'>owner</item>
                                     <item key='3'>tech</item>
                              </dt_array>
                            </item>
                            <item key='contact_set'>
                                <dt_assoc>
                                    <item key='admin'>
                                        ".$contactblock."
                                    </item>
                                    <item key='billing'>
                                        ".$contactblock."
                                    </item>
                                    <item key='owner'>
                                        ".$contactblock."
                                    </item>
                                    <item key='tech'>
                                        ".$contactblock."
                                    </item>
                                </dt_assoc>
                            </item>
                          </dt_assoc>
                        </item>
                      </dt_assoc>
                    </data_block>";


        $response = $this->_buildAndSendRequest($request);

        // Check the response
        if ($response == null) {
            throw new Exception("OpenSRS API Error: Unable to communicate with OpenSRS.");
        }

        // XMLize the reply
        $response = XmlFunctions::xmlize($response);
        $response = $response['OPS_envelope']['#']['body'][0]['#']['data_block'][0]['#']['dt_assoc'][0]['#']['item'];
        return $response;
    }


    /**
     * Private function used for building the xml request and sending it.
     *
     * @param String $request The xml request body
     * @return The xml string response
     */
    function _buildAndSendRequest($request)
    {
        // Make the start of the XML request
        $request = '<?xml version=\'1.0\' encoding="UTF-8" standalone="no" ?>
                    <!DOCTYPE OPS_envelope SYSTEM "ops.dtd">
                    <OPS_envelope>
                     <header>
                      <version>'.$this->apiVersion.'</version>
                     </header>
                     <body>
                       '.$request.'
                     </body>
                    </OPS_envelope>';

        // Generate the signature
        $signature = md5(md5($request.$this->key).$this->key);

        // Contruct the headers
        $header = "POST ".$this->host." HTTP/1.0\r\n";
        $header .= "Content-Type: text/xml\r\n";
        $header .= "X-Username: " . $this->username . "\r\n";
        $header .= "X-Signature: " . $signature . "\r\n";
        $header .= "Content-Length: " . strlen($request) . "\r\n\r\n";

        // Open the connection
        $fp = @fsockopen ("ssl://$this->host", $this->port, $errno, $errstr, 30);
        if (!$fp) {
            CE_Lib::log(4, "Couldn't connect to OpenSRS: $errno, $errstr");
            throw new Exception("OpenSRS API Error: Unable to communicate with OpenSRS. Please check your settings.", EXCEPTION_CODE_CONNECTION_ISSUE);
        }

        // Send the request
        fputs ($fp, $header . $request);
        $response = "";

        // Gather the reply
        while (!feof($fp))
        {
            $response .= fgets ($fp, 1024);
        }

        // Close the file
        fclose($fp);

        // Log the reply
        CE_Lib::log(4, "OpenSRS response: " . $response);

        // drop the headers so we can xmlize it
        $arrResponse = explode("\n", $response);
        $response = "";
        $flag = false;
        foreach ($arrResponse as $line)
        {
            if ($flag) $response .= $line."\n";
            else if (trim($line) == "") $flag = true;
        }

        // Return the final reply
        return $response;
    }

    function splitDomain($domain)
    {
        if (($position = strpos($domain, '.')) === false) {
            return array($domain, '');
        }
        return array(mb_substr($domain, 0, $position), mb_substr($domain, $position + 1));
    }
}

?>
