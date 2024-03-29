var viewinvoices = {};
viewinvoices.pricetypecombovalue = 0;
viewinvoices.AcceptCCNumber = false;

viewinvoices.addinvoicedescription = function(filterid)
{
    filterid = parseInt(filterid);
    var desc = "";
    switch(filterid) {
        case 0:
            desc =  lang("Viewing all invoices that are passed their due date yet not paid.");
            break;
        case 1:
            desc =  lang("Viewing all invoices that have not been paid.");
            break;
        case 2:
            desc =  lang("Viewing all invoices.");
            break;
        case 3:
            desc =  lang("Viewing invoices for a filtered user.");
            break;
        case 4:
            desc =  lang("Viewing pending invoices. Invoices that have been sent to merchant but not cleared.  i.e. eChecks");
            break;
        case 5:
            desc =  lang("Viewing all draft invoices. These invoices are not ready to be processed.");
            break;
    }

    if (desc != "") $('.filter-description').text(desc);

}


$(document).ready(function() {
    RichHTML.debugLvl = 0;

    richgrid = new RichHTML.grid({
        el: gridEl,
        width: "100%",
        editable : true,
        url: 'index.php?fuse=billing&controller=invoice&action=getinvoices',
        baseParams: {
            customerid: $('#invoice-userid').val(),
            sort: 'id',
            dir : 'desc', // or 'desc'
            limit: clientexec.records_per_view,
            invoicefilter: ($('#invoice-userid').val() == "") ? invoicelist_filter : 2,
            moduleview: "billing invoice list"
        },
        root: 'invoices',
        totalProperty : 'totalcount',
        columns: [{
            text: "",
            xtype: "expander",
            escapeHTML: true,
            renderer:renderExpander,
            renderOnExpand: true
        },{
            text:     	"",
            dataIndex:  "id",
            xtype:      "checkbox"
        },{
            text:     	"Invoice #",
            dataIndex:  "id",
            align:      "center",
            width:      85,
            sortable: true,
            renderer: function(text,row, el) {
                if ( viewinvoices.viewingFromProfile === true ) {
                    desc = "<a href='index.php?controller=invoice&fuse=billing&frmClientID="+row.customerid+"&view=invoice&invoiceid="+row.id+"&profile=1'>#"+text+"</a>";
                } else {
                    desc = "<a href='index.php?controller=invoice&fuse=billing&frmClientID="+row.customerid+"&view=invoice&invoiceid="+row.id+"'>#"+text+"</a>";
                }
                return desc + "  <span class='invoicepdflink'><a href='index.php?sessionHash="+gHash+"&fuse=billing&controller=invoice&action=generatepdfinvoice&invoiceid=" + row.invoiceid + "' target='_blank'><img class='pdfimage' src='../templates/admin/images/document-pdf-text.png' border='0' data-toggle='tooltip' title='"+ lang('View PDF Invoice') +"' /></a></span>";
            }
        },{
            text:       lang("Due"),
            dataIndex:  "billdate",
            align:      "right",
            width:      85,
            sortable: true,
            flex : 1,
            renderer: ce.dateRenderer
        },{
            text:     	"Customer Name",
            dataIndex:  "customername",
            hidden: (gView == "profileinvoices") ? true : false,
            align: 		"left",
            flex : 1,
            renderer: function(value,record) {
                value = ce.htmlspecialchars(value);
                if ( viewinvoices.viewingFromProfile === true ) {
                    // we're viewing from the users profile, so only return their name
                    return value;
                }
                var filter = "";
                if ($('#invoice-userid').val() == "") {
                    filter = '&nbsp;&nbsp;<span class="filter-invoice-user link ico-small" data-userid="'+record.customerid+'" data-icon="F"></span>';
                }
                return '<a href="index.php?fuse=clients&controller=userprofile&view=profilecontact&frmClientID=' + record.customerid + '">' + value + '</a>' + filter;
            }
        },
        {
            text:   "Gateway",
            width:	   (gView == "profileinvoices") ? "" : "145",
            dataIndex: "paymenttype",
            sortable: true,
            hidden:true,
            align: "center",
            renderer: function(value,record) {
                var tSubscriptionId = '';
                if ( record.subscriptionid != null && record.subscriptionid != "" ) {
                   value = record.paymenttype+'<br/> <span style="font-size:9px;">'+record.subscriptionid+'</span>';
                } else {
                    value = record.paymenttype;
                }

                return value;
            }
        },
        {
            text:   "Pmt Reference",
            width:	   "100",
            dataIndex: "billpmtref",
            sortable: true,
            hidden:true,
            align: "center",
            renderer: function(value,record) {
                value = record.billpmtref;
                return value;
            }
        },{
            text: lang("Amount"),
            width: 83,
            dataIndex: 'balancedue',
            sortable: true,
            hidden: (gView == "profileinvoices") ? false : true,
            align:"right",
            renderer : function (val, row) {
                var font_class = "";
                var due = row.simplebalancedue;
                if (due.length >= 18) {
                    font_class = "xxlong-currency";
                } else if (due.length >= 15) {
                    font_class = "xlong-currency";
                } else if (due.length >= 13) {
                    font_class = "long-currency";
                }
                return "<span class='"+font_class+"'>"+val+"</span>";
            }
        },{
            text: lang("Total"),
            width: 83,
            dataIndex: 'amount',
            sortable: true,
            align:"right",
            renderer : function (val, row) {
                var font_class = "";
                var due = row.simplebalancedue;
                if (due.length >= 18) {
                    font_class = "xxlong-currency";
                } else if (due.length >= 15) {
                    font_class = "xlong-currency";
                } else if (due.length >= 13) {
                    font_class = "long-currency";
                }
                return "<span class='"+font_class+"'>"+val+"</span>";
            }
        },
        {
            text:	"Status",
            width: 70,
            dataIndex: "billstatus",
            align: "center",
            escapeHTML: false,
            renderer: renderStatus
        }
        ]
    });

    richgrid.render();

    function renderExpander(value,record,el){
        $.ajax({
            url: 'index.php?fuse=billing&controller=invoice&action=getstyledinvoicetransactions',
            dataType: 'json',
            async: false,
            data: {
                invoiceid:record.invoiceid
            },
            success: function(data) {
                html = data.invoicetransactions;
            }
        });
        return html;
    }

    function renderStatus(value,record, el){
        if(record.statusenum == -2){
            el.addClass = "invoiceoverdue";
        }else if (record.statusenum === 0){
        }else if(record.statusenum === 1){
            el.addClass = "invoicepaid";
        }else if (record.statusenum === -1){
            el.addClass = "invoicevoidrefund";
        }

        return value;
        //return String.format("{0}",value);
    }

    //GLOBAL BINDS FOR OUR INSTALLATION
    $(richgrid).bind({
        "load" : function(event,data) {
            viewinvoices.disableButtons();
            $('span.filter-invoice-user').click(function() {
                var userid = $(this).attr('data-userid');
                $('input#invoice-userid').val(userid);
                viewinvoices.postactionsfilterbyuserid(userid);
            });
        },
        "rowselect": function(event,data) {
            viewinvoices.disableButtons();
            if (data.totalSelected > 0) {
                viewinvoices.enableButtons();
            } else {
                viewinvoices.disableButtons();
            }
        }
    });

    $('#invoicelist-grid-filter').change(function(){
        richgrid.reload({
            params:{
                "start":0,
                "limit":$(this).val()
            }
        });
    });

    $('#invoice-userid').keydown(function(e){
        if (e.keyCode === 13) {
            if (trim($(this).val()) === ""){
                RichHTML.msgBox(lang('User id field may not be left blank'), {type:"error"});
                return false;
            } else {
                alert('here');
                viewinvoices.addinvoicedescription(3);
                viewinvoices.postactionsfilterbyuserid($(this).val());
            }
        }
        return true;
    });

    $('#invoicelist-grid-filterbystatus').change(function(){

        if ($(this).val() == "3") {
            $('#invoice-userid').val('');
            $('#td-for-userid').show();
        } else {
            viewinvoices.addinvoicedescription($(this).val());
            $('#td-for-userid').hide();
            $('#viewing-invoices-text').text(lang("Viewing Invoices"));
            richgrid.reload({
                params:{
                    "start":0,
                    "customerid": $('#invoice-userid').val(),
                    "invoicefilter":$(this).val()
                }
            });
        }
    });

    $('div#invoicelist-grid-buttons a.btn:not(.dropdown-toggle), div#invoicelist-grid-buttons ul.dropdown-menu li').click(function(button){
        if ( $(this).attr('disabled') ) {
            return;
        }

        richgrid.disable();
        $('span.btn-group').removeClass('open');

        var id = $(this).attr('data-actionname');

        if(id == "inv-markpaid"){
            RichHTML.msgBox(lang("Do you want to send a receipt?"),{type:'confirm'},
                function(ret) {
                    var sendReceipt = false;
                    if (ret.btn == "yes") {
                        sendReceipt = true;
                    } else if(ret.btn == "cancel") {
                        richgrid.enable();
                        return;
                    }
                    viewinvoices.performaction(id,{sendreceipt:sendReceipt});
                    return;
                }
            );
        } else if(id == "inv-deleteinvoices"){
            RichHTML.msgBox(lang("Are sure you want to delete the selected invoice(s)."),{type:'yesno'},
                function(ret) {
                    if(ret.btn == "no") {
                        richgrid.enable();
                        return;
                    }
                    viewinvoices.performaction(id);
                });

        } else if(id == "inv-varpayment"){

            var balancedue = richgrid.getSelectedRowData()[0].simplebalancedue;
            RichHTML.msgBox('',
                {
                    type:'prompt',
                    content: 'Balance Due: '+balancedue+'<br/><input type="text" name="paymentamount" class="required float" placeholder="'+lang("Amount")+'" /><input type="text" name="checknum" placeholder="'+lang("Pmt. Reference")+'" />'
                },
                function(ret){
                    if (ret.btn == "cancel") {
                        richgrid.enable();
                        return;
                    } else {
                        viewinvoices.performaction(id,ret.elements);
                    }
                }
            );

        } else if(id == "inv-process"){
            var selectedRowData = richgrid.getSelectedRowData();
            var arrayLength = selectedRowData.length;
            var askAboutCharge = false;
            for (var idx = 0; idx < arrayLength; idx++) {
                if(selectedRowData[idx].canbechargedtoday == 0){
                    askAboutCharge = true;
                    break;
                }
            }

            if(askAboutCharge){
                RichHTML.msgBox(lang(lang("Some invoices are not yet on their due date. Are you sure you want to proceed?")),
                    {type:'yesno'},function(result){
                       if(result.btn === "yes") {
                            //viewinvoices.AcceptCCNumber = false;
                            if (viewinvoices.AcceptCCNumber) {

                                RichHTML.msgBox(lang('Enter your passphrase:'),
                                    {type:'prompt',password:true},
                                    function(result){
                                        if(result.btn === "ok") {
                                            viewinvoices.performaction(id,{passphrase:result.elements.value,acceptccnumber:viewinvoices.AcceptCCNumber});
                                        } else {
                                            richgrid.enable();
                                        }
                                    }
                                );
                            } else {
                                RichHTML.msgBox(lang(lang("Are you sure you want to process the selected account(s)?")),
                                    {type:'yesno'},function(result){
                                       if(result.btn === "yes") {
                                            viewinvoices.performaction(id,{acceptccnumber:viewinvoices.AcceptCCNumber});
                                        } else {
                                            richgrid.enable();
                                        }
                                });
                            }
                        } else {
                            richgrid.enable();
                        }
                });
            } else {
                //viewinvoices.AcceptCCNumber = false;
                if (viewinvoices.AcceptCCNumber) {

                    RichHTML.msgBox(lang('Enter your passphrase:'),
                        {type:'prompt',password:true},
                        function(result){
                            if(result.btn === "ok") {
                                viewinvoices.performaction(id,{passphrase:result.elements.value,acceptccnumber:viewinvoices.AcceptCCNumber});
                            } else {
                                richgrid.enable();
                            }
                        }
                    );
                } else {
                    RichHTML.msgBox(lang(lang("Are you sure you want to process the selected account(s)?")),
                        {type:'yesno'},function(result){
                           if(result.btn === "yes") {
                                viewinvoices.performaction(id,{acceptccnumber:viewinvoices.AcceptCCNumber});
                            } else {
                                richgrid.enable();
                            }
                    });
                }
            }
        } else {
            //all other actions do not need confirmations or prompts
            viewinvoices.performaction(id);
        }

    });

    viewinvoices.performaction = function(id,args) {

        var data = {
                items:          richgrid.getSelectedRowIds(),
                itemstype:      'invoices',
                actionbutton:   id
            };

        $.extend(data,args);

        $.ajax({
            url: "index.php?fuse=billing&controller=invoice&action=actoninvoice",
            type: 'POST',
            data:  data,
            success:  function(xhr){
                richgrid.reload();
                ce.parseResponse(xhr);
                if (typeof profile !== "undefined") {
                    setTimeout(function() {
                        profile.get_counts();
                    },1000);
                }
            }
        });
    };

    viewinvoices.enableButtons = function() {

        $.ajax({
           url: "index.php?fuse=billing&controller=invoice&action=getinvoicebuttons",
           data: {invoices: richgrid.getSelectedRowIds()},
           success: function(data) {

               viewinvoices.AcceptCCNumber = data.buttons.acceptccnumber;

               $.each(data.buttons,function(name,val){
                   if (val) {
                       $('div#invoicelist-grid-buttons li[data-actionname="inv-'+name+'"]').show();
                       $('div#invoicelist-grid-buttons a[data-actionname="inv-'+name+'"]:not(.btn-group a)').show();
                   } else {
                       $('div#invoicelist-grid-buttons li[data-actionname="inv-'+name+'"]').hide();
                       $('div#invoicelist-grid-buttons a[data-actionname="inv-'+name+'"]:not(.btn-group a)').hide();
                   }
               });

               //if no options are available for the btngroup then hide it
               //this code hides all group buttons that do not have child elements
               //then sets the name and action of the btn to that of the top most option
               $('div#invoicelist-grid-buttons span.btn-group').each(function(k,v) {
                   var li_filter = $(this).find('ul.dropdown-menu li[data-actionname]').filter(function() { return $(this).css("display") != "none"; });
                   if (li_filter.length == 0) {
                       $(this).hide();
                   } else {
                       $(this).show();
                   }
                   //lets make the top option the main option
                   $(this).find('a.btn:not(.dropdown-toggle)').attr('data-actionname',li_filter.first().attr('data-actionname'));
                   $(this).find('a.btn:not(.dropdown-toggle)').text(li_filter.first().text());
               });

               $('#invoicelist-grid-buttons .btn').removeAttr('disabled');

           }
        });
    };

    viewinvoices.disableButtons = function() {
        $('#invoicelist-grid-buttons').show();
        $('#invoicelist-grid-buttons .btn').attr('disabled','disabled');
    };

    viewinvoices.postactionsfilterbyuserid = function(userid) {

        if ( typeof viewinvoices.viewingFromProfile === 'undefined' ) {
            History.pushState({}, "", "index.php?fuse=billing&controller=invoice&view=invoices&customerid="+userid);
        } else {
            History.pushState({}, "", "index.php?fuse=clients&controller=userprofile&view=profileinvoices&frmClientID="+userid);
        }
        richgrid.reload({params:{start:0,invoicefilter:"3",customerid:userid}});

        viewinvoices.addinvoicedescription(3);

        $('#invoicelist-grid-filterbystatus').select2("val",3);
        $('#td-for-userid').show();
        $('#viewing-invoices-text').text(lang("Viewing Invoices for User Id "+$('#invoice-userid').val()));
    };

    viewinvoices.paymentReferenceWindow = new RichHTML.window({
        height: '75',
        width: '260',
        grid: richgrid,
        url:       'index.php?fuse=billing&controller=invoice&view=paymentreference',
        actionUrl: 'index.php?fuse=billing&controller=invoice&action=savepaymentreference',
        showSubmit: true,
        title: lang("Add Payment Reference")
    });

    if ($('#invoice-userid').val() != "") {
        viewinvoices.postactionsfilterbyuserid($('#invoice-userid').val());
    }

});
