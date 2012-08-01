// Path to the blank image must point to a valid location on your server
Ext.BLANK_IMAGE_URL = 'ext/resources/images/default/s.gif';

Ext.namespace('PC.global');

// Main application entry point
Ext.onReady(function(){
	// write your application here
	Ext.QuickTips.init();
	Ext.select('body').setStyle('background', '#ddd');
	// Copy strings from PC.langs[admin_ln] to PC.i18n
	PC.utils.localize();
	//PC.dialog.styles.show();
	//load_plugins();
	//PC.dialog.gmaps.show();
	//PC.dialog.tables.show();
	//PC.dialog.gallery.show();
	//PC.dialog.source.show();
	//PC.dialog.search.show();
	//PC.dialog.links.show();
	PC.dialog.gallery.show();
	/*setTimeout(function(){
		PC.dialog.gallery.edit_image('3', 'medium');
	}, 500);*/
});

function load_plugins() {
	//load modules
	Ext.Ajax.request({
		url: 'ajax.plugins.php',
		success: function(rspns, opts) {
			// code based on Ext.Element.update()
			var hd = document.getElementsByTagName('head')[0];
			var attr_rx = /\s(\w+)=([\'\"])(.*?)\2/ig;
			var m, am;
			
			// EXTRACT & LOAD STYLES
			var style_rx = /<style([^>]*)>([^\0]*?)<\/style>/gim;
			while (m = style_rx.exec(rspns.responseText)) {
				var ch = document.createElement('style');
				while (am = attr_rx.exec(m[1]))
					ch.setAttribute(am[1], am[3]); // need to do htmlspecialchars_decode on am[3]
				if (ch.styleSheet)
					ch.styleSheet.cssText = m[2]; // IE
				else
					ch.appendChild(document.createTextNode(m[2])); // the world
				hd.appendChild(ch);
			}
			rspns.responseText = rspns.responseText.replace(style_rx, '');
			
			// EXTRACT & LOAD SCRIPTS
			var a;
			var script_rx = /<script([^>]*)>([^\0]*?)<\/script>/gim;
			while (m = script_rx.exec(rspns.responseText)) {
				a = {};
				while (am = attr_rx.exec(m[1]))
					a[am[1]] = am[3]; // need to do htmlspecialchars_decode on am[3]
				if (a.src) {
					var ch = document.createElement('script');
					for (am in a)
						ch.setAttribute(am, a[am]);
					hd.appendChild(ch);
				} else {
					try {
						if (window.execScript)
							window.execScript(m[2]); // IE
						else
							window.eval(m[2]); // the world
					} catch(e) {
						debug_alert(e);
					};
				}
			}
			rspns.responseText = rspns.responseText.replace(script_rx, '');
			
			//PC.global.modules_panel.update(rspns.responseText);
			mod_sites_langs_click();
			/*
			//Sort PC.modules by priority
			PC.modules.sort(function(a, b) {
				if (a.priority == b.priority) return 0;
				return (a.priority < b.priority)?1:-1;
			});*/
		},
		failure: function(rspns, opts) {
			//PC.global.modules_panel.update(PC.i18n.msg.error.modules);
		}
	});
}