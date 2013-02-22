Ext.namespace('PC.ux');

PC.ux.PageTree = function(config) {
	Ext.applyIf(config, {
		flex: 1,
		border: false,
		useArrows: false,
		autoScroll: true,
		animate: true,
		loader: new Ext.tree.TreeLoader({
			dataUrl: 'ajax.pagetree.php',
			baseParams: {
				site: PC.global.site
			},
			listeners: {
				beforeload: function(loader, node, callback){
					loader.baseParams.deleted = /^\/0\/-1\//.test(node.getPath());
					loader.baseParams.site = PC.global.site;
					if (node.getOwnerTree().additionalBaseParams) {
						Ext.apply(loader.baseParams.additional, node.getOwnerTree().additionalBaseParams);
					}
					loader.baseParams.additional = Ext.util.JSON.encode(loader.baseParams.additional);
				},
				load: function(loader, node, callback){
					PC.tree.UpdateNodes();
				}
			}
		}),
		root: {
			nodeType: 'async',
			draggable: false,
			editable: false,
			text: '* root *',
			id: '0'
		},
		rootVisible: false,
		_ln: PC.global.ln
	});
	if (config._sid != undefined) config.loader.baseParams.site = config._sid;
	// call parent constructor
	PC.ux.PageTree.superclass.constructor.call(this, config);
	
	this.on('append', function(tr, prnt, n, idx) {
		// Process node before rendering
		this.localizeNode(n);
		this.renderIcon(n);
		// shortcut overlay before render
	});
};

Ext.extend(PC.ux.PageTree, Ext.tree.TreePanel, {
	initialParams: {},
	additionalParams: {},
	renderIcon: function(n) {
		var attr = n.attributes;
		var ctrl = attr.controller;
		
		var nodeIcon = false;
		var parseClasses = true;
		var parseAttributes = true;
		
		//if (!/^[0-9]+$/.test(attr.id)) return; //prevent controller nodes from being parsed / edit: but now we need to parse them!
		if (attr._empty) n.loaded = true;
		
		var all_classes = ['cms-tree-node-folder', 'cms-tree-node-menu', 'cms-tree-node-nomenu', 'cms-tree-node-hot', 'cms-tree-node-hot_nomenu', 'cms-tree-node-hidden'];
		var _classes = ['cms-tree-node-folder'];
		
		if (attr.id == -1) {
			_classes.push('cms-tree-node-trash');
		}
		else if (attr.id == 'create') {
			_classes.push('cms-tree-node-add');
		}
		else if (attr._front == 1) {
			_classes.push('cms-tree-node-home');
		}
		else if (ctrl == 'menu') {
			_classes.push('cms-tree-node-menu');
		}
		else if (!/^[1-9][0-9]*$/.test(attr.id)) {
			var id = PC.pages.ParseID(attr.id);
			if (id.controller != undefined) {
				var hookName = 'core/tree/node/renderIcon/'+ id.controller;
				if (PC.hooks.Count(hookName)) {
					var params = {
						id: n.id,
						node: n,
						parseClasses: true,
						icon: false
					};
					PC.hooks.Init(hookName, params);
					nodeIcon = params.icon;
					var parseClasses = params.parseClasses;
				}
			}
		}
		if (!nodeIcon) {
			//n.setIcon(Ext.BLANK_IMAGE_URL);
		}
		

		if (nodeIcon) {
			parseClasses = false;
			n.setIcon(nodeIcon);
		}
		//else {
		//	n.setIcon(nodeIcon);
		//}
		else if (parseClasses) {
			if (attr._front != undefined) if (attr._front < 1) parseAttributes = false;
			if (parseAttributes) {
				if (attr.published != 1) {
					if (attr.published != undefined) _classes.push('cms-tree-node-hidden');
				}
				else if (attr.hot == 1 && attr._front != 1) {
					if (attr.nomenu == 1) _classes.push('cms-tree-node-hot_nomenu');
					else _classes.push('cms-tree-node-hot');
				}
				else if (attr.nomenu == 1 && attr._front != 1) _classes.push('cms-tree-node-nomenu');
			}
			Ext.iterate(all_classes, function(cls){
				n.ui.removeClass(cls);
			});
			Ext.iterate(_classes, function(cls){
				n.ui.addClass(cls);
			});
			attr.cls = 'cms-tree-node-folder '+ _classes.join(' ');
			if (attr._redir) {
				n.setIcon('images/shortcut.png');
			}
			else if (!nodeIcon) {
				n.setIcon(Ext.BLANK_IMAGE_URL);
			}
			
			
			if (attr.controller != '' && attr.controller != 'menu' && attr.id > 0) {
				if (PC.controller_page_icons && PC.controller_page_icons[attr.controller]) {
					n.setIcon(PC.controller_page_icons[attr.controller]);
				}
				else {
					n.setIcon('images/controller.png');
				}
			}
			
		}
		else if (attr._redir) {
			n.setIcon('images/shortcut.png');
		}
		else if (attr.controller != '' && attr.controller != 'menu' && attr.id > 0) {
			if (PC.controller_page_icons && PC.controller_page_icons[attr.controller]) {
				n.setIcon(PC.controller_page_icons[attr.controller]);
			}
			else {
				n.setIcon('images/controller.png');
			}
		}
		else {
			//n.setIcon('');
			n.setIcon(Ext.BLANK_IMAGE_URL);
		}
	},
	setLn: function(ln) {
		if (this._ln == ln) return;
		this._ln = ln;
		this.localizeAllNodes();
	},
	setSite: function(site) {
		if (this.loader.baseParams.site == site) return;
		this.loader.baseParams.site = site;
		this.setRootNode(new Ext.tree.AsyncTreeNode({
			draggable: false,
			editable: false,
			text: '* root *',
			id: '0'
		}));
		this.root.expand();
	},
	localizeNode: function(n) {
		var ctrl = n.attributes.controller;
		/*because of timing issues everything was moved server side on ajax.treepages.php!
		if (n.ui.iconNode) { // if rendered
			// shortcut overlay after render
			n.ui.iconNode.src = n.ui.emptyIcon;
			if (n.attributes._redir) {
				n.ui.iconNode.src = 'images/shortcut.png';
			}
			else if (ctrl != undefined) if (ctrl.length && ctrl != 'menu') n.ui.iconNode.src = 'images/controller.png';
			// menu icon
			if (ctrl == 'menu') {
				n.ui.addClass('cms-tree-node-menu');
			}
			else n.ui.removeClass('cms-tree-node-menu');
		}*/
		var setText = function(text, greyOut) {
			var text = PC.utils.escape(text);
			if (greyOut) {
				//text = '<span style="color: #666"><i>'+ text +'</i></span>';
				if (n.ui.rendered) {
					n.ui.addClass('tree_node_grey_out');
				}
				n.pc_grey_out = true;
				
			}
			else {
				if (n.ui.rendered) {
					n.ui.removeClass('tree_node_grey_out');
				}
				n.pc_grey_out = false;
			}
			return n.setText(text);
		}
		if (n.attributes.id == 'create') {
			setText(PC.i18n.create_new_page);
		}
		else if (n.attributes.id == -1) {
			setText(PC.i18n.bin);
		}
		else if (n.attributes._front > 0) {
			setText(PC.i18n.home);
		}
		else if (ctrl == 'search') {
			setText(PC.i18n.search);
		}
		else if (n.attributes._names) {
			if (n.attributes._names[this._ln]) {
				setText(n.attributes._names[this._ln]);
			} else {
				var name_mock = '';
				Ext.iterate(n.attributes._names, function(language, name) {
					if (name != undefined && name != '') {
						name_mock = name;
						return false;
					}
				});
				if (name_mock == '') name_mock = '...'; //PC.i18n.no_title;
				setText(name_mock, true);
			}
			if (n.attributes._front > 0) {
				setText(PC.i18n.home);
			}
		}
	},
	localizeAllNodes: function(n) {
		if (!n) n = this.root;
		Ext.each(n.childNodes, function(i) {
			this.localizeNode(i);
			this.localizeAllNodes(i);
		}, this);
	},
	addLoaderParam: function(ctrl, param, value){
		var params = this.additionalParams;
		if (params[ctrl] == undefined) params[ctrl] = {};
		//if (params[ctrl][param] == undefined) params[ctrl][param] = {};
		params[ctrl][param] = value;
		return true;
	},
	listeners: {
		beforerender: function(tree){
			tree.initialParams = tree.loader.baseParams;
		},
		beforeload: function(n) {
			var loader = this.loader;
			//base
			var site = loader.baseParams.site;
			var search_string = false;
			if (loader.baseParams.searchString) {
				search_string = loader.baseParams.searchString;
			}
			loader.baseParams = {};
			Ext.iterate(this.initialParams, function(param, value){
				loader.baseParams[param] = value;
			});
            loader.baseParams.site = site;
			if (search_string) {
				loader.baseParams.searchString = search_string;
			}
			var ctrl = n.attributes.controller;
			if (ctrl != undefined) if (ctrl != '') loader.baseParams.controller = n.attributes.controller;
			//beforeload hook
			PC.hooks.Init('tree.beforeload', {
				tree: n.getOwnerTree(),
				node: n
			});
			//additional
			loader.baseParams.additional = {};
			Ext.apply(loader.baseParams.additional, this.additionalParams);
			
			//custom tree renderers
			/*var renderer = '';
			var checkNode = n;
			while (checkNode != undefined) {
				if (checkNode.attributes.renderer != undefined && checkNode.attributes.renderer.length) {
					renderer = checkNode.attributes.renderer;
					break;
				}
				checkNode = checkNode.parentNode;
			}
			this.getLoader().baseParams.renderer = renderer;*/
		},
		load: function(n) {
			PC.hooks.Init('tree.load', {
				tree: n.getOwnerTree(),
				node: n
			});
			this.additionalParams = {};
		},
		checkchange: function (node, checked) {
			if (this.checked_nodes) {
				var node_id = node.id;
				
				if (this.checked_nodes_id_string && node_id.indexOf(this.checked_nodes_id_string) == -1) {
					return
				}
				if (checked) {
					if (this.checked_nodes.indexOf(node_id) == -1) {
						this.checked_nodes.push(node_id);
					}
				}
				else {
					this.checked_nodes.remove(node_id);
				}
			}
		}
	}
});


Ext.ComponentMgr.registerType('profis_pagetree', PC.ux.PageTree);

(function() {
	var originalRender = Ext.tree.TreeNode.prototype.render;

  Ext.override(Ext.tree.TreeNode, {
	  
	render: function() {
		var node_id = this.id;
		var my_tree = this.getOwnerTree();
		if (my_tree.checked_nodes) {
			if (my_tree.checked_nodes_id_string && node_id.indexOf(my_tree.checked_nodes_id_string) == -1) {
			}
			else {
				if (my_tree.checked_nodes.indexOf(node_id) != -1 && !this.ui.isChecked()) {
					//this.ui.toggleCheck();
					this.attributes.checked = true;
				}
				else if (typeof this.attributes.checkbox != 'undefined') {
					this.attributes.checked = false;
				}
			}
		}
		originalRender.apply(this, arguments);
		if (this.pc_grey_out) {
			this.ui.addClass('tree_node_grey_out');
		}
			
		
	}
	
  });
})();
