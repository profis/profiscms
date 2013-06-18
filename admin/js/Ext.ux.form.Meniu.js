//dialog
Ext.ns('Ext.ux.form.Meniu');
Ext.ux.form.Meniu = {
	show: function(x, y, splitbutton) {
		this.ln = PC.i18n.dialog.form;
		if(tinyMCE.isIE) {
			// we need focus to get selection correctly in IE
			tinyMCE.activeEditor.focus();
		}
		this.selection = tinyMCE.activeEditor.selection;
		this.bookmark = this.selection.getBookmark('simple');
		this.forms = tinyMCE.activeEditor.dom.select('form');
		this.splitbutton = splitbutton;
		var dialog = this;
		if (this.window) {
			var items = this.window.items.items;
			for (i=0; i<items.length; i++)
				if(items[i].id=="forms-meniu")
					dialog.update_meniu(items[i],dialog);

			this.window.show();
			this.window.setPagePosition(x, y);
			return;
		}
		this.window = new PC.ux.Window({
			pc_temp_window: true,
			width: 175,
			layout: 'form',
			bodyCssClass: 'x-border-layout-ct',
			style: 'padding:5px;',
			closeAction: 'hide',
			resizable: false,
			closable: false,
			draggable:false,
			shadow:false,
			border: false,
			defaults: {hideLabel:true},
			items: [
				{	xtype:'panel',
					id: 'forms-meniu',
					bodyCssClass: 'x-border-layout-ct',
					border: false,
					listeners: {
						render: function(ul) {
							dialog.update_meniu(ul,dialog);
						}
					}
				}
			],
			listeners: {
				deactivate: function(w){
					w.hide();
				}
			}
		});
		this.window.show();
		this.window.setPagePosition(x, y);
		
	},
	get_current_node: function()
	{
		this.selection.moveToBookmark(this.bookmark);
		var node = this.selection.getNode();
		if(node!=null && node.nodeName!="FORM")
			node = tinyMCE.activeEditor.dom.getParent(node, "FORM");
		
		return node;
	},
	selected_meniu_item: function(attribute) 
	{
		var node=this.get_current_node();
		var content = this.selection.getContent();
		
//		if (tinymce.isIE)
//		{	
//			
//		}

		if( node!=null && node.nodeName=="FORM" )
		{
			switch(attribute)
			{
				case 'FORM':
					break;
				case 'textarea':
					tinyMCE.execInstanceCommand(tinymce.activeEditor.id,"mceInsertContent",false,'<textarea>'+content+'</textarea>');
					break;
				case 'label':
					tinyMCE.execInstanceCommand(tinymce.activeEditor.id,"mceInsertContent",false,'<label>'+((content=='')?'&nbsp;':content)+'</label>');
					break;
				case 'select':
					tinyMCE.execInstanceCommand(tinymce.activeEditor.id,"mceInsertContent",false,'<select>'+((content=='')?content:'<option>'+content+'</option>')+'</select>');
					break;
				default:
					tinyMCE.execInstanceCommand(tinymce.activeEditor.id,"mceInsertContent",false,'<input type="'+attribute+'" value="'+content+'" />');	
			}
		}
		else if( attribute=='FORM' )
		{
			var node = this.selection.getNode();
			if(node!=null && node.nodeName!="P") {
				node = tinyMCE.activeEditor.dom.getParent(node, "P");
			}
			var id = 'pc_form_' + Math.random().toString(36).substr(2, 5);
			var content = '<form method="post" enctype="multipart/form-data" accept-charset="utf-8" id="'+id+'"><p>'+content+'</p></form>';
			if(tinyMCE.isIE && node!=null) {
				tinyMCE.execInstanceCommand(tinymce.activeEditor.id,'mceInsertRawHTML',false,'</p>'+content+'<p>');
			} else {
				tinyMCE.execInstanceCommand(tinymce.activeEditor.id,'mceInsertContent',false,content);
			}
			
			tinyMCE.execInstanceCommand(tinymce.activeEditor.id,"mceCleanup");
		}
	},
	is_available_menu_item: function(item) 
	{
		
		node=this.get_current_node();
		
		if(node!=null && node.nodeName == "FORM" && item != "FORM")
		{
			return true;
		}
		
		ParentNode = tinyMCE.activeEditor.dom.getParent(node, "FORM");
		if( (ParentNode==null || ParentNode.nodeName != "FORM") && item == "FORM")
		{
			return true;
		}
		return false;
	},
	
	update_meniu: function(ul,dialog) 
	{
		this.ln = PC.i18n.dialog.form;
		var meniu = 
		[
			//1-icon			2-name			3-action_id
			['forms_text.png',this.ln.insert_text,'text'],
			['forms_pass.png',this.ln.insert_pass,'password'],
			['forms_pass.png',this.ln.insert_email,'email'],
			['forms_check.png',this.ln.insert_checkbox,'checkbox'],
			['forms_radio.png',this.ln.insert_radio,'radio'],
			['forms_area.png',this.ln.insert_textarea,'textarea'],
			['forms_select.png',this.ln.insert_listbox,'select'],
			['forms_file.png',this.ln.insert_file,'file'],
			['forms_submit.png',this.ln.insert_submit,'submit'],
			['forms_image.png',this.ln.insert_image,'image'],
			['forms_reset.png',this.ln.insert_reset,'reset'],
			['forms_hidden.png',this.ln.insert_hidden,'hidden'],
			['forms_label.png',this.ln.insert_label,'label'],
			['forms_insert.png',this.ln.insert_form,'FORM']
		];
		var att = 
		{
			icon : 0,
			name : 1,
			action_id : 2
		}
		var ul_str='<ul>';
		for (i=0; i<meniu.length; i++)
		{
			ul_str+='<li _form_param="'+meniu[i][att.action_id]+'" class="'+(this.is_available_menu_item(meniu[i][att.action_id])?'formitem':'formitem_deactivated')+'"><img src="images/'+meniu[i][att.icon]+'">'+meniu[i][att.name]+'</li>';
		}
		ul_str+='</ul>';
		ul.update( ul_str );
		Ext.select('#forms-meniu li.formitem').on('click', function()
		{
			dialog.selected_meniu_item(this.getAttribute('_form_param'));
			//Ext.ux.form.Params.show();
			dialog.window.hide();
		});
	}
};
