var packages = packages || {};

$(document).ready(function() {
    packages.grid = new RichHTML.grid({
        el: 'packages-grid',
        url: 'index.php?fuse=clients&action=getpackages&controller=packages',
        baseParams: { limit: clientexec.records_per_view, sort: 'id', dir: 'asc', filter: 'active', type: 3},
        editable: true,
        root: 'results',
        columns: [{
                id:         "cb",
                dataIndex:  "id",
                xtype:      "checkbox"
            },{
                id: "id",
                dataIndex: "id",
                text: lang("ID"),
                sortable: true,
                width: 40
            },{
                id: "domainname",
                text: lang("Domain Name"),
                dataIndex: "domainname",
                sortable: true,
                renderer: function(text, row) {
                    if ( row.cycle_issue == true ) {
                        return "<a href='index.php?fuse=clients&controller=userprofile&view=profileproduct&id=" +row.id + "&frmClientID=" + row.customerid + "'>"+row.domainname+"</a> <a href='index.php?fuse=clients&controller=userprofile&view=profileproducts&frmClientID=" + row.customerid + "&packageid=" +row.id + "&tab=billing&frmClientID=" + row.customerid + "'><img style='padding-left:2px;margin-bottom:-3px;' title='" + lang('Invalid payment term for domain') + "' src='../templates/admin/images/icon_alert.gif' /></a>";
                    } else {
                        return "<a href='index.php?fuse=clients&controller=userprofile&view=profileproduct&id=" +row.id + "&frmClientID=" + row.customerid + "'>"+row.domainname+"</a>";
                    }
                }
            },{
                id: "customer",
                text: lang("Customer"),
                dataIndex: "customer",
                sortable: true,
                renderer: function(text, row) {
                    return "<a href='index.php?fuse=clients&controller=userprofile&view=profilecontact&frmClientID=" + row.customerid + "'>" + row.customer + "</a>";
                }
            },{
                id: "expires",
                text: lang("Expiration Date"),
                dataIndex: "expires",
                sortable: true,
                width: 125
            },{
                id: "period",
                text: lang("Period"),
                dataIndex: "period",
                sortable: false,
                width: 75
            },{
                id: "autorenew",
                text: lang("Auto Renew"),
                dataIndex: "autorenew",
                sortable: false,
                width: 75
            },{
                id: "registrar",
                text: lang("Registrar"),
                dataIndex: "registrar",
                sortable: true,
                width: 100
            },{
                id: "status",
                text: lang("Status"),
                dataIndex: "status",
                sortable: true,
                width: 100
            }
        ].concat(packages.config.customFields)
    });
    packages.grid.render();

    $('#packages-grid-filter').change(function(){
        packages.grid.reload({params:{start:0, limit:$(this).val()}});
    });

    $('#packages-grid-package-filter').change(function(){
        packages.grid.reload({params:{start:0, filter:$(this).val()}});
    });

    $(packages.grid).bind({
        "rowselect": function(event,data) {
            if (data.totalSelected > 0) {
                $('#btn-send-reminder').removeAttr('disabled');
            } else {
                $('#btn-send-reminder').attr('disabled','disabled');
            }
        }
    });

    $('#btn-send-reminder').click(function () {
        $.post("index.php?action=senddomainreminder&controller=packages&fuse=clients", {
            ids: packages.grid.getSelectedRowIds()
        },
        function(data){
            ce.parseResponse(data);
        });
    });
});
