(function() {
	tinymce.create('tinymce.plugins.profis_styles', {
		init : function(ed, url) {
			this.editor = ed;

			// Register buttons
			ed.addButton('profis_styles', {
				title : 'Profis Styles',
				cmd : 'mceAdvLink'
			});

			ed.addShortcut('ctrl+k', 'advlink.advlink_desc', 'mceAdvLink');

			ed.onNodeChange.add(function(ed, cm, n, co) {
				cm.setDisabled('link', co && n.nodeName != 'A');
				cm.setActive('link', n.nodeName == 'A' && !n.name);
			});
		}
	});

	// Register plugin
	tinymce.PluginManager.add('profis_styles', tinymce.plugins.profis_styles);
})();