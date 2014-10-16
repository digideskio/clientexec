productview = {};

productview.callPluginAction = function(pluginAction, packageId)
{
    RichHTML.mask();
    $.ajax({
        url: 'index.php?fuse=clients&action=callpluginaction',
        success: function(xhr) {
            var json = ce.parseResponse(xhr);
             RichHTML.unMask();
        },
        data: {
            id: packageId,
            actioncmd: pluginAction
        }
    });
};