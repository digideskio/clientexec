clientsList = {};
clientsList.has_scores = false;

$(document).ready(function(){

    //let's see if we pased args
    clientsList.baseParams = {
        limit: clientexec.records_per_view,
        sort: 'dateactivated',
        dir: 'desc',
        filter: viewusers.config.customer_group_id,
        filter2:  $('#clientsList-grid-filterbystatus').val()
    };

    if (viewusers.config.customsearchtype !== "") {
        clientsList.baseParams = $.extend(clientsList.baseParams,viewusers.config);
        $('.advanced-search-alert').show();
        $('.advanced-search-reset').bind('click',function(){
            window.location = "index.php?fuse=clients&controller=user&view=viewusers";
        });
    } else {
        $('.clients-main-alert').show();
    }

    jQuery.ajax({
         url: 'index.php?fuse=clients&action=haveuserswithscores&controller=users',
         success: function(result) {
            if (result.has_scores) clientsList.has_scores = true;
         },
         async:   false
    });

    clientsList.grid = new RichHTML.grid({
        el: 'clientsList-grid',
        url: 'index.php?fuse=clients&action=getusers&controller=users&comma=yes',
        baseParams: clientsList.baseParams,
        root: 'clients',
        totalProperty: 'totalcount',
        editable: true,
        columns: [{
            xtype: 'expander',
            renderOnExpand: true,
            renderer: function(value, record, el) {
                var html = '<b>' + lang('Outstanding balance') + ': </b>';
                $.ajax({
                    url: 'index.php?fuse=clients&controller=user&action=gettotalamountunpaid',
                    data: {id: record.id},
                    dataType: 'json',
                    async: false,
                    success: function(data) {
                        html += data.total;
                    }
                });
                return html;
            },
        }, {
            id:         "cb",
            dataIndex:  "id",
            xtype:      "checkbox"
        }, {
            id:         "id",
            dataIndex:	"id",
            text:		lang("Id"),
            align:		"center",
            sortable:	true,
            width:		40
        },{
            id: 'clientname',
            text: lang("Name"),
            renderer: renderName,
            align: "left",
            dataIndex : "lastname",
            sortable: true,
            hidden:  false
        },{
            id: 'clienttype',
            text: lang("Client Type"),
            dataIndex : "clienttype",
            align: 'right',
            renderer: renderClientType,
            hidden: false,
            sortable: false,
            width:  135
        },{
            id: 'clientemail',
            text: lang("Email"),
            dataIndex : "email",
            width: 220,
            hidden: false,
            sortable: true,
            align:"right",
            flex : 1
        },{
            id: 'dateactivated',
            text: lang("Activated"),
            dataIndex : "dateactivated",
            width: 100,
            hidden: true,
            sortable: true,
            align:"center",
            renderer: function (text,row) {
                return row.cleandateactivated;
            }
        },{
            id: 'kscore',
            text: lang("Score"),
            dataIndex : "plus_score",
            width: 60,
            sortable: true,
            hidden: !clientsList.has_scores,
            align:"center",
            renderer : function(value, row) {
                if (row.plus_date == null) value = lang("N/A");
                return value;
            }
        },{
            id: 'status',
            text: lang("Status"),
            dataIndex : "status",
            renderer: renderStatus,
            width: 100,
            hidden: false,
            sortable: true,
            align:"center"
        }].concat(viewusers.config.customfields)
    });

    clientsList.grid.render();

    clientsList.addUserWindow = new RichHTML.window({
        width: '260',
        grid: clientsList.grid,
    	url: 'index.php?fuse=clients&controller=user&view=saveclientform',
    	actionUrl: 'index.php?fuse=clients&controller=user&action=savenewclient',
    	showSubmit: true,
    	title: lang("Add Client"),
        onSubmit: function (data) {
            ce.parseResponse(data, false);
        }
    });


    // **** start click binding
    $('#clientsList-grid-filter').change(function(){
    	clientsList.grid.reload({params:{start:0,limit:$(this).val()}});
    });

    $('#clientsList-grid-filterbystatus').change(function(){
        if ($(this).val() != 99) {
            $('#validateCC').hide();
        } else {
            $('#validateCC').show();
        }
    	clientsList.grid.reload({params:{start:0,filter2:$(this).val()}});
    });

    $('#clientsList-grid-groupby').change(function(){
    	clientsList.grid.reload({params:{start:0,filter:$(this).val()}});
    });

    $('#adduser').click(function(){
        clientsList.addUserWindow.show();
    });


    $('#delUser').click(function () {
        if ($(this).attr('disabled')) { return false; }
    	RichHTML.msgBox(lang('Do you want to use the respective server plugins to delete all these users?'), {
            type:"confirm",
        }, function(result) {
            if ( result.btn === 'cancel' ) {
                clientsList.grid.reload({params:{start:0}});
                return;
            }

            if ( result.btn === 'yes' ) {
                result = 1;
            } else {
                result = 0;
            }

            $.post("index.php?fuse=clients&controller=user&action=deleteclient&deletewithplugin=" + result, { deleteid: clientsList.grid.getSelectedRowIds() }, function(data) {
                clientsList.grid.reload({params:{start:0}});
            });
        });
    });

    $('#validateCC').click(function () {
        if ($(this).attr('disabled')) { return false; }
        RichHTML.msgBox('Enter your passphrase:',
            {type:'prompt',password:true},
            function(result){
            if(result.btn === "ok") {
                RichHTML.mask();
                $.ajax({
                    type: 'POST',
                    url: 'index.php?fuse=billing&controller=creditcard&action=validateccnumber',
                    success: function(xhr) {
                        json = ce.parseResponse(xhr);
                        RichHTML.unMask();
                        if (json.success) {
                            ce.msg(lang('Successfully validated credit cards'));
                            clientsList.grid.reload({params:{start:0}});
                        }
                    },
                    data: {
                    passphrase: result.elements.value,
                    customerids: clientsList.grid.getSelectedRowIds()
                    }
                });
            }
        });
    });



    clientsList.ccValidateWindow = new RichHTML.window({
        height: '75',
        grid: clientsList.grid,
        el: 'validate-cc-form',
    	actionUrl: 'index.php?fuse=billing&controller=creditcard&action=validateccnumber',
    	showSubmit: true,
    	title: lang("Validate CC")
    });

    $(clientsList.grid).bind({
        "rowselect": function(event,data) {
            if (data.totalSelected > 0) {
                $('#delUser').removeAttr('disabled');
                $('#validateCC').removeAttr('disabled');
            } else {
                $('#delUser').attr('disabled','disabled');
                $('#validateCC').attr('disabled','disabled');
            }
        }
    });
});

function renderName(text, row){

    var name = ce.htmlspecialchars(row.fullname);
    if ( row.isOrganization == 1 ) {
        name = ce.htmlspecialchars(row.firstname) +
            " " + ce.htmlspecialchars(row.lastname) +
            " - " + name;
    }
    if($.trim(name) == "") name = "<i style='color:#888;'>"+lang("No name entered")+"</i>";
    name = "<span>"+name+"</span>";
    if ( row.clienttypebgcolor == "" ) {
        url = String.format('<a href="index.php?fuse=clients&controller=userprofile&view=profilecontact&frmClientID={1}">{0}</a>', name, row.id);
    } else {
        url = String.format('<span class="customergroupstyle"><a style="background:{2} !important;" href="index.php?fuse=clients&controller=userprofile&view=profilecontact&frmClientID={1}">{0}</a></span>', name, row.id, row.clienttypebgcolor);
    }

    return url;
}

function renderClientType(text, row){
    return String.format("{0}",row.clienttypename);
}

function renderStatus(text, row){
    return String.format("{0}",row.statusname);
}
