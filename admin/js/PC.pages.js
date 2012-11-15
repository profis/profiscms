Ext.ns('PC.pages');
PC.pages = {
	Create: function(root) {
		if (root !== true && PC.tree.menus.current_node) var id_new = PC.tree.menus.current_node.parentNode.id;
		else var id_new = 0;
		if (PC.tree.menus.current_node != undefined) if (PC.tree.menus.current_node.parentNode.attributes._front > 0) return;
		Ext.Ajax.request({
			url: 'ajax.pagetree.php',
			params: {
				'new': id_new,
				'site': PC.global.site
			},
			method: 'POST',
			callback: function(opts, success, rspns) {
				if (success && rspns.responseText) {
					try {
						var data = Ext.decode(rspns.responseText);
						if (!data._names.length) data._names = {};
						var n = PC.tree.component.getNodeById(opts.params['new']);
						n.expand();
						var nn = PC.tree.Append(n, data);
						if (nn) {
							PC.tree.component.localizeNode(nn);
							nn.loaded = true;
							nn.expand();
							node_rename_menu(nn, true);
						}
						return; // OK
					} catch(e) {};
				}
				Ext.MessageBox.show({
					title: PC.i18n.error,
					msg: PC.i18n.msg.error.page.create,
					buttons: Ext.MessageBox.OK,
					icon: Ext.MessageBox.ERROR
				});
			}
		});
	},
	ParseID: function(id) {
		var data = {};
		var i = id.indexOf('/');
		if (i !== -1) {
			data.controller = id.substring(0, i);
			data.id = id.substring(i+1);
		}
		else data.id = id;
		return data;
	}
};