/*
 * Ext JS Library 3.0 RC1
 * Copyright(c) 2006-2009, Ext JS, LLC.
 * licensing@extjs.com
 *
 * http://extjs.com/license
 */

Ext.onReady(function(){

    gridstore = new Ext.data.JsonStore({
        id: "gridstore",
        root: 'results',
        totalProperty: 'totalcount',
        idProperty: 'id',
        remoteSort: true,
        fields: [
                'id','customer','customerid','name', 'productid', 'productgroupname', 'status', 'productname'
        ],
        sortInfo: { field: "id", direction: "ASC" },
        proxy: new Ext.data.HttpProxy({
                url: 'index.php?fuse=clients&action=adminGetUserPackages&filter=0&domainsfilter=1'
        })
    });
   
    // Function to put together the link for the open window
    function renderCustomerName(value,p,record){
        var link = "<a href='index.php?fuse=clients&controller=userprofile&view=profilecontact&frmClientID=" + record.data.customerid + "'>" + record.data.customer + "</a>";
        return link;
    }
    
    function renderPackageName(value, p, record) {
        var link = "<a href='index.php?fuse=clients&controller=userprofile&view=profileproducts&packageid=" + record.data.productid + "'>" + record.data.productname + "</a>";
        return link;
    }

    var paginationbar = new Ext.PagingToolbar({
        id:'domaingridpagingbar',
        store: gridstore,
        emptyMsg : "",
        listeners:{
       'change': function(bb,data){

                if(data.total == 0) { var addBy = 0;} else { var addBy = 1;}
                from = (data.activePage * bb.pageSize) - bb.pageSize + addBy;
                to = from+ bb.pageSize - 1;
                if(to>data.total) to = data.total;

            document.getElementById('domaingridpagingbar').style.display = ( data.pages > 1 ) ? '' : 'none';
            document.getElementById('domainlistlabel').innerHTML = String.format(lang('Displaying packages') +' {0} - {1}' + ' of ' + '{2}',from,to,data.total);
      }}
    });
    
    var grid = new Ext.grid.GridPanel({
        el:'domains-grid',
        autoHeight:true,
        title:'',
        store: gridstore,
        trackMouseOver:true,
        disableSelection:true,
        loadMask: true,
        // grid columns
        columns:[
        {
            id: 'domainsid',
            header: lang("ID"),
            width: 20,
            sortable: true,
            dataIndex : "id",
            align:"left"
        },{
            id: 'customer',
            header: lang("Customer"),
            dataIndex : "customer",
            renderer: renderCustomerName,
            sortable: true,
            width: 120
        },{
            id: 'name',
            header: lang('Package'),
            dataIndex: 'name',
            sortable: true,
            renderer: renderPackageName,
            width:150
        },{
            id: 'productgroupname',
            header: lang('Group'),
            dataIndex: 'productgroupname',
            sortable: true,
            width:150
        }, {
            id: 'status',
            header: lang('Status'),
            dataIndex: 'status',
            sortable: true,
            width:75
        }

],
        stripeRows: true,
        // customize view config
        viewConfig: {
            forceFit:true,
            enableRowBody:true,
            emptyText: lang("There are no packages available to view at this time"),
            scrollOffset:1
        },
        // paging bar on the bottom
        bbar: paginationbar,
        buttonAlign:'left'
    });

    var storeItem = Ext.data.Record.create([{name:'myId',type:'string'},{name:'displayText',type:'string'}]);
    var domainsfiltercombo = new Ext.form.ComboBox({
        triggerAction: 'all',
        lazyRender:true,
        editable:false,
        value:'Active Packages',
        mode: 'local',
        store: new Ext.data.ArrayStore({
            idIndex: 0,
            fields: [
                'myId',
                'displayText',
                'type',
                'private'
            ]
        }),
        valueField: 'myId',
        width: 220,
        displayField: 'displayText',
        applyTo: 'domainsfilter',
        // all of your config options
        listeners:{
             'select': function(cb,record){

                FilterGrid(record.data.myId);
             }
        }

    });
    

    grid.render();
    paginationbar.pageSize = 10;

    // trigger the data store load
    gridstore.setBaseParam('limit', 10);
    gridstore.load({params:{start:0}});
    
    function FilterGrid(filter){
        //these are set in the viewusers.tpl
	    //if(customerid!=null) gridstore.setBaseParam('customerid', customerid);
        if(filter!=null) gridstore.setBaseParam('domainsfilter', filter);

        gridstore.load({params:{start:0,limit:15}});
    }

    function updateButtonsWithContent(data){

        if(data.type == "filter"
           )
        {
            if(data.private == undefined){
                data.private = 0;
            }

            var comboitem = new storeItem({myId:data.id,displayText:data.name,type:data.type, private:data.private});
            domainsfiltercombo.getStore().insert(
                domainsfiltercombo.getStore().getCount(),
                comboitem
            );
        }

    }

    function PopulateDomainsFilters(){
        Ext.Ajax.request({
           url: 'index.php?fuse=clients&action=getPackagesFilters',
           success: function(xhr) {
               
                        //clear the items from our combobox
                        domainsfiltercombo.getStore().removeAll();
                        //go through our options and add to the UI
                        var data = Ext.util.JSON.decode(xhr.responseText);
                        //data.options.forEach(updateButtonsWithContent);
                        for ( var i in data.options )
                        {
                            updateButtonsWithContent( data.options[i] );
                        }

                    },
           failure: function() { },
           params: { foo: 'bar' }
        });
    }

 new Ext.form.ComboBox({
        triggerAction: 'all',
        lazyRender:true,
        editable:false,
        value: paginationbar.pageSize,
        mode: 'local',
        store: new Ext.data.ArrayStore({
            id: 0,
            fields: [
                'myId',
                'displayText'
            ],
            data: [[10, '10'], [25, '25'] ,[50, '50'], [150,'150'],[300,'300']]
        }),
        valueField: 'myId',
        width: 50,
        displayField: 'displayText',
        applyTo: 'local-states-with-qtip',
        // all of your config options
        listeners:{
             'select': function(cb,record){
                gridstore.setBaseParam('limit', record.data.myId);
                paginationbar.pageSize = record.data.myId;
                gridstore.load({params:{start:0}});
             }
        }
    });

    PopulateDomainsFilters();
});