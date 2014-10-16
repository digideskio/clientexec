var dashboard = dashboard || {
    renderAtAGlance: function(payload) {
        var at_a_glance_template = "{{#arr}}<tr><td>{{name}}</td><td><center>{{count}}</center></td></tr>{{/arr}}{{^arr}}<tr><td colspan='2'><center>" + lang("There are no items needing your attention") + "</center></td></tr>{{/arr}}";
        var output = Mustache.render(at_a_glance_template, {arr: payload});
        $('.tbl-today-at-a-glance tbody').html(output);
    },
    vital: {
        getTodayAtAGlance:function() {
            $.ajax({
                url: 'index.php?fuse=home&controller=index&action=getataglance',
                dataType: 'json',
                success: function(response) {
                    response = ce.parseResponse(response);
                    if (response.ataglance.length > 0) {
                        dashboard.renderAtAGlance(response.ataglance);
                    }
                }
            });

        },

        automation_status_template : "{{#arr}}<tr><td>{{{name}}}</td><td><center>{{{status}}}</center></td></tr>{{/arr}}{{^arr}}<tr><td colspan='2'><center>" + lang("Currently there are no services enabled") + "</center></td></tr>{{/arr}}",

        getAutomationStatus: function() {
            $.ajax({
                url: 'index.php?fuse=home&controller=index&action=getautomationstatus',
                dataType: 'json',
                success: function(response) {
                    response = ce.parseResponse(response);
                    var output = Mustache.render(dashboard.vital.automation_status_template, {arr:response.automationstatus});
                    // console.log("RESPONSE", response);
                    // console.log("OUTPUT", output);
                    $('.tbl-need-your-attention tbody').html(output);
                }
            });
        }
    },
    events: {
        activeService: null,
        getUpcomingEvents: function(service) {
            if ( service == '' ) return;
            RichHTML.mask();
            $.ajax({
                url: 'index.php?fuse=home&controller=index&action=getupcomingevents',
                data: {
                    service: service
                },
                dataType: 'json',
                success: function (response) {
                    var output = response.output;
                    $('.selected-automation-name').text(response.servicename);
                    $.get('../templates/admin/views/home/index/upcomingevents.mustache', function(template) {
                        var items = {
                            headers: output.headers,
                            data: output.data,
                            extract: function() {
                                returnString = '';
                                $.each(this, function(i,o) {
                                    returnString += '<td>' + o + '</td>';
                                });
                                return returnString;
                            }
                        };
                        $('#upcoming-events-table').html(Mustache.render(template, items));
                        RichHTML.unMask();
                    });
                }
            });
        }
    }
};

$(document).ready(function(){

    $('.active-service-select').bind('click', function() {
        dashboard.events.getUpcomingEvents($(this).attr('data-value'));
    });

    //if we have cache data let's delay this call to allow other background
    //calls that are needed quicker
    if ($('.tbl-today-at-a-glance tbody[data-has-cache-data]').length > 0) {
        window.setTimeout(dashboard.vital.getTodayAtAGlance,2000);
    } else {
        dashboard.vital.getTodayAtAGlance();
    }

    dashboard.vital.getAutomationStatus();

    dashboard.pos = 1;

    $('.delete-event').click(function(e){
        var self = this;
        var event_id = $(this).attr('data-event-id');
        $.post('index.php?fuse=home&controller=events&action=deleteevent',{event_id:event_id},function(response){
            response = ce.parseResponse(response);
            if (!response.error) {
                $(self).parent().parent().remove();
            }
        });
    });

    $('.btn-delete-warnings').bind('click', function(e) {
        e.preventDefault();

        $.post('index.php?fuse=home&controller=events&action=deleteerrorevents',function(response){
            response = ce.parseResponse(response);
            $('.recent-error-table tbody').empty();
            $('.recent-error-table tbody').append("<tr><td colspan='3'><center>"+lang("No errors or warnings found")+"</center></td></tr>");
        });

    });

    $('.graph-slider-btn-prev').click(function() {

        if ($('.prev[disabled]').length > 0) return;
        if (dashboard.pos == 1) return;

        $('.prev').attr('disabled','true');
        $('.next').attr('disabled','true');
        var marginLeft = parseInt($('.graph_buttons').css('marginLeft'),10);
        marginLeft = marginLeft + (4*$(".overview li").width());

        $('.graph_buttons').animate({marginLeft: marginLeft},function()
        {
            dashboard.pos -= 4;
            $('.prev').removeAttr('disabled');
            $('.next').removeAttr('disabled');
        });
    });

    $('.graph-slider-btn-next').click(function() {

        if ((dashboard.total_num_of_reports - dashboard.pos) < 4) return;
        if ($('.next[disabled]').length > 0) return;

        $('.prev').attr('disabled','true');
        $('.next').attr('disabled','true');
        var marginLeft = parseInt($('.graph_buttons').css('marginLeft'),10);
        marginLeft = marginLeft - (4*$(".overview li").width());

        $('.graph_buttons').animate({marginLeft: marginLeft},function()
        {
            dashboard.pos += 4;
            $('.next').removeAttr('disabled');
            $('.prev').removeAttr('disabled');
        });
    });

    $('.overview li').on('click',function(){
        //let's set style
        $('.overview li').removeClass('active');
        $('.overview li').addClass('inactive');
        $(this).removeClass('inactive').addClass('active');
        clientexec.populate_report($(this).attr('data-report-value'),'#myChart',{indashboard:1});
    });

});
