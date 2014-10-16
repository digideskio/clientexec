/*!
 * Ext JS Library 3.0+
 * Copyright(c) 2006-2009 Ext JS, LLC
 * licensing@extjs.com
 * http://www.extjs.com/license
 */

var InvoiceEntryWin;
var createinvoicechecked = 0;
var billingIdType = 0;
var readOnly = false;
var originalFields = null;
var billingtypestore;
var appliestostore;
var istaxable = "1";

function ShowEditInvoiceEntryWindow(id,isRecurringFee,isProduct,customerid){
        if(document.getElementById('istaxable')) {
            istaxable = document.getElementById('istaxable').value;
        }

		if (typeof(customerid) === "undefined") customerid = 0;

        appliestostore.load({params:{customerid:customerid}});
        if(id==0){
            //create new charge
            InvoiceEntryWin.CreateWindow(false,isRecurringFee);
            InvoiceEntryWin.SetForNewEntry();
            createinvoicechecked = 0;

            if(istaxable == "1"){
                // If the customer is taxable, show taxable checked
                Ext.getCmp('taxable').setValue(true);
            }else{
                // If the customer is not taxable, disable the taxable setting
                Ext.getCmp('taxable').setDisabled(true);
            }
        }else{
            InvoiceEntryWin.CreateWindow(true,isRecurringFee);
            if(isProduct == undefined){
                isProduct = false;
            }
            InvoiceEntryWin.LoadFields(id,isRecurringFee,isProduct);
            if((isRecurringFee!=null) && (isRecurringFee==true)){
                InvoiceEntryWin.SetForUpdateRecurringFee();
            }else{
                InvoiceEntryWin.SetForUpdateInvoiceEntry();
            }
        }
        billingtypestore.load();
}

Ext.onReady(function(){
    var win;

    if ( document.getElementById('addchargebutton') ) {
        var btAddCharge = new Ext.Button({
            id:"addcharge",
            renderTo:"addchargebutton",
            text: lang('Add Charge'),
            iconAlign: 'left',
            handler:function(){
                 ShowEditInvoiceEntryWindow(0);
            }
        })
    }

    var duedatevalue = (new Date()).dateFormat(clientexec.dateFormat);

    var duedatefield = new Ext.form.DateField({
        fieldLabel: lang('Due Date'),
        height: "5px",
        id:'nextdate',
        name: 'nextdate',
        anchor:'95%',
        allowBlank:false,
        value: duedatevalue,
        selectOnFocus:true,
        format : clientexec.dateFormat
    });

    billingtypestore = new Ext.data.JsonStore({
        url: 'index.php?fuse=billing&action=GetBillingTypes',
        root: 'billingtypes',
        idProperty: 'id',
        fields: ['id','name','description','detail','price']
    });

    var billingtype = new Ext.form.ComboBox({
        tpl: '<tpl for="."><div title="<b>Name</b>: {name} - {price}<br/><b>Description</b>: {description}<br/><b>Detail</b>: {detail}" class="x-combo-list-item">{name}</div></tpl>',
        triggerAction: 'all',
        id: 'billingtype',
        name: 'billingtype',
        anchor:'95%',
        store: billingtypestore,
        valueField: 'id',
        displayField: 'name',
        hiddenName:'billingtypeid',
        fieldLabel: lang('Select a Billing Type'),
        allowBlank: false,
        mode: 'local',
        forceSelection: true,
        editable: false,
        listeners:{
         'select': function(combo,record){
            if( (Ext.getCmp('name').getValue() !='') && (Ext.getCmp('name').getValue() != record.data.description) ){
                Ext.MessageBox.confirm('Confirm', lang('Warning: Your data will be overwritten.  Click YES if you want to replace the text fields with the default content of the selected billing type.'),
                function(btn, text){
                    if (btn == 'yes'){
                        PopulateFields(record.data.description,record.data.detail,record.data.price);
                    }
                });
            }else{
                PopulateFields(record.data.description,record.data.detail,record.data.price);
            }
         }
      }
    });

    var billingCycleStore = new Ext.data.ArrayStore({
        id:     'billingcycleOptions',
        name:   'billingcycleOptions',
        fields: [
            'id',
            'displayText'
        ],
        data:   [
            [
                '1',
                lang('Monthly')
            ],
            [
                '3',
                lang('Quarterly')
            ],
            [
                '6',
                lang('Semi-Annual')
            ],
            [
                '12',
                lang('Annual')
            ],
            [
                '24',
                lang('Every 2 Years')
            ],

            // Added to work properly with the Domians Registration Length
            [
                '36',
                lang('Every 3 Years')
            ],
            [
                '48',
                lang('Every 4 Years')
            ],
            [
                '60',
                lang('Every 5 Years')
            ],
            [
                '72',
                lang('Every 6 Years')
            ],
            [
                '84',
                lang('Every 7 Years')
            ],
            [
                '96',
                lang('Every 8 Years')
            ],
            [
                '108',
                lang('Every 9 Years')
            ],
            [
                '120',
                lang('Every 10 Years')
            ]
        ]
    });

    var billingcycle = new Ext.form.ComboBox({
        typeAhead: false,
        triggerAction: 'all',
        name: 'billingcycle',
        id:'billingcycle',
        hideLabel : false,
        hiddenName:'paymentterm',
        anchor:'95%',
        store: billingCycleStore,
        valueField: 'id',
        displayField: 'displayText',
        fieldLabel: lang('Select a Billing Cycle'),
        allowBlank: false,
        mode: 'local',
        disabled:true,
        forceSelection: true,
        editable: false
    });

    var subscriptionStore = new Ext.data.ArrayStore({
        id:     'subscriptionOptions',
        name:   'subscriptionOptions',
        fields:   [
            'id',
            'displayText'
        ],
        data:     [
            [
                0,
                lang("No subscription")
            ],
            [
                1,
                lang("Subscription")
            ]
        ]
    });

    var subscription = new Ext.form.ComboBox({
        typeAhead: false,
        triggerAction: 'all',
        name: 'subscription',
        id:'subscription',
        hideLabel : false,
        hiddenName:'paymentSubscription',
        anchor:'95%',
        store: subscriptionStore,
        valueField: 'id',
        displayField: 'displayText',
        fieldLabel: lang('Subscription'),
        allowBlank: false,
        mode: 'local',
        disabled:true,
        forceSelection: true,
        editable: false
    });

    appliestostore = new Ext.data.JsonStore({
        url: 'index.php?fuse=clients&action=adminGetUserPackages&filteroncustomer=1&returnNone=1',
        root: 'results',
        idProperty: 'productid',
        fields: ['productid','name']
    });

    var appliesto = new Ext.form.ComboBox({
        typeAhead: false,
        triggerAction: 'all',
        store:appliestostore,
        name: 'appliesto',
        id: 'appliesto',
        hiddenName:'appliestoid',
        anchor:'95%',
        valueField: 'productid',
        displayField: 'name',
        value: lang('None'),
        fieldLabel: lang('Applies to product'),
        allowBlank: false,
        mode: 'local',
        forceSelection: true,
        editable: false
    });
//
    var simpleForm = new Ext.FormPanel({
        labelAlign: 'top',
        frame:true,
        hideBorders:true,
        shadow:false,
        floating:false,
        autoHeight:true,
        title: '',
        bodyStyle:'padding:5px 5px 0',
        width: 600,
        items: [
            {
                layout:'column',
                items:[{
                    columnWidth:.7,
                    layout: 'form',
                    items: [billingtype]
                },{
                    columnWidth:.3,
                    layout: 'form',
                    items: [duedatefield]
                }]
            },
            {
                id:'id',
                name: 'id',
                value: '0',
                xtype: "hidden",
                hidden:true
            },
            {
                fieldLabel: lang('Charge Name'),
                id:'name',
                name: 'name',
                xtype: "textfield",
                allowBlank:false,
                anchor:'98%'
            },
            {
                fieldLabel: lang('Charge Description'),
                id:'desc',
                name: 'desc',
                xtype: "textarea",
                allowBlank:false,
                height: 100,
                anchor:'98%'
            },
            {
                layout:'column',
                items:[
                    {
                        columnWidth:.7,
                        layout: 'form',
                        items: [appliesto]
                    },
                    {
                        columnWidth:.3,
                        layout: 'form',
                        items: [
                            {
                                xtype:'numberfield',
                                fieldLabel: lang('Price'),
                                name: 'price',
                                id: 'price',
                                anchor:'95%'
                            }
                        ]
                    }
                ]
            },
            {
                xtype:'fieldset',
                checkboxToggle:false,
                id: 'advancedoptions',
                name: 'advancedoptions',
                title: '&nbsp;&nbsp;'+lang('Advanced Options'),
                autoHeight:true,
                border : true,
                collapsed: false,
                items :[
                    {
                        layout:'column',
                        items:[
                            {
                                columnWidth:.1,
                                items: [
                                    {
                                        xtype:'checkbox',
                                        name: 'taxable',
                                        id:'taxable',
                                        style:'position:relative;top:-4px;',
                                        hideLabel:true
                                    }
                                ]
                            },
                            {
                                columnWidth:.9,
                                items: [
                                    {
                                        xtype:'label',
                                        name:'taxentrylabel',
                                        id: 'taxentrylabel',
                                        text : lang('Tax this entry')
                                    }
                                ]
                            }
                        ]
                    },
                    {
                        layout:'column',
                        items:[
                            {
                                columnWidth:.1,
                                items: [
                                    {
                                        xtype:'checkbox',
                                        name: 'recurring',
                                        style:'position:relative;top:-4px;',
                                        id: 'recurring',
                                        hideLabel:true,
                                        handler: function(cb,checked){
                                            //hide invoice checkbox check (should refactor)
                                            if(Ext.getCmp('recurring').checked){
                                                Ext.getCmp('creatinvoice').setRawValue(0);
                                                Ext.getCmp('creatinvoice').setVisible(false);
                                                Ext.getCmp('creatinvoicelabel').setVisible(false);
                                            }else{
                                                Ext.getCmp('creatinvoice').setVisible(true);
                                                Ext.getCmp('creatinvoicelabel').setVisible(true);
                                            }
                                            if(checked){
                                                Ext.getCmp('billingcycle').setDisabled(false);
                                                Ext.getCmp('recurringmonths').setDisabled(false);
                                                //Ext.getCmp('subscription').setDisabled(false);
                                                //Ext.getCmp('subscriptionid').setDisabled(false);
                                            }else{
                                                Ext.getCmp('billingcycle').setDisabled(true);
                                                //Ext.getCmp('subscription').setDisabled(true);
                                                Ext.getCmp('recurringmonths').setDisabled(true);
                                                //Ext.getCmp('subscriptionid').setDisabled(true);
                                                if(Ext.getCmp('appliesto').value!=0 && Ext.getCmp('appliesto').value!=lang("None")){
                                                }
                                            }
                                        }
                                    }
                                ]
                            },
                            {
                                columnWidth:.9,
                                items: [
                                    {
                                        xtype:'label',
                                        id :'labelrecurringpayment',
                                        text : lang('Is this a recurring payment?')
                                    }
                                ]
                            }
                        ]
                    },
                    {
                        layout:'column',
                        style:'padding-top:10px;',
                        items:[
                            {
                                columnWidth:.7,
                                layout: 'form',
                                items: [billingcycle]
                            },
                            {
                                columnWidth:.3,
                                layout: 'form',
                                items: [
                                    {
                                        xtype:'textfield',
                                        hideLabel : false,
										fieldLabel: "<span style='border-bottom: 1px solid rgb(223, 223, 223); top: 0px;' class='tip-target' title='" + lang('The number of months the recurring charge will be active for.') + "'>" +  lang('Duration in months') + "</span>",
                                        name: 'recurringmonths',
                                        id:'recurringmonths',
                                        disabled:true,
                                        anchor:'95%'
                                    }
                                ]
                            }
                        ]
                    },
                    {
                        layout:'column',
                        style:'padding-top:10px;',
                        items:[
                            {
                                columnWidth:.7,
                                layout: 'form',
                                items: [subscription]
                            },
                            {
                                columnWidth:.3,
                                layout: 'form',
                                items: [
                                    {
                                        xtype:'textfield',
                                        hideLabel : false,
                                        fieldLabel: lang('Subscription ID'),
                                        name: 'subscriptionid',
                                        id:'subscriptionid',
                                        disabled:true,
                                        anchor:'95%'
                                    }
                                ]
                            }
                        ]
                    }
                ]
            }
        ]
    });

    function TableTypeWindow(update){
        if(this instanceof TableTypeWindow){
            this.update = update;
            this.CreateWindow = createWindow;
            this.LoadFields = loadFields;
            this.SetForUpdateRecurringFee = setForUpdateRecurringFee;
            this.SetForUpdateInvoiceEntry = setForUpdateInvoiceEntry;
            this.SetForNewEntry = setForNewEntry;
        }else{
            return new TableTypeWindow();
        }
    }
    InvoiceEntryWin = new TableTypeWindow();

    function setForNewEntry(){
        readOnly = false;
        originalFields = {
            nextdate: duedatevalue,
            name: '',
            desc: '',
            appliestoid: 0,
            price: '0',
            taxable: 'false',
            recurring: 'false',
            paymentterm: '1',
            recurringmonths: '',
            paymentSubscription: 0,
            subscriptionid: ''
        };


        //show add invoice checkbox
        Ext.getCmp('recurring').setDisabled(false);
        Ext.getCmp('recurring').setVisible(true);
        Ext.getCmp('recurringmonths').setDisabled(true);
        Ext.getCmp('recurringmonths').ownerCt.setVisible(true);

        Ext.getCmp('subscription').setDisabled(true);
        Ext.getCmp('subscription').ownerCt.setVisible(false);
        Ext.getCmp('subscriptionid').setDisabled(true);
        Ext.getCmp('subscriptionid').ownerCt.setVisible(false);

        Ext.getCmp('billingtype').setDisabled(false);
        Ext.getCmp('nextdate').setDisabled(false);
        Ext.getCmp('appliesto').setDisabled(false);
        Ext.getCmp('price').setDisabled(false);
        Ext.getCmp('taxable').setDisabled(false);
        Ext.getCmp('recurring').setDisabled(false);

        Ext.getCmp('name').setDisabled(false);
        Ext.getCmp('desc').setDisabled(false);

        Ext.getCmp('labelrecurringpayment').setDisabled(false);
        Ext.getCmp('labelrecurringpayment').setVisible(true);
        Ext.getCmp('creatinvoice').setValue(false);
        Ext.getCmp('creatinvoice').setRawValue(0);
        Ext.getCmp('overidesubscription').setValue(false);
        Ext.getCmp('overidesubscription').setRawValue(0);
        Ext.getCmp('overidesubscription').setVisible(false);
        Ext.getCmp('overidesubscriptionlabel').setVisible(false);
        Ext.getCmp('btnUpdate').setDisabled(false);

        billingcycle.setDisabled(true);
        subscription.setDisabled(true);
        billingcycle.ownerCt.setVisible(true);
    }

    function setForUpdateRecurringFee(){
        Ext.getCmp('recurring').setDisabled(true);
        Ext.getCmp('labelrecurringpayment').setDisabled(true);
    }

    function setForUpdateInvoiceEntry(){
        Ext.getCmp('billingtype').setDisabled(false);
        Ext.getCmp('recurring').setDisabled(true);
        Ext.getCmp('recurring').setVisible(false);
        Ext.getCmp('recurringmonths').setDisabled(true);
        Ext.getCmp('recurringmonths').ownerCt.setVisible(false);

        Ext.getCmp('subscription').setDisabled(true);
        Ext.getCmp('subscription').ownerCt.setVisible(false);
        Ext.getCmp('subscriptionid').setDisabled(true);
        Ext.getCmp('subscriptionid').ownerCt.setVisible(false);

        Ext.getCmp('labelrecurringpayment').setDisabled(true);
        Ext.getCmp('labelrecurringpayment').setVisible(false);
        billingcycle.setDisabled(true);
        subscription.setDisabled(true);
        billingcycle.ownerCt.setVisible(false)
    }

    function loadFields(id,isRecurringFields,isProduct){
        if(!isRecurringFields){
            url = 'index.php?fuse=billing&controller=entry&action=getinvoiceentry';
        }else{
            url = 'index.php?fuse=billing&controller=recurring&action=getrecurringcharges';
        }
        simpleForm.getForm().load({
            url: url,
            waitMsg:lang('Loading'),
            success: function(form, action) {
                var response = Ext.util.JSON.decode(action.response.responseText);
                Ext.getCmp('overidesubscription').setValue(false);
                Ext.getCmp('overidesubscription').setRawValue(0);
                if(response.data.showSubscription){
                    Ext.getCmp('subscription').ownerCt.setVisible(true);
                    Ext.getCmp('subscriptionid').ownerCt.setVisible(true);

                    if(response.data.readonly){
                        Ext.getCmp('overidesubscription').setVisible(true);
                        Ext.getCmp('overidesubscriptionlabel').setVisible(true);
                    }else{
                        Ext.getCmp('overidesubscription').setVisible(false);
                        Ext.getCmp('overidesubscriptionlabel').setVisible(false);
                    }
                }else{
                    Ext.getCmp('subscription').setDisabled(true);
                    Ext.getCmp('subscription').ownerCt.setVisible(false);
                    Ext.getCmp('subscriptionid').setDisabled(true);
                    Ext.getCmp('subscriptionid').ownerCt.setVisible(false);

                    Ext.getCmp('overidesubscription').setVisible(false);
                    Ext.getCmp('overidesubscriptionlabel').setVisible(false);
                }

                if(response.data.readonly){
                    readOnly = true;

                    Ext.getCmp('nextdate').setDisabled(true);
                    Ext.getCmp('appliesto').setDisabled(true);
                    Ext.getCmp('price').setDisabled(true);
                    Ext.getCmp('taxable').setDisabled(true);
                    //Ext.getCmp('recurring').setDisabled(true);
                    Ext.getCmp('billingcycle').setDisabled(true);
                    Ext.getCmp('recurringmonths').setDisabled(true);
                    Ext.getCmp('subscription').setDisabled(true);
                    Ext.getCmp('subscriptionid').setDisabled(true);

                    if(response.data.billingtypeid=="-1"){
                        Ext.getCmp('name').setDisabled(true);
                        Ext.getCmp('desc').setDisabled(true);
                        Ext.getCmp('btnUpdate').setDisabled(true);
                    }
                }else{
                    readOnly = false;

                    Ext.getCmp('nextdate').setDisabled(false);
                    Ext.getCmp('appliesto').setDisabled(false);
                    Ext.getCmp('price').setDisabled(false);
                    Ext.getCmp('taxable').setDisabled(false);
                    //Ext.getCmp('recurring').setDisabled(false);
                    Ext.getCmp('billingcycle').setDisabled(false);
                    Ext.getCmp('recurringmonths').setDisabled(false);
                    Ext.getCmp('subscription').setDisabled(false);
                    Ext.getCmp('subscriptionid').setDisabled(false);

                    Ext.getCmp('name').setDisabled(false);
                    Ext.getCmp('desc').setDisabled(false);
                    Ext.getCmp('btnUpdate').setDisabled(false);
                }

                //check to see if this is a recurring if so can not change type
                //and just mark it as a Recurring charge
                if(parseInt(response.data.billingtypeid) < 0 || response.data.readonly){
                    Ext.getCmp('billingtype').setDisabled(true);
                }else{
                    Ext.getCmp('billingtype').setDisabled(false);
                }
                originalFields = response.data;
                billingIdType = response.data.billingtypeid;
                //set the billingtype name
                //might want to return additional string to put after like
                //Product: The name of the product
                //Domain: somedomain.com
                //Coupon: xmlfree2
                //Addon: addon name
                if(response.data.billingtypeid=="-1"){
                    Ext.getCmp('billingtype').setValue('Product');
                }else if(response.data.billingtypeid=="-2"){
                    Ext.getCmp('billingtype').setValue('Domain');
                }else if(response.data.billingtypeid=="-3"){
                    Ext.getCmp('billingtype').setValue('Coupon');
                }else if(response.data.billingtypeid=="-4"){
                    Ext.getCmp('billingtype').setValue('Product Addon');
                }
            },
            failure: function(form, action) {alert('failure');},
            params: {
                id: id,
                isProduct: isProduct
            }
        });

    }

    function createWindow(update,isRecurring){
        requesturl = 'index.php?fuse=billing&controller=recurring&action=setrecurringentry';
        if(update){
            if(!isRecurring){
                requesturl = 'index.php?fuse=billing&controller=entry&action=setinvoiceentry'
            }
            buttonText = lang('Update');
            titleText = '&nbsp;&nbsp;'+lang('Edit Charge');
        }else{
            buttonText = lang('Save');
            titleText = '&nbsp;&nbsp;'+lang('Add Charge');
        }

        if(!win){
            win = new Ext.Window({
                layout:'fit',
                title:titleText,
                width:480,
                y:100,
                shadow:false,
                autoHeight:true,
                modal:true,
                closable:false,
                closeAction:'hide',
                plain: true,
                items: [simpleForm],
                fbar: [
                    {
                        xtype:'checkbox',
                        name: 'overidesubscription',
                        id: 'overidesubscription',
                        style:'position:relative;top:-3px;',
                        hideLabel:true,
                        handler: function(cb,checked){
                            if(checked){
                                Ext.getCmp('subscription').setDisabled(false);
                                Ext.getCmp('subscriptionid').setDisabled(false);
                                Ext.getCmp('btnUpdate').setDisabled(false);
                            }else{
                                Ext.getCmp('subscription').setDisabled(true);
                                Ext.getCmp('subscriptionid').setDisabled(true);

                                if(originalFields.billingtypeid=="-1"){
                                    Ext.getCmp('btnUpdate').setDisabled(true);
                                }
                            }
                        }
                    },
                    {
                        xtype:'label',
                        name: 'overidesubscriptionlabel',
                        id: 'overidesubscriptionlabel',
                        text : lang('Overide Subscription'+'  ')
                    },
                    {
                        xtype:'checkbox',
                        name: 'creatinvoice',
                        id: 'creatinvoice',
                        style:'position:relative;top:-3px;',
                        hideLabel:true,
                        handler: function(cb,checked){
                            if(checked){
                                createinvoicechecked = 1;
                            }else{
                                createinvoicechecked = 0;
                            }
                        }
                    },
                    {
                        xtype:'label',
                        name: 'creatinvoicelabel',
                        id: 'creatinvoicelabel',
                        text : lang('Create Invoice'+'  ')
                    },
                    {
                        text: 'Close',
                        handler: function(){
                            win.hide();
                        }
                    },
                    {
                        id:'btnUpdate',
                        text: buttonText,
                        handler: function(){
                            simpleForm.getForm().submit({
                                clientValidation: true,
                                url: requesturl,
                                params: {
                                    readOnly: readOnly,
                                    createinvoice: createinvoicechecked,
                                    isrecurring: Ext.getCmp('recurring').checked,
                                    billingtypeid: billingIdType,
                                    nextdate: originalFields.nextdate,
                                    name: originalFields.name,
                                    desc: originalFields.desc,
                                    appliestoid: originalFields.appliestoid,
                                    price: originalFields.price,
                                    taxable: Ext.getCmp('taxable').checked,
                                    recurring: originalFields.recurring,
                                    paymentterm: originalFields.paymentterm,
                                    recurringmonths: originalFields.recurringmonths,
                                    paymentSubscription: originalFields.paymentSubscription,
                                    subscriptionid: originalFields.subscriptionid
                                },
                                success: function(form, action) {
                                    refreshProfileHeaderBalance();

                                   win.hide();
                                   //get the grid if there is one
                                   //if we are in the billingtype view
                                   //if not then skip
                                   if (typeof(invoices_store) != 'undefined'){
                                       invoices_store.load();
                                   }
                                   if (typeof(entries_store) != 'undefined'){
                                       entries_store.load();
                                   }
                                   if (typeof(invoicestore) != 'undefined'){
                                       //ShowingInvoiceDetails(selectedinvoice);
                                       invoicestore.load();
                                   }

                                   if (typeof(uninvoicedlist) != 'undefined') {
                                        uninvoicedlist.grid.reload({params:{start:0}});
                                   }

                                },
                                failure: function(form, action) {
                                    refreshProfileHeaderBalance();
                                    switch (action.failureType) {
                                        case Ext.form.Action.CLIENT_INVALID:
                                            Ext.Msg.alert('Failure', 'Form fields may not be submitted with invalid values');
                                            break;
                                        case Ext.form.Action.CONNECT_FAILURE:
                                            Ext.Msg.alert('Failure', 'Ajax communication failed');
                                            break;
                                        case Ext.form.Action.SERVER_INVALID:
                                           Ext.Msg.alert('Failure', action.result.msg);
                                   }
                                }
                            });
                        }
                    }
                ]
            });
        }
        simpleForm.getForm().reset();
        duedatevalue = (new Date()).dateFormat(clientexec.dateFormat);
        duedatefield.setValue(duedatevalue);
        appliesto.setValue(lang('None'));

        win.show(this);
    }

    var refreshProfileHeaderBalance = function(){
		if ($('#profile-header-credit-value')[0] === undefined) return;

        Ext.Ajax.request({
            url:        "index.php?fuse=billing&action=GetProfileHeaderBalance",
            params:     {
            },
            autoAbort:  true,
            success:    function(xhr) {
                var data = Ext.util.JSON.decode(xhr.responseText);

                if (document.getElementById('profile-header-credit-value')) {
                    document.getElementById('profile-header-credit-value').innerHTML = data.balance.credit;
                    document.getElementById('profile-header-totalpaid-value').innerHTML = data.balance.totalpaid;
                    document.getElementById('profile-header-uninvoiced-value').innerHTML = data.balance.uninvoiced;
                    document.getElementById('profile-header-totalunpaid-value').innerHTML = data.balance.totalunpaid;
                }
            },
            failure:    function(){
            }
        });
    }

    // option button code
    function toggleButtons(count){
        if(count == undefined){
            //get the sm if there is one
            //if we are in the currency view
            //if not then skip
            sm = Ext.getCmp("billingtype-sm");
            if(sm!=null){
                count = sm.getCount();
            }
        }
        if(count != undefined){
            //get the deletebutton if there is one
            //if we are in the currency view
            //if not then skip
            deletebutton = Ext.getCmp("actionbuttons");

            //get the setdefaultbutton if there is one
            //if we are in the currency view
            //if not then skip
            archivebutton = Ext.getCmp("statusbuttons");

            if(count>0){
                if(deletebutton!=null){
                    deletebutton.setDisabled(false);
                }

                if(archivebutton!=null){
                    archivebutton.setDisabled(false);
                }
            }else{
                if(deletebutton!=null){
                    deletebutton.setDisabled(true);
                }

                if(archivebutton!=null){
                    archivebutton.setDisabled(true);
                }
            }
        }
    }

     ///populates the fields
    function PopulateFields(description,detail,price){
        Ext.getCmp('name').setValue(description);
        Ext.getCmp('desc').setValue(detail);
        Ext.getCmp('price').setValue(price);
    }

});