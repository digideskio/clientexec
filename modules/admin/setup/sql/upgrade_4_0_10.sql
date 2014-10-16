UPDATE `help` SET `detail` = /*T*/'[<font class=bodyhighlight>COMPANYNAME</font>] <br> Company name<br>[<font class=bodyhighlight>COMPANYADDRESS</font>] <br> Company address<br>[<font class=bodyhighlight>ACCOUNTINFORMATION</font>]<br> Includes Domain Name, Username, Password, IP<br>[<font class=bodyhighlight>DOMAINNAME</font>]<br> domain name without http://www.<br>[<font class=bodyhighlight>DOMAINUSERNAME</font>]<br> Domain User Name<br>[<font class=bodyhighlight>DOMAINPASSWORD</font>]<br> Domain Password<br>[<font class=bodyhighlight>DOMAINIP</font>]<br> IP Address to Domain<br>[<font class=bodyhighlight>COMPANYURL</font>]<br> URL to your web site<br>[<font class=bodyhighlight>SUPPORTEMAIL</font>]<br> E-mail to support staff<br>[<font class=bodyhighlight>CLIENTAPPLICATIONURL</font>]<br> URL to ClientExec.<br>[<font class=bodyhighlight>FORGOTPASSWORDURL</font>] URL to retrieve forgotten password.<br>[<font class=bodyhighlight>CLIENTNAME</font>]<br>Both first and last name<br>[<font class=bodyhighlight>FIRSTNAME</font>]<br>First name<br>[<font class=bodyhighlight>CLIENTEMAIL</font>]<br>[<font class=bodyhighlight>ORGANIZATION</font>] <br> Client\'s Organization<br>[<font class=bodyhighlight>PLANNAME</font>] <br> Client\'s Plan<br>[<font class=bodyhighlight>NAMESERVERS</font>]<br>lists only hostnames<br>[<font class=bodyhighlight>NAMESERVERSANDIPS</font>]<br>lists both IPs and hostnames<br>[<font class=bodyhighlight>SERVERHOSTNAME</font>]<br>example server1.yourdomain.com<br>[<font class=bodyhighlight>SERVERSHAREDIP</font>]<br>shared IP for server<br>[<font class=bodyhighlight>CUSTOMPROFILE_xxxx</font>]<br>where xxx is custom profile field name<br>[<font class=bodyhighlight>CUSTOMPACKAGE_xxxx</font>]<br>where xxx is custom package field name'/*~T*/ WHERE `title` = 'Welcome Email Tags';
UPDATE `help` SET `detail` = /*T*/'[<font class=bodyhighlight>CLIENTNAME</font>] <br>[<font class=bodyhighlight>FIRSTNAME</font>] <br>[<font class=bodyhighlight>TICKETNUMBER</font>]<br>[<font class=bodyhighlight>DESCRIPTION</font>]<br>[<font class=bodyhighlight>CLIENTAPPLICATIONURL</font>] URL to ClientExec.<BR>[<font class=bodyhighlight>COMPANYNAME</font>] <br>[<font class=bodyhighlight>COMPANYADDRESS</font>] <br>[<font class=bodyhighlight>CUSTOM_xxxx</font>]<br>where xxx is custom field name'/*~T*/ WHERE `title` = 'Ticket Template Tags';
UPDATE `help` SET `detail` = /*T*/'[<font class=bodyhighlight>DATE</font>] Date payment is due.<br>[<font class=bodyhighlight>CLIENTNAME</font>] <br>[<font class=bodyhighlight>FIRSTNAME</font>] <br>[<font class=bodyhighlight>CLIENTEMAIL</font>] <br>[<font class=bodyhighlight>AMOUNT</font>] The total price due.<br>[<font class=bodyhighlight>CLIENTAPPLICATIONURL</font>] URL to ClientExec.<br>[<font class=bodyhighlight>FORGOTPASSWORDURL</font>] URL to retrieve forgotten password.<BR>[<font class=bodyhighlight>COMPANYNAME</font>] <BR>[<font class=bodyhighlight>COMPANYADDRESS</font>] <BR>[<font class=bodyhighlight>BILLINGEMAIL</font>] E-mail address for billing inquiries<br>[<font class=bodyhighlight>CUSTOMPROFILE_xxxx</font>]<br>where xxx is custom profile field name'/*~T*/ WHERE `title` = 'Batch Invoice Notifier Tags';
UPDATE `help` SET `detail` = '[<font class=bodyhighlight>DATE</font>] Date payment is due.<br>[<font class=bodyhighlight>SENTDATE</font>] Date invoice was last sent.<br>[<font class=bodyhighlight>CLIENTNAME</font>] <br>[<font class=bodyhighlight>FIRSTNAME</font>] <br>[<font class=bodyhighlight>CLIENTEMAIL</font>] <br>[<font class=bodyhighlight>INVOICENUMBER</font>]<br>[<font class=bodyhighlight>INVOICEHASH</font>] A hash for the invoice in case you need to send one.<br>[<font class=bodyhighlight>INVOICEDESCRIPTION</font>]<br>[<font class=bodyhighlight>TAX</font>]<br>[<font class=bodyhighlight>AMOUNT_EX_TAX</font>] The total price excluding taxes.<br>[<font class=bodyhighlight>AMOUNT</font>] The total price due.<br>[<font class=bodyhighlight>PAID</font>] The amount already paid of the invoice.<br>[<font class=bodyhighlight>BALANCEDUE</font>] The balance due of the invoice.<br>[<font class=bodyhighlight>RAW_AMOUNT</font>] The total price excluding currency symbol.<br>[<font class=bodyhighlight>CLIENTAPPLICATIONURL</font>] URL to ClientExec.<br>[<font class=bodyhighlight>FORGOTPASSWORDURL</font>] URL to retrieve forgotten password.<BR>[<font class=bodyhighlight>COMPANYNAME</font>] <BR>[<font class=bodyhighlight>COMPANYADDRESS</font>] <BR>[<font class=bodyhighlight>BILLINGEMAIL</font>] E-mail address for billing inquiries<br>[<font class=bodyhighlight>CUSTOMPROFILE_xxxx</font>]<br>where xxx is a profile custom field name' WHERE `title` = 'Invoice Template Tags';
UPDATE `help` SET `detail` = '[<font class=bodyhighlight>CLIENTNAME</font>] <br>[<font class=bodyhighlight>FIRSTNAME</font>] <br>[<font class=bodyhighlight>CLIENTEMAIL</font>] <br>[<font class=bodyhighlight>CCLASTFOUR</font>] The last four digits of the customer\'s credit card.<br>[<font class=bodyhighlight>CCEXPDATE</font>] The expiration date of the customer\'s credit card.<br>[<font class=bodyhighlight>CLIENTAPPLICATIONURL</font>] URL to Client Exec.<br>[<font class=bodyhighlight>FORGOTPASSWORDURL</font>] URL to retrieve forgotten password.<BR>[<font class=bodyhighlight>COMPANYNAME</font>] <BR>[<font class=bodyhighlight>COMPANYADDRESS</font>] <BR>[<font class=bodyhighlight>BILLINGEMAIL</font>] Email address for billing inquiries<br>[<font class=bodyhighlight>CUSTOMPROFILE_xxxx</font>]<br>where xxx is a profile custom field name' WHERE `title` = 'Expiring CC Template';
UPDATE `help` SET `detail` = '[<font class=bodyhighlight>CLIENTNAME</font>]<br>[<font class=bodyhighlight>FIRSTNAME</font>]<br>[<font class=bodyhighlight>CLIENTEMAIL</font>] <br>[<font class=bodyhighlight>DOMAIN</font>] <br> The client domain name.<br>[<font class=bodyhighlight>EXPDATE</font>] <br> The expiry date of the domain.<br>[<font class=bodyhighlight>COMPANYNAME</font>] <br>[<font class=bodyhighlight>COMPANYADDRESS</font>] <br>[<font class=bodyhighlight>FORGOTPASSWORDURL</font>] <br> URL to retrieve forgotten password.<br>[<font class=bodyhighlight>BILLINGEMAIL</font>] <br> Email address for billing inquiries<br>[<font class=bodyhighlight>CLIENTAPPLICATIONURL</font>] URL to Client Exec.<br>[<font class=bodyhighlight>CUSTOMPROFILE_xxxx</font>]<br>where xxx is a profile custom field name.<br>' WHERE `title` = 'Domain Reminder Tags';
UPDATE `help` SET `detail` = '[<font class=bodyhighlight>CLIENTNAME</font>] <br>[<font class=bodyhighlight>FIRSTNAME</font>] <br>[<font class=bodyhighlight>CLIENTEMAIL</font>] <br>[<font class=bodyhighlight>COMPANYNAME</font>]<br>[<font class=bodyhighlight>COMPANYADDRESS</font>]<br>[<font class=bodyhighlight>REQUESTIP</font>] IP of the machine which requested the password change.<br>[<font class=bodyhighlight>CONFIRMATION URL</font>] URL that user must press to confirm the password change<br>[<font class=bodyhighlight>CUSTOMPROFILE_xxxx</font>]<br>where xxx is a profile custom field name' WHERE `title` = 'Reset Password Tags';
UPDATE `help` SET `detail` = '[<font class=bodyhighlight>CLIENTNAME</font>] <br>[<font class=bodyhighlight>FIRSTNAME</font>] <br>[<font class=bodyhighlight>CLIENTEMAIL</font>] <br>[<font class=bodyhighlight>NEWPASSWORD</font>] <br>[<font class=bodyhighlight>CLIENTEXEC URL</font>] <br>[<font class=bodyhighlight>COMPANYNAME</font>] <br>[<font class=bodyhighlight>COMPANYADDRESS</font>] <br>[<font class=bodyhighlight>COMPANYEMAIL</font>]' WHERE `title` = 'Get New Password Tags';
UPDATE `help` SET `detail` = '[<font class=bodyhighlight>CLIENTNAME</font>] <br>[<font class=bodyhighlight>FIRSTNAME</font>] <br>[<font class=bodyhighlight>COMPANYNAME</font>] <br>[<font class=bodyhighlight>COMPANYADDRESS</font>] <br>[<font class=bodyhighlight>COMPANYURL</font>]<br> URL to your web site<br>[<font class=bodyhighlight>SUPPORTEMAIL</font>]<br> E-mail to support staff<br>[<font class=bodyhighlight>CLIENTAPPLICATIONURL</font>]<br> URL to ClientExec.' WHERE `title` = 'Rejection E-Mail Tags';
UPDATE `help` SET `detail` = '[<font class=bodyhighlight>CLIENTNAME</font>] <br>[<font class=bodyhighlight>FIRSTNAME</font>] <br>[<font class=bodyhighlight>CLIENTEMAIL</font>] <br>[<font class=bodyhighlight>TICKETNUMBER</font>]<br>[<font class=bodyhighlight>TICKETSUBJECT</font>]<br>[<font class=bodyhighlight>DESCRIPTION</font>]<br>[<font class=bodyhighlight>CLIENTAPPLICATIONURL</font>] URL to ClientExec.<BR>[<font class=bodyhighlight>COMPANYNAME</font>] <br>[<font class=bodyhighlight>COMPANYADDRESS</font>] <br>[<font class=bodyhighlight>CUSTOMPROFILE_xxxx</font>]<br>where xxx is a profile custom field name' WHERE `title` = 'Ticket Template Tags';
UPDATE `help` SET `detail` = '[<font class=bodyhighlight>CLIENTNAME</font>] <br>[<font class=bodyhighlight>FIRSTNAME</font>] <br>[<font class=bodyhighlight>CLIENTEMAIL</font>] <br>[<font class=bodyhighlight>COMPANYNAME</font>]<br>[<font class=bodyhighlight>COMPANYADDRESS</font>]<br>[<font class=bodyhighlight>RATEEXCELLENTSERVICEURL</font>]<br />[<font class=bodyhighlight>RATEGOODSERVICEURL</font>]<br />[<font class=bodyhighlight>RATENOTGREATSERVICEURL</font>]<br />[<font class=bodyhighlight>RATEPOORSERVICEURL</font>]<br />[<font class=bodyhighlight>CUSTOMPROFILE_xxxx</font>]<br>where xxx is a profile custom field name' WHERE `title` = 'Feedback Template Tags';
UPDATE `help` SET `detail` = '[<font class=bodyhighlight>CLIENTNAME</font>] <br>[<font class=bodyhighlight>FIRSTNAME</font>] <br>[<font class=bodyhighlight>CLIENTEMAIL</font>] <br>[<font class=bodyhighlight>COMPANYNAME</font>]<br>[<font class=bodyhighlight>COMPANYADDRESS</font>]<br>[<font class=bodyhighlight>TICKETNUMBER</font>]<br />[<font class=bodyhighlight>CUSTOMPROFILE_xxxx</font>]<br>where xxx is a profile custom field name' WHERE `title` = 'AutoClose Service Template Tags';
UPDATE `help` SET `detail` = '[<font class=bodyhighlight>CLIENTNAME</font>] <br>[<font class=bodyhighlight>FIRSTNAME</font>] <br>[<font class=bodyhighlight>CLIENTEMAIL</font>] <br>[<font class=bodyhighlight>COMPANYNAME</font>] <br>[<font class=bodyhighlight>COMPANYADDRESS</font>] <br>[<font class=bodyhighlight>TICKETNUMBER</font>] <br>[<font class=bodyhighlight>TICKETSUBJECT</font>] <br>[<font class=bodyhighlight>TICKETTYPE</font>] <br>[<font class=bodyhighlight>DESCRIPTION</font>] <br>[<font class=bodyhighlight>CLIENTAPPLICATIONURL</font>]' WHERE `title` = 'Notify Support For New High Priority Ticket Template Tags';
UPDATE `help` SET `detail` = '[<font class=bodyhighlight>CLIENTNAME</font>] <br>[<font class=bodyhighlight>FIRSTNAME</font>] <br>[<font class=bodyhighlight>TICKETNUMBER</font>] <br>[<font class=bodyhighlight>TICKETSUBJECT</font>] <br>[<font class=bodyhighlight>TICKETTYPE</font>] <br>[<font class=bodyhighlight>DESCRIPTION</font>]<br>[<font class=bodyhighlight>COMPANYNAME</font>] <br>[<font class=bodyhighlight>COMPANYADDRESS</font>] <br>' WHERE `title` = 'Notify Customer For New Ticket Template Tags';
UPDATE `help` SET `detail` = '[<font class=bodyhighlight>CLIENTNAME</font>] <br>[<font class=bodyhighlight>FIRSTNAME</font>] <br>[<font class=bodyhighlight>TICKETNUMBER</font>] <br>[<font class=bodyhighlight>DESCRIPTION</font>]' WHERE `title` = 'Notify Assignee For Ticket Reply Template Tags';
UPDATE `help` SET `detail` = '[<font class=bodyhighlight>CLIENTNAME</font>] <br>[<font class=bodyhighlight>FIRSTNAME</font>] <br>[<font class=bodyhighlight>TICKETNUMBER</font>] <br>[<font class=bodyhighlight>DESCRIPTION</font>]' WHERE `title` = 'Notify For New FeedBack Template Tags';