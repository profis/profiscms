Ext.ns('PC.sites');
//PC.sites.Current = null;

Ext.ns('PC.sites.languages');
PC.sites.languages.Get = function() {
	var site = Get_site();
	if (!site) return [];
	return site[3];
}
//PC.sites.languages.GetCurrent = function() {}
PC.sites.languages.Each = function(callback) {
	var lns = PC.sites.languages.Get();
	if (!lns) return false;
	if (lns.length) for (var a=0; lns[a] != undefined; a++) {
		var item = lns[a];
		callback(item[0], item[1]);
	}
	return true;
}

Ext.ns('PC.sites.languages.ext')
PC.sites.languages.ext.GetGridColumns = function(prefix) {
	if (prefix == undefined) var prefix = 'value_';
	var list = [];
	var r = PC.sites.languages.Each(function(ln, name){
		list.push({
			width: 160,
			header: '<div class="flag" style="background-position:'+ PC.utils.getFlagOffsets(ln) +';margin-right:4px;vertical-align:-1px;"></div>'+ name,
			dataIndex: prefix + ln,
			sortable: true,
			groupable: false,
			editor: {xtype: 'textfield'}
		});
	});
	if (!r) return [];
	return list;
}
PC.sites.languages.ext.FillStoreFields = function(fields, prefix) {
	if (prefix == undefined) var prefix = 'value_';
	PC.sites.languages.Each(function(ln, name){
		fields.push(prefix + ln);
	});
}