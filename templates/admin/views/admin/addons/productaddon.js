productaddon.newidcount = 1;
productaddon.product_type = null;

productaddon.clonenewoption = function()
{
    var newEl = $('.clone-addon-option').clone();
    newEl.removeClass('clone-addon-option');
    newEl.addClass('addon-option');
    newEl.attr('data-addon-id','0');
    newEl.attr('data-new-id',productaddon.newidcount++);
    newEl.show();
    $("#table-addonoptions").append(newEl);

    $("#table-addonoptions").tableDnD();

    $('.removeoption').unbind('click',productaddon.deleteaddonoption);
    $('.removeoption').bind('click',productaddon.deleteaddonoption);

};

productaddon.deleteaddonoption = function(e)
{
    if ($('.addon-option').length === 1){
        RichHTML.error("You can not delete all the options from an addon. You must have at least one option.");
    } else {
        if ($(this).data('candelete') != "1") {
            RichHTML.error("This option is in use. You can not delete it until you first change the following package to use another option: <br>" + $(this).data('candelete'));
        } else {
            $(this).closest('tr').remove();
        }
    }
};

productaddon.addaddonoption = function(e)
{
    $('.nooption').hide();
    productaddon.clonenewoption();
};

productaddon.convertbooltoint = function(val)
{
    return (val) ? 1 : 0;
};

productaddon.load_plugin_variables = function()
{
    //let's build up plugin variable options
    $.get('index.php?fuse=admin&controller=addons&action=getaddonvariables&productType='+productaddon.grouptype,function(data){
        var selected = false;
        var div = 0;
        data = ce.parseResponse(data);
        $('#pluginoption').empty();
        $(data.addons).each(function(index,obj) {

            //let's set values for custom
            if (productaddon.pluginvar.indexOf("CUSTOM_") != -1) {
                $('#custompluginvariable_value').val(productaddon.pluginvar.replace("CUSTOM_",""));
                productaddon.pluginvar = "CUSTOM";
            }

            selected = (productaddon.pluginvar == obj.plugin_var) ? " selected='selected' " : "";
            if (obj.name == '---') {
                div++;
                $('#pluginoption').append('<option value="divider_'+div+'">---</option>');
            } else {
                $('#pluginoption').append('<option data-availablein="'+obj.available_in+'" data-description="'+obj.description+'" value="'+obj.plugin_var+'" '+selected+'>'+obj.name+'</option>');
            }

            $('#pluginoption').trigger('click');

        });
        $('#pluginoption').select2('val', productaddon.pluginvar);
    });
}

$(document).ready(function(){

    if (productaddon.pluginvar != "NONE") {
        $('.pluginvaluecell').show();
    }

    $("#table-addonoptions").tableDnD();

    $('#product-groups').select2({
        width: '100%',
        tokenSeparators: [',']
    });

    $('#product-groups').on("change", function(e) {
        //console.debug({val:e.val, added:e.added, removed:e.removed});
        if (e.added) {
            var label = $(e.added.element).closest("optgroup").data("grouptype");
            $('select').find('optgroup[data-grouptype!="'+label+'"]').remove();

            productaddon.grouptype = label;

            // if ( $('#pluginoption').val() == "NONE" || $('#pluginoption').val() == "CUSTOM" ) {
            if ( $('#pluginoption').val() == "NONE" ) {
                //we haven't loaded any custom vars for a specific type so let's load
                //the list
                productaddon.load_plugin_variables();
            } else {
                //let's not load the plugin variable dropdown since we already have something selected
                //which means that the list was already loaded
            }


        } else if (e.removed){
            //what to do when we remove all product types
            //we should check if plugins has been setup
            //if it hasn't allow showing all products
            if ( $(this).val() === null && $('#pluginoption').val() == "NONE") {
                //lets clear all options we have left and readd (we only had filtered by type and we want to include all)
                $('#product-groups optgroup').remove();
                $.each(productaddon.productgroups,function(index,value){
                    groupel = $('<optgroup value="0" data-grouptype="'+value[0].typeid+'" label="Groups of type:'+value[0].type+'"></optgroup>');
                    //echo "<optgroup value='0' data-grouptype='".$cat[0]['typeid']."'  label='Groups of type: ".$key."'>";
                    $.each(value,function(index2,value2){
                        groupel.append('<option value="'+value2.id+'">'+value2.name+'</option>');
                    });
                    $('#product-groups').append(groupel);
                });

                //let's reset plugin variable
                productaddon.grouptype = "";
                productaddon.load_plugin_variables();

            }
        }

    });

    productaddon.load_plugin_variables();

    if (productaddon.grouptype !== -1) {
        $('select').find('optgroup[data-grouptype!="'+productaddon.grouptype+'"]').remove();
    }

    $('#pluginoption').bind('click',function(e){

        $('#custompluginvariable').hide();
        $('#pluginvar_description').hide();
        $('#pluginvar_availablein').hide();

        if ($('#pluginoption').val() == "CUSTOM") {
            $('#custompluginvariable').show();
            $('.pluginvaluecell').show();
        } else if ($('#pluginoption').val() == "NONE") {
            $('.pluginvaluecell').hide();
        } else if ($('#pluginoption').val().indexOf('divider_') != -1) {
            $('#pluginoption').select2("val", "NONE");
            $('.pluginvaluecell').hide();
        } else {
            if ($(this).find('option:selected').data('availablein') !== "") {
                $('#pluginvar_description').text($(this).find('option:selected').data('description'));
                $('#pluginvar_availablein').text("Supported Plugins: " + $(this).find('option:selected').data('availablein'));
            } else {
                $('#pluginvar_description').text("");
                $('#pluginvar_availablein').html("<span style='color:red;'>Not integrated with any plugin.</span><br/>It is recommended that at least one option for this addon is set to 'Open Ticket'.</span>");
            }
            $('#pluginvar_description').show();
            $('#pluginvar_availablein').show();
            $('.pluginvaluecell').show();
        }

    });

    $('.add-product-addon').bind('click',productaddon.addaddonoption);

    $('.removeoption').bind('click',productaddon.deleteaddonoption);

    $('.submit-addoon').bind('click',function(e){
        RichHTML.mask();
        e.preventDefault();

        if ($('.addon-option').length === 0){
            RichHTML.error("You must have at least one addon option before saving.");
            return;
        }

        $('.submit-addoon').addClass("disabled");

        var sortKey = 0;
        var addonpricing = [];
        var addonoption = {};
        //lets get prices
        $('.addon-option').each(function(a,row){
            addonoption = {};
            addonoption.id = $(this).data('addon-id');
            addonoption.detail = $(this).find("input[name='optionname']").val();
            addonoption.price0 = $(this).find("input[name='price0']").val();

            addonoption.price1 = $(this).find("input[name='price1']").val();
            addonoption.price1Force =
                productaddon.convertbooltoint($(this).find("input[name='price1_force']").is(':checked'));

            addonoption.price3 = $(this).find("input[name='price3']").val();
            addonoption.price3Force =
                productaddon.convertbooltoint($(this).find("input[name='price3_force']").is(':checked'));


            addonoption.price6 = $(this).find("input[name='price6']").val();
            addonoption.price6Force =
                productaddon.convertbooltoint($(this).find("input[name='price6_force']").is(':checked'));

            addonoption.price12 = $(this).find("input[name='price12']").val();
            addonoption.price12Force =
                productaddon.convertbooltoint($(this).find("input[name='price12_force']").is(':checked'));

            addonoption.price24 = $(this).find("input[name='price24']").val();
            addonoption.price24Force =
                productaddon.convertbooltoint($(this).find("input[name='price24_force']").is(':checked'));

            addonoption.pluginVarValue = $(this).find("input[name='plugin_var_value']").val();
            addonoption.sortKey = sortKey++;
            addonoption.openticket = productaddon.convertbooltoint($(this).find("input[name='openticket']").is(':checked'));
            addonoption.newid = $(this).data('new-id');
            addonpricing.push(addonoption);

        });

        var fielddata = {
            id: productaddon.id,
            addonpricing: addonpricing,
            pluginoption: $('#pluginoption').val(),
            custompluginvariable_value: $('#custompluginvariable_value').val(),
            productaddonname: $("input[name='product-addon-name']").val(),
            productgroups: $("#product-groups").val(),
            productdescription: $("textarea[name='product-addon-description']").getCode()
        };

        //redactor uses this for empty descriptions sometimes
        if (fielddata.productdescription == "<p><br></p>") fielddata.productdescription = "";

        $.post('index.php?fuse=admin&controller=addons&action=saveproductaddon', fielddata,
            function(data){
                data = ce.parseResponse(data);
                if (!data.error) {
                    // no error so redirect to addons list
                    window.location = 'index.php?fuse=admin&controller=addons&view=productaddons&groupid=' + $("#product-groups").val();
                }
                RichHTML.unMask();
            }
        );
    });
});