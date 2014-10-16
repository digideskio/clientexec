var cannedresponses = {};

$(document).ready(function() {
    // ** grid definition
    cannedresponses.grid = new RichHTML.grid({
        el: 'CannedResponses-grid',
        url: 'index.php?fuse=support&controller=cannedresponse&action=getcannedresponses',
        baseParams: { limit: clientexec.records_per_view, sort: 'name', dir: 'asc'},
        columns: [{
                xtype:      "expander",
	            dataIndex:  "response",
	            renderer:	function(text) {
	            	return nl2br(html_entity_decode(text));
	            }
            },{
                id:         "cb",
                dataIndex:  "id",
                xtype:      "checkbox"
            }, {
            	id: 		"id",
            	dataIndex:	"id",
            	text:		lang("Id"),
            	align:		"center",
            	sortable:	true,
            	width:		60
            },{
                id:         "name",
                text:     	lang("Name"),
                dataIndex:  "name",
                sortable: true,
                renderer:   function(text,row) {
                    text = ce.htmlspecialchars(text);
                	return (trim(text) === "") ? "<span style='color:#cdcdcd'><a onclick='cannedresponses.window.show({params:{id:"+row.id+"}});'>not named</a></span>" : "<a onclick='cannedresponses.window.show({params:{id:"+row.id+"}});'>"+text+"</a>";
                },
                flex: 1
            },{
                id:         "username",
                text:     	lang("Creater"),
                width:      150,
                align:		"center",
                dataIndex:  "username",
                sortable: 	true
            }
        ]
    });
    cannedresponses.grid.render();

    // ** define window object to handle edits and loads
    cannedresponses.window = new RichHTML.window({
    	height: '195',
        grid: cannedresponses.grid,
    	url: 'index.php?fuse=support&view=cannedresponse&controller=cannedresponse',
    	actionUrl: 'index.php?action=savecannedresponse&controller=cannedresponse&fuse=support',
    	showSubmit: true,
    	title: lang("Add Canned Reply")
    });


    // **** start click binding
    $('#CannedResponses-grid-filter').change(function(){
    	cannedresponses.grid.reload({params:{start:0,limit:$(this).val()}});
    });

    $('#CannedResponses-grid-filterbystatus').change(function(){
    	cannedresponses.grid.reload({params:{start:0,filter:$(this).val()}});
    });

    $('#addresponse').click(function(){
        cannedresponses.window.show();
    });

    // **** listeners to grid
    $(cannedresponses.grid).bind({
        "rowselect": function(event,data) {
            if (data.totalSelected > 0) {
                $('#delResponse').removeAttr('disabled');
            } else {
                $('#delResponse').attr('disabled','disabled');
            }
        }
    });

    // **** lets bind our buttons
    $('#delResponse').click(function () {
        if ($(this).attr('disabled')) {
            return false;
        }
        RichHTML.msgBox(lang('Are you sure you want to delete the selected response(s)'),
        {
            type:"confirm"
        }, function(result) {
            if(result.btn === "yes") {
                $.post("index.php?action=deleteresponses&controller=cannedresponse&fuse=support", {
                    ids:cannedresponses.grid.getSelectedRowIds()
                    },
                function(data){
                    cannedresponses.grid.reload({
                        params:{
                            start:0
                        }
                    });
                });
            }
        });
    });
});
