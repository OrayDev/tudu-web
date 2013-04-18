/*******************************************************************************
* KindEditor - WYSIWYG HTML Editor for Internet
* Copyright (C) 2006-2011 kindsoft.net
*
* @author Roddy <luolonghao@gmail.com>
* @site http://www.kindsoft.net/
* @licence http://www.kindsoft.net/license.php
*******************************************************************************/

// Google Maps: http://code.google.com/apis/maps/index.html

KindEditor.plugin('googlemap', function(K) {
	var self = this, name = 'map', lang = self.lang(name + '.');
	self.plugin.mapDialog = function(options){
		var html = ['<div style="padding:10px 20px;">',
            '<div class="ke-dialog-row">',
            lang.address + ' <input id="kindeditor_plugin_map_address" name="address" class="ke-input-text" value="中国" style="width:200px;" /> ',
            '<span class="ke-button-common ke-button-outer">',
            '<input type="button" name="searchBtn" class="ke-button-common ke-button" value="' + lang.search + '" />',
            '</span>',
            '</div>',
            '<div class="ke-map" style="width:558px;height:360px;"></div>',
            '</div>'].join('');
        var dialog = self.createDialog({
            name : name,
            width : 600,
            title : self.lang(name),
            body : html,
            yesBtn : {
                name : self.lang('yes'),
                click : function(e) {
                    var geocoder = win.geocoder,
                        map = win.map,
                        center = map.getCenter().lat() + ',' + map.getCenter().lng(),
                        zoom = map.getZoom(),
                        maptype = map.getMapTypeId(),
                        url = 'http://maps.googleapis.com/maps/api/staticmap';
                        url += '?center=' + encodeURIComponent(center);
                        url += '&zoom=' + encodeURIComponent(zoom);
                        url += '&size=558x360';
                        url += '&maptype=' + encodeURIComponent(maptype);
                        url += '&markers=' + encodeURIComponent(center);
                        url += '&language=' + self.langType;
                        url += '&sensor=false';
                    self.exec('insertimage', url).hideDialog().focus();
                }
            },
            beforeRemove : function() {
                searchBtn.remove();
                if (doc) {
                    doc.write('');
                }
                iframe.remove();
            }
        });
        var div = dialog.div,
            addressBox = K('[name="address"]', div),
            searchBtn = K('[name="searchBtn"]', div),
            win, doc;
        var iframe = K('<iframe class="ke-textarea" frameborder="0" src="/js/kindeditor-4.1.5/plugins/googlemap/googlemap.html" style="width:558px;height:360px;"></iframe>');
        function ready() {
            win = iframe[0].contentWindow;
            doc = K.iframeDoc(iframe);
        }
        iframe.bind('load', function() {
            iframe.unbind('load');
            if (K.IE) {
                ready();
            } else {
                setTimeout(ready, 0);
            }
        });
        K('.ke-map', div).replaceWith(iframe);
        // search map
        searchBtn.click(function() {
            win.search(addressBox.val());
        });
	}
	
	self.clickToolbar(name, function(){
		self.plugin.mapDialog();
	});
});
