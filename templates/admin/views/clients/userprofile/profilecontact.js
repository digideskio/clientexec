var datefields = new Array();

$().ready(function(){

    $('#deleteclient').click(function(){

        var answer=confirm(lang("Click OK if you are sure you want to delete this account"));
        if (answer){

            var contactForm = $("#frmdeleteclient");

            var answer2=confirm(lang("Do you want to delete the product(s) using the respective server plugin?"));
            if (answer2) $('#deletewithplugin').val(1);

            $.ajax( {
                url: contactForm.attr( 'action' ),
                type: contactForm.attr( 'method' ),
                data: contactForm.serialize(),
                success: function (json){
                    ce.parseResponse(json);
                    window.location = "index.php?fuse=clients&controller=user&view=viewusers";
                }
            } );
        }

        return false;
    });

    $("#updatecontact").click(function() {

        var contactForm = $("#customerdata");
        $.ajax( {
            url: contactForm.attr( 'action' ),
            type: contactForm.attr( 'method' ),
            data: contactForm.serialize(),
            success: function (json){
                ce.parseResponse(json);
            }
        } );

        return false;
    });

});