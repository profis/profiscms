
Ext.namespace('PC.ux');

PC.ux.DynamicArrayReader = Ext.extend(Ext.data.ArrayReader,  {

    readRecords: function(data) {
        if (data.length > 0) {
            var item = data[0];
            var fields = new Array();
            var columns = new Array();
            var p;

            for (p in item) {
                if (p && p != undefined) {
                    // floatOrString type is only an option
                    // You can make your own data type for more complex situations
                    // or set it just to 'string'
                    fields.push({name: p, type: 'string'});
                    columns.push({text: p, dataIndex: p});
                }
            }

            data.metaData = { fields: fields, columns: columns };
			data.meta = data.metaData;
        }

        return this.callParent([data]);
    }
	
});

