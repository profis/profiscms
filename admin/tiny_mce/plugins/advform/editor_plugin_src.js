(function() 
{
	var each = tinymce.each;
tinymce.create('tinymce.plugins.AdvancedFormPlugin', 
{
	init : function(ed, url) 
	{
		var t = this;
		t.editor = ed;
		t.url = url;
		t.attributes = [
			{name: 'id', pack: false},
			{name: 'name', pack: true},
			{name: 'title', pack: true},
			{name: 'value', pack: true},
			{name: 'size', pack: true},
			{name: 'maxlength', pack: true},
			{name: 'readonly', pack: true},
			{name: 'required', pack: true},
			{name: 'checked', pack: true},
			{name: 'cols', pack: true},
			{name: 'rows', pack: true},
			{name: 'multiple', pack: true},
			{name: 'data-maxuploadsize', pack: true},
			{name: 'style', pack: false},
			{name: 'onfocus', pack: true},
			{name: 'onblur', pack: true},
			{name: 'onselect', pack: true},
			{name: 'onchange', pack: true},
			{name: 'class', pack: true},
			{name: 'for', pack: false},
			{name: 'textContent', pack: true},
			{name: 'src', pack: false}
		];
		t.optionAttributes = ['value', 'selected', 'disabled', 'textContent'];
		
		ed.addCommand('mceAdvForm', function() 
		{
			var el=ed.selection.getNode(), dom = ed.dom;
			
			ParentNode = dom.getParent(el, "FORM");
			if(/mceItemForm_.+/.test(dom.getAttrib(el, 'class')))
			{
				Ext.ux.form.Params.show(el);
			}
			else if (el.nodeName == 'FORM' || ( ParentNode!=null && ParentNode.nodeName == 'FORM'))
			{
				Ext.ux.form.Params.show(el.nodeName=='FORM'?el:ParentNode);
			}
			return;

			ed.windowManager.open
			({
				file : url + '/form.htm',
				width : 480 + parseInt(ed.getLang('advform.delta_width', 0)),
				height : 385 + parseInt(ed.getLang('advform.delta_height', 0)),
				inline : 1
			}, 
			{
				plugin_url : url
			});
		});
		
		ed.onInit.add(function(ed) 
		{
			ed.selection.onSetContent.add(function() {t._inputsToImgs(ed.getBody());});

			if (ed.settings.content_css !== false)
				ed.dom.loadCSS(url + "/css/content.css");
			
			if (ed && ed.plugins.contextmenu)
			{
				ed.plugins.contextmenu.onContextMenu.add
				(
				function(th, m, e) 
				{
					var sm, se = ed.selection, el = se.getNode() || ed.getBody(), dom=ed.dom;
					ParentNode = dom.getParent(el, "FORM");
					if (el.nodeName == 'FORM' || ( ParentNode!=null && ParentNode.nodeName == 'FORM'))
					{
						m.addSeparator();
						if(/mceItemForm_.+/.test(dom.getAttrib(el, 'class')))
						{
							m.add({title : 'Edit form '+dom.getAttrib(el, 'class').split("mceItemForm_")[1], cmd : 'mceAdvForm', icon : 'form',ui : true});
						}
						else
						{
							m.add({title : 'Edit form', icon : 'form', cmd : 'mceAdvForm', ui : true});
						}
					}
				});
			}
		});
		
		ed.onPreProcess.add(function(ed, o)
		{
			var dom = ed.dom;
			if(o.get){
			each(dom.select('span', o.node), function(n) 
			{
				if (/mceItemForm_.+/.test(dom.getAttrib(n, 'class')))
				{
					dom.replace(t._buildInput(n),n);
				}
			
			});
			each(dom.select('img', o.node), function(n) 
			{
				if (/mceItemForm_.+/.test(dom.getAttrib(n, 'class')))
				{
					dom.replace(t._buildInput(n),n);
				}
			
			});
			}
		});
		
		ed.onSetContent.add(function(){t._inputsToImgs(ed.getBody());});
		
		ed.addButton('form', {title: 'advform.form_desc', cmd: 'mceAdvForm'});
	},
	_inputsToImgs : function(p)
	{
		var t = this, dom = t.editor.dom;
		each(dom.select('label', p), function(n)
		{
			dom.replace(t._buildReplacement(n), n);
		});
		each(dom.select('input,textarea,select', p), function(n)
		{
			dom.replace(t._buildReplacement(n), n);
		});
	},
	_buildInput : function(n) 
	{
		var ob, dom = this.editor.dom, type = dom.getAttrib(n, 'class').split("mceItemForm_")[1], attrs = this.attributes, attrObject, tagName = this._getTagName(type);

		// unpack packed attributes
		var attr = dom.getAttrib(n, 'data-advform');
		attrObject = this._parse(attr);

		// read non-packed attributes
		for (var i=0; i<attrs.length; i++) {
			if(!attrs[i].pack) {
				var attr = dom.getAttrib(n, attrs[i].name);
				if((typeof(attr) != 'undefined') && (attr != '')) {
					attrObject[attrs[i].name] = attr;
				}
			}
		}
		if(type != 'image') {
			delete attrObject.src;
		}
		
		if(tagName == 'input') {
			attrObject.type = type;
			ob = dom.create('input', attrObject);
		} else if(tagName == 'textarea') {
			var textContent = attrObject.textContent;
			delete attrObject.textContent;
			ob = dom.create('textarea', attrObject, textContent);
		} else if(tagName == 'select') {
			var options = attrObject.options;
			delete attrObject.options;
			ob = dom.create('select', attrObject);
			// add options
			for(var i=0; i<options.length; i++) {
				var textContent = options[i].textContent;
				delete options[i].textContent;
				dom.add(ob, 'option', options[i], textContent)
			}
		} else if(tagName == 'label') {
			ob = dom.create('label', attrObject, n.innerHTML);
		}
		
		return ob;
	},
	_buildReplacement : function(n)
	{
		var t = this, dom = this.editor.dom, va = this.attributes, ea = {}, ao = {}, im, type;

		for (var i=0; i<va.length; i++) {
			var cav = dom.getAttrib(n, va[i].name);
			if((typeof(cav) != 'undefined') && (cav != '')) {
				if(va[i].pack) {
					ea[va[i].name] = cav;
				} else {
					ao[va[i].name] = cav;
				}
			}
		}
		
		if (n.nodeName.toLowerCase() == 'label') {
			ao['class'] = 'mceItemForm_label';
			ao['data-advform'] = this._serialize(ea);
			span = dom.create('span', ao, n.innerHTML);
			
			return span;
		} else {
			if(n.nodeName.toLowerCase() == 'textarea') {
				type = 'textarea';
				ea.textContent = n.innerHTML;
			} else if (n.nodeName.toLowerCase() == 'select') {
				type = 'select';
				var options = [];
				each(dom.select('option', n), function(on)
				{
					var va = t.optionAttributes;
					var o = {};
					for (var i=0; i<va.length; i++) {
						var cav = dom.getAttrib(on, va[i]);
						if((typeof(cav) != 'undefined') && (cav != '')) {
							o[va[i]] = cav;
						}
					}
					o.textContent = on.innerHTML;
					options.push(o);
				});
				ea.options = options;
			} else {
				type = dom.getAttrib(n, 'type');
			}
			if((type != 'image') || (typeof(ao.src) == 'undefined')) {
				ao.src = this.url + '/img/'+type+'.png';
			}
			ao['class'] = 'mceItemForm_' + type;
			ao['data-advform'] = this._serialize(ea);
			im = dom.create('img', ao);
			
			return im;
		}
	},
	_getTagName : function(title)
	{
		switch(title)
		{
			case 'textarea':
			case 'select':
			case 'label':
				return title;
			default:
				return 'input';
		}
	},
	_parse : function(s) 
	{
		return tinymce.util.JSON.parse(s);
	},
	_serialize : function(o) {
		return tinymce.util.JSON.serialize(o);
	},
	getInfo : function() 
	{
			return {
				longname : 'Advanced form',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
	}
});
	// Register plugin
	tinymce.PluginManager.add('advform', tinymce.plugins.AdvancedFormPlugin);
})();
