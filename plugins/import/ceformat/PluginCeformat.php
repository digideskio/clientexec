<?php
require_once 'modules/admin/models/ImportPlugin.php';

class PluginCeformat extends ImportPlugin
{
    protected $_description;
    protected $_name = 'ceformat';
    protected $_title = 'ClientExec Format';
    protected $_tplPath = 'PluginCeformat.phtml';

    function __construct($user, $typeOfFetch = 1)
    {
        $this->_description = lang("This import plugin imports customers, packages, and invoices from a ClientExec exported file.");
        parent::__construct($user, $typeOfFetch);
    }
}
