//namespace for packagemanager for userpackages
var packageemanager = {};
packageemanager.package_id = 0;
packageemanager.arrayOfLazyLoadingPanels = ["hosting","domaininfo","hostrecords","nameservers", 'certinfo']; //any panel setting type that requires lazy loading needs to be in this list
packageemanager.activeTab = "groupinfo";

Ext.onReady(function(){

    /**
    * Toggle recurring billing dropdown
    */
    function billingToggleRecurring()
    {
        $('.fieldblock').each(function (index, domEle) {
                if (domEle.id != "fieldblock_recurring") {
                if (Ext.getCmp('ctrlrecurring').getValue() == 1) {
                    domEle.style.display = "";
                } else {
                    domEle.style.display = "none";
                }
                }
        });

        if (Ext.getCmp('ctrlrecurring').getValue() == 1) {
            //set first item in dropdown
            Ext.getCmp('ctrlbillingcycle').setValue(Ext.getCmp('ctrlbillingcycle').getStore().getAt(0).data.field1);
        } else {
            //remove validation
        }

        billingChangedBillingCycle();
    }

    function billingChangedBillingCycle() {
        Ext.Msg.alert('Warning', 'Changing the Recurring or Billing Cycle of the product can affect its addons.<br>Please make sure to update the addons after finishing updating the billing information.');
    }



    /**
     * Lets get the status buttons back dynamically
     */
    function toggleButtons(count){

        count = sm.getCount();
        if(count == 0) {
            // Hide everything
            Ext.getCmp('sendbuttons').setDisabled(true);
            //TODO 4.2 Ext.getCmp('deleteproducts').setDisabled(true);
        } else {

            // Show Buttons
            Ext.getCmp('sendbuttons').setDisabled(false);
            //TODO 4.2 Ext.getCmp('deleteproducts').setDisabled(false);

        }
    }

    packageemanager.deleteGridProduct = function(deleteFromGrid){
        var packageids = packageemanager.package_id;
        if (deleteFromGrid) {
            //get package_ids from selected items in grid
            packageids = packageemanager.getProductItems();
        }
        Ext.Ajax.request({
            url: 'index.php?fuse=clients&controller=packages&action=admindeletepackages',
            success: function(xhr) {
                var json = ce.parseAjaxResponse(xhr);
                if (!json.error) {
                    window.location.href = "index.php?fuse=clients&controller=userprofile&view=profileproducts";
                }
            },
            params: {
                domainid: packageids,
                performonserver: $('#performonserver:checked').val()
            }
        });
    };

    packageemanager.TogglePackageStatusPackageId = 0;
    packageemanager.TogglePackageStatus = function(packageid, currentStatus) {
        packageemanager.TogglePackageStatusPackageId = packageid;
        $('#productstatudropdown').show();
        var x = $('#linkforproductstatus_'+packageid).offset().left-8;
        var y = $('#linkforproductstatus_'+packageid).offset().top -2;
        $('.productstatus').show();
        $('#linkforproductstatus_'+packageid).hide();

        if(!packageemanager.productstatudropdown) {
            packageemanager.productstatudropdown = new Ext.form.ComboBox({
                triggerAction: 'all',
                lazyRender:true,
                editable:false,
                width: 60,
                store : new Ext.data.ArrayStore({idIndex: 0,fields: ['id','name']}),
                displayField:'name',
                valueField:'id',
                mode:'local',
                applyTo: 'productstatudropdown',
                listeners:{
                    'select': function(cb, record){
                        recurringgrid.disable();
                        Ext.Ajax.request({
                            url : 'index.php?fuse=clients&action=adminUpdateUserPackage',
                            success: function(xhr) {
                                recurringgrid.enable();
                                packageemanager.productstatudropdown.setVisible(false);
                                packageemanager.entries_store.load();
                                json = ce.parseAjaxResponse(xhr);

                            },
                            params : {userPackageId:packageemanager.TogglePackageStatusPackageId,settingtype:'groupinfo',productstatus:cb.getValue()}
                        });
                    }
                }
            });
        } else {
            packageemanager.productstatudropdown.setVisible(true);
        }

        var store = packageemanager.productstatudropdown.getStore();
        store.removeAll();
        packageemanager.productstatudropdown.setPagePosition(x,y);
        Ext.Ajax.request({
            url: 'index.php?fuse=clients&action=getProductStatuses',
            params:   {packageId: packageid},
            success: function(xhr) {
                var json = ce.parseAjaxResponse(xhr);
                //packageemanager.productstatudropdown
                var store = packageemanager.productstatudropdown.getStore();
                var MyRecordType = Ext.data.Record.create(['id', 'name']);
                dropdownoptions = json.statuses[0].dropdownoptions;
                for (var x=0; x< dropdownoptions.length; x++) {
                    myrec = new MyRecordType({
                        "id":dropdownoptions[x][0],
                        "name":dropdownoptions[x][1]
                    });
                    store.add(myrec);
                }
                packageemanager.productstatudropdown.setValue(currentStatus);
            }
        });

        return false;
        if(document.getElementById('label_customerstatus').style.display == "none"){
            document.getElementById('label_customerstatus').style.display = "";
            Ext.getCmp('productstatuscombo').setVisible(false);
            //call store load for package status
        }else{
            document.getElementById('label_customerstatus').style.display = "none";
            Ext.getCmp('productstatuscombo').setVisible(true);
            //call store load for package status
        }
    };


    /**
     * Update the plugin drop down actions
     */
    packageemanager.updateAvailablePluginActions = function()
    {
        Ext.Ajax.request({
            url: 'index.php?fuse=clients&action=getAvailablePluginActions',
            success: function(xhr) {
                var json = ce.parseAjaxResponse(xhr);
                //call to populate plugin options from ajax call or initial productData call
                //ensure when we get new product data that we recreate this list

                var pluginActionMenu = Ext.getCmp('pluginactiondropdownmenu');
                pluginActionMenu.removeAll();
                var count=0;
                for ( var i in json.pluginActions )
                {
                    if (typeof json.pluginActions[i] != "object") continue;
                    var comboitem = new Ext.menu.Item({
                        itemId: json.pluginActions[i].cmd,
                        text: json.pluginActions[i].label
                    });
                    comboitem.addListener("click", function(menuItem, e ){

                        //call special methods for actions if they exist
                        var funcName = "pluginaction_"+menuItem.itemId;
                        if (eval("typeof " + funcName + " == 'function'")) {
                            eval(funcName+"()");
                        }else {
                            //we only want to call if we are not calling another function
                            //as that other function will handle when it wants to call the plugin action method
                            packagemanager.plugincallaction(menuItem.itemId);
                        }
                    });
                    pluginActionMenu.addMenuItem(comboitem);
                    count++;
                }
                if (count==0) {
                    $('#plugindropdown').hide();
                } else {
                    $('#plugindropdown').show();
                }
            },
            params: {
                id:packageemanager.package_id
            }
        });
    };

    packageemanager.plugincallaction = function(itemid,args)
    {

        if (args==null) {
            args = "";
        }

        Ext.Ajax.request({
            url: 'index.php?fuse=clients&action=callPluginAction',
            success: function(xhr) {
                json = ce.parseAjaxResponse(xhr);
                packageemanager.showManageSettingByType(packageemanager.activeTab);
                packageemanager.updateAvailablePluginActions();
            },
            params: {
                id:packageemanager.package_id,
                actioncmd:itemid,
                additionalArgs: args
            }});
    };

    packageemanager.currentProductStore = new Ext.data.JsonStore({
        root:           'results',
        totalProperty:  'totalcount',
        idProperty:     'productid',
        remoteSort:     true,
        fields:     ['id','producttype','Permissions','producttypename','dateactivated','pluginname','hasplugin','productgroupname','productid','name','group','desc','renewal','signup','status','status_class','panels','productidentifier','productpluginlogo'],
        proxy:          new Ext.data.HttpProxy({
            url: 'index.php?fuse=clients&action=adminGetUserPackages&filteroncustomer=1'
        })
    });

    packageemanager.currentProductStore.on('load',function() {
        if (packageemanager.currentProductStore.data.length > 0) {
            packageemanager.productData = packageemanager.currentProductStore.getAt(0).data;
        } else {
            alert("error: didn't return product");
        }

        //show settings panels that are available
        //hide those that are not
        $(".vtab").each(function (index, domEle) {
            domEle.style.display = "none";
        });

        for (var i in packageemanager.productData.panels)
        {
            if(document.getElementById('settingtype_'+packageemanager.productData.panels[i]) != undefined) {
                document.getElementById('settingtype_'+packageemanager.productData.panels[i]).style.display = "";
            }

            //lazy load any javascript for type specific settings
            if($.inArray(packageemanager.productData.panels[i],packageemanager.arrayOfLazyLoadingPanels) > -1) {
                ce.lazyLoad([relativePath+'templates/admin/views/clients/packages/sharedjs/profile_product_'+packageemanager.productData.panels[i]+'.js'], function () {});
            }
        }
        //show any image and product identifier
        document.getElementById('productidentifier').innerHTML = "#"+packageemanager.package_id+" "+packageemanager.productData.productidentifier;
        document.getElementById('productidentifier-type').innerHTML = packageemanager.productData.producttypename;
        document.getElementById('productidentifier-date').innerHTML = packageemanager.productData.dateactivated;

        if (packageemanager.productData.productpluginlogo != "") {
            document.getElementById('selectedproductlogo').style.display = "";
            document.getElementById('selectedproductlogo').src = relativePath+"plugins/"+packageemanager.productData.productpluginlogo;
        } else {
            document.getElementById('selectedproductlogo').style.display = "none";
        }

        //select product from dropdown list after we load new data
        packageemanager.productlistcombo.setValue(packageemanager.package_id);

        //TODO4.1 determine first if we need to show the options
        packageemanager.displayPluginOptions();
    });

    /**
     * Display Plugin dropdown list if the productData has a plugin
     */
    packageemanager.displayPluginOptions = function()
    {
        //determine if we have a plugin
        if( (packageemanager.productData.hasplugin) && ($('#divpluginactiondropdown').html() != "") ){
            //$('#plugindropdown').show();
            if($('#updateviaplugin').length > 0) {

                // Change the label on the update package checkbox
                if(packageemanager.productData.producttype == 3) {
                     $('#performonserver_wrapper >span').text('Update at Registrar if necessary');
                } else {
                    $('#performonserver_wrapper >span').text($('#fieldblock_updateviaplugin >dt>label').text());
                }
                if(packageemanager.productData.producttype == 2 ) {
                    $('#performonserver_wrapper').hide();
                } else {
                    $('#performonserver_wrapper').show();
                    $('#performonserver').attr('checked', true);
                    $('#accountnotonserverwarning').hide();
                }



                 // Show the hidden domain tabs
                 document.getElementById('settingtype_domaincontactinfo').style.visibility = 'visible';
                 document.getElementById('settingtype_hostrecords').style.visibility = 'visible';
                 document.getElementById('settingtype_nameservers').style.visibility = 'visible';

            } else if($('#accountnotonserver').length > 0) {
                 $('#accountnotonserverwarning >div').text($('#fieldblock_accountnotonserver >dt>label').text());
                 $('#accountnotonserverwarning').show();
                 $('#performonserver').attr('checked', false);
            } else {
                $('#performonserver_wrapper').hide();
                $('#accountnotonserverwarning').hide();
		$('#performonserver').attr('checked', false);
            }
        } else if (packageemanager.productData.hasplugin){
            plugindiv = $("<div id='plugindropdown' style='position:relative;top:-2px;display:none;'></div>");
            $('#divpluginactiondropdown').append(plugindiv);
            new Ext.Button({
                id : 'btnpluginoptionbuttons',
                renderTo: 'plugindropdown',
                defaultType: 'splitbutton',
                arrowAlign: 'right',
                text: 'Plugin Options',
                split: true,
                cls: 'x-box-text-small',
                border:false,
                menu: pluginActionMenu = new Ext.menu.Menu({
                    id: 'pluginactiondropdownmenu',
                    enableScrolling:false,
                    shadow:false
                }),
                layout: 'anchor'
            });
            if($('#updateviaplugin').length > 0) {
                $('#performonserver_wrapper >span').text($('#fieldblock_updateviaplugin >dt>label').text());
                 $('#performonserver_wrapper').show();
				 $('#performonserver').attr('checked', true);
				 $('#accountnotonserverwarning').hide();
            } else if($('#accountnotonserver').length > 0) {
                 $('#accountnotonserverwarning >div').text($('#fieldblock_accountnotonserver >dt>label').text());
                 $('#accountnotonserverwarning').show();
				 $('#performonserver').attr('checked', false);
            } else {
                $('#performonserver_wrapper').hide();
                $('#accountnotonserverwarning').hide();
				$('#performonserver').attr('checked', false);
            }
        }else {
            //check to see if dropdown plugin exists and if so remove it
            if($('#divpluginactiondropdown').html() != "") {
                $('#plugindropdown').hide();
            }
            $('#performonserver_wrapper').hide();
            $('#accountnotonserverwarning').hide();
			$('#performonserver').attr('checked', false);
        }
    };

    /* Stores used for setting types */
    packageemanager.product_store_general = new Ext.data.JsonStore({
        id :    "product_store_general",
        root :  "productFields",
        totalProperty:  'totalcount',
        baseParams: {
            productgroup_id:0,
            getCountriesValues: 1
        },
        idProperty: 'id',
        remoteSort: true,
        fields: ['id','name','fieldtype','vtype','value','width','selectfirstonblank','addrowdeletebtn','addrowdeletefnc','ischangeable','listener','isrequired','ishidden','dropdownoptions'],
        proxy:  new Ext.data.HttpProxy({
            url:  'index.php?fuse=clients&action=adminGetClientProductFields'
        })
    });

    Ext.get('btnUpdateProduct').on('click', function(){
        errors = false;
        var settingsForm = new Ext.form.BasicForm('productsettingsform');
        //loop through the html form's elements
        //checks to see if those that are Extjs objects validate
        for (var key in settingsForm.getValues()) {
            isctrl = false;
            if (Ext.type(Ext.getCmp(key))){
                isctrl = true;
            }else if(Ext.type(Ext.getCmp("ctrl"+key))){
                isctrl = true;
                key = "ctrl"+key;
            }

            if (isctrl && !Ext.getCmp(key).isValid()){
                if ($('#'+key).is(":reallyvisible")) {
                    errors = true;
                }
            }
        }
        //if errors return and show message
        if(errors) {
            Ext.Msg.alert('Failure', 'Form fields may not be submitted with invalid values');
            return;
        }

        settingsForm.submit({
            params: {
                settingtype: packageemanager.viewingSettingPanel
            },
            success: function(form, action) {
                packageemanager.entries_store.load(); //to make sure grid and dropdown update their values
                if (action.result.message!="") {
                    ce.msg(action.result.message);
                }else {
                    ce.msg("Settings Updated Successfully");
                }
                //check to see if we need to update plugin dropdown list
                if ((action.result.doActions.productpluginneedsupdating) && (packageemanager.productData.hasplugin) ){
                    packageemanager.updateAvailablePluginActions();
                }

                packageemanager.showManageSettingByType(packageemanager.activeTab);

                /*
                //we need to clear all validation issues
                for (var key in settingsForm.getValues()) {
                    if (Ext.type(Ext.getCmp(key))){
                        Ext.getCmp(key).clearInvalid();
                    }else if(Ext.type(Ext.getCmp("ctrl"+key))){
                        Ext.getCmp("ctrl"+key).clearInvalid();
                    }
                }*/

            },
            failure: function(form,action){
                Ext.Msg.alert('Action Failed',action.result.message);
            }
        });
    });





});


