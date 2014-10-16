var packages = packages || {};

$(document).ready(function() {
    packages.grid = new RichHTML.grid({
        el: 'packages-grid',
        url: 'index.php?fuse=clients&action=getpackages&controller=packages',
        baseParams: { limit: clientexec.records_per_view, sort: 'id', dir: 'asc', filter: 'active', type: 2},
        root: 'results',
        editable: true,
        columns: [{
                id: "domainname",
                text: lang("Domain Name"),
                dataIndex: "domainname",
                sortable: true,
                renderer: function(text, row) {
                    return "<a href='index.php?fuse=clients&controller=userprofile&view=profileproduct&id=" +row.id + "&frmClientID=" + row.customerid + "'>"+row.domainname+"</a><br/> Product ID: " + row.id;
                }
            },{
                id: 'type',
                text: lang('Type'),
                dataIndex: 'productname',
                sortable: true,
                renderer: function(text, row) {
                    return row.productname + '<br/>(' + row.registrar + ')';
                },
                width: 250
            },{
                id: "customer",
                text: lang("Customer"),
                dataIndex: "customer",
                sortable: true,
                renderer: function(text, row, el) {
                    el.addStyle = 'vertical-align: top';
                    return "<a href='index.php?fuse=clients&controller=userprofile&view=profilecontact&frmClientID=" + row.customerid + "'>" + row.customer + "</a>";
                },
                width: 150
            },{
                id: "expires",
                text: lang("Expiration Date"),
                dataIndex: "expires",
                sortable: true,
                width: 100,
                renderer: function(text, row, el) {
                    el.addStyle = 'vertical-align: top';
                    return text;
                }
            },{
                id: "status",
                text: lang("Status"),
                dataIndex: "status",
                sortable: true,
                width: 75,
                renderer: function(text, row, el) {
                    el.addStyle = 'vertical-align: top';
                    return text;
                }
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
});
