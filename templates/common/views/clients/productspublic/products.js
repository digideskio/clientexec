var products = {
    removedActionCol: false
};

$(document).ready(function() {
    products.grid = new RichHTML.grid({
        el: 'products-grid',
        url: 'index.php?fuse=clients&controller=products&action=getproducts',
        baseParams: { limit: 15, sort: 'name', dir: 'asc'},
        root: 'data',
        columns: [{
            id: "name",
            dataIndex: "name",
            text: lang("Package"),
            align: "left",
            sortable: true,
            renderer : function (val, row) {
                //index.php?fuse=clients&controller=products&view=product
                return "<a href='index.php?fuse=clients&controller=products&view=product&id="+row.id+"'>"+ce.htmlspecialchars(val)+"</a>";
            }
        },{
            id: 'nextDueDate',
            text: lang('Next Due Date'),
            dataIndex: 'nextDueDate',
            width: 125,
            align:"center",
            sortable: true,
            renderer: function(val, row) {
                if (val == "") val = lang("Not Applicable");
                return val;
            }
        },{
            id: 'term',
            text: lang('Billing Cycle'),
            dataIndex: 'term',
            width: 130,
            align:"center",
            sortable: true,
            renderer: function (val, row) {
                return row.term + " <br/> " + row.price;
            }
        },{
            id: 'status',
            text: lang('Status'),
            dataIndex: 'status',
            width: 150,
            align:"center",
            sortable: true,
        },{
            id: 'actions',
            text: lang('Actions'),
            dataIndex: 'actions',
            width: 80,
            align:"center",
            sortable: false,
        }]
    });

    // Each row might have a different set of custom fields (e.g. products from different packages configured
    // with different custom fields), so after we receive the data the grid page is reloaded to account for new
    // custom fields found in the data.
    $(products.grid).bind({
        'load': function(evt, data) {
            var reload = false;
            $.each(data.jsonData.data, function(index, row) {
                if (!row.actions && !products.removedActionCol) {
                    products.grid.columns.splice(products.grid.columns.length - 2, 1);
                    products.removedActionCol = true;
                    reload = true;
                }

                $.each(row.customfields, function(name, value) {
                    var already = false;
                    $.each(products.grid.columns, function(index, col) {
                        if (col.dataIndex == 'customfield_' + name) {
                            already = true;
                            return false;
                        }
                    });

                    if (!already) {
                        var lastCol = products.grid.columns.length - (products.removedActionCol? 1 : 2);
                        products.grid.columns.splice(lastCol, 0, {
                            text: lang(name),
                            dataIndex: 'customfield_' + name,
                            align:"center",
                            sortable: false,
                            renderer: function(val, row) {
                                return row.customfields[name];
                            }
                        });
                        reload = true;
                    }
                });
            });

            if (reload) {
                products.grid.reload();
            }
        }
    });

    products.grid.render();

});
