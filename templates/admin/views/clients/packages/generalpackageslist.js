var packages = packages || {};

$(document).ready(function() {
    packages.grid = new RichHTML.grid({
        el: 'packages-grid',
        url: 'index.php?fuse=clients&action=getpackages&controller=packages',
        baseParams: { limit: clientexec.records_per_view, sort: 'id', dir: 'asc', filter: 'active', type: 0},
        root: 'results',
        editable: true,
        columns: [{
                id: "id",
                dataIndex: "id",
                text: lang("ID"),
                sortable: true,
                width: 60
            },{
                id: "customer",
                text: lang("Customer"),
                dataIndex: "customer",
                sortable: true,
                renderer: function(text, row) {
                    return "<a href='index.php?fuse=clients&controller=userprofile&view=profilecontact&frmClientID=" + row.customerid + "'>" + row.customer + "</a>";
                }

            },{
                id: "package",
                text: lang("Package"),
                dataIndex: "name",
                sortable: true,
                renderer: function(text, row) {
                    return "<a href='index.php?fuse=clients&controller=userprofile&view=profileproduct&id=" + row.productid + "&frmClientID=" + row.customerid + "'>" + row.productname + "</a>";
                }

            },{
                id: "group",
                text: lang("Group"),
                dataIndex: "productgroupname",
                sortable: true

            },{
                id: "status",
                text: lang("Status"),
                dataIndex: "status",
                sortable: true,
                align: 'center',
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
});
