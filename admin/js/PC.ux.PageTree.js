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
		
		if (!/^[0-9]+$/.test(attr.id)) return;
		
		if (attr._empty) n.loaded = true;
		
		var all_classes = ['cms-tree-node-folder', 'cms-tree-node-menu', 'cms-tree-node-nomenu', 'cms-tree-node-hot', 'cms-tree-node-hot_nomenu', 'cms-tree-node-hidden'];
		var _classes = ['cms-tree-node-folder'];
		
		//jei nera fiktyvus node'as
		
		if (attr.id == -1) {
			_classes.push('cms-tree-node-trash');
		}
		else if (attr.id == 'create') {
			_classes.push('cms-tree-node-add');
		}
		else if (attr._front == 1) {
			_classes.push('cms-tree-node-home');
		}
		else if (ctrl == 'search') {
			_classes.push('cms-tree-node-search');
		}
		else if (ctrl == 'menu') {
			_classes.push('cms-tree-node-menu');
		}
		else if (attr.id > 0) {
			if (attr.published != 1) {
				if (attr.published != undefined) _classes.push('cms-tree-node-hidden');
			}
			else if (attr.hot == 1) {
				if (attr.nomenu == 1) _classes.push('cms-tree-node-hot_nomenu');
				else _classes.push('cms-tree-node-hot');
			}
			else if (attr.nomenu == 1) _classes.push('cms-tree-node-nomenu');
		}
		
		Ext.iterate(all_classes, function(cls){
			n.ui.removeClass(cls)
		});
		Ext.iterate(_classes, function(cls){
			n.ui.addClass(cls)
		});
		attr.cls = 'cms-tree-node-folder '+ _classes.join(' ');
		
		if (attr._redir) n.setIcon('images/shortcut.png');
		else if (attr.controller != '' && attr.controller != 'menu' && attr.id > 0) n.setIcon('images/controller.png');
		else n.setIcon('');
		/*
		if (update_el) {
			var node_el = n.getUI().getEl();
			//var icon_el = n.getUI().getIconEl();
			node_el.className = 'x-tree-node cms-tree-node-folder '+ n.attributes.cls;
			//icon_el.className = 'x-tree-node-icon '+ n.attributes.cls.substring('cms-tree-node-folder'.length+1);
		}
		*/
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
		if (n.attributes.id == 'create') {
			n.setText(PC.i18n.create_new_page);
		}
		else if (n.attributes.id == -1) {
			n.setText(PC.i18n.bin);
		}
		else if (n.attributes._front > 0) {
			n.setText(PC.i18n.home);
		}
		else if (ctrl == 'search') {
			n.setText(PC.i18n.search);
		}
		else if (n.attributes._names) {
			if (n.attributes._names[this._ln]) {
				n.setText(n.attributes._names[this._ln]);
			} else {
				var name_mock = '';
				Ext.iterate(n.attributes._names, function(language, name) {
					if (name != undefined && name != '') {
						name_mock = name;
						return false;
					}
				});
				if (name_mock == '') name_mock = '...'; //PC.i18n.no_title;
				n.setText('<span style="color: #666"><i>'+name_mock+'</i></span>');
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
			loader.baseParams = {};
			Ext.iterate(this.initialParams, function(param, value){
				loader.baseParams[param] = value;
			});
			var ctrl = n.attributes.controller;
			if (ctrl != undefined) if (ctrl != '') loader.baseParams.controller = n.attributes.controller;
			//beforeload hook
			PC.hooks.Init('tree.beforeload', {
				tree: n.getOwnerTree(),
				node: n
			});
			//additional
			loader.baseParams.additional = Ext.encode(this.additionalParams);
			
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
		}
	}
});

Ext.ComponentMgr.registerType('profis_pagetree', PC.ux.PageTree);