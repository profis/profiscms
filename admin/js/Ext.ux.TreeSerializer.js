/*global Ext*/

/**
 * @class Ext.tree.TreeSerializer
 * A base class for implementations which provide serialization of an
 * {@link Ext.tree.TreePanel}.
 * <p>
 * Implementations must provide a toString method which returns the serialized
 * representation of the tree.
 * 
 * @constructor
 * @param {TreePanel} tree
 * @param {Object} config
 */
Ext.tree.TreeSerializer = function(tree, config){
	if (typeof this.toString !== 'function') {
		throw 'Ext.tree.TreeSerializer implementation does not implement toString()';
	}
	this.tree = tree;

	if (this.attributeFilter) {
		this.attributeFilter = this.attributeFilter.createInterceptor(this.defaultAttributeFilter);
	} else {
		this.attributeFilter = this.defaultAttributeFilter;
	}
	
	if (this.nodeFilter) {
		this.nodeFilter = this.nodeFilter.createInterceptor(this.defaultNodeFilter);
	} else {
		this.nodeFilter = this.defaultNodeFilter;
	}
	
	Ext.apply(this, config);

};

Ext.tree.TreeSerializer.prototype = {

	/*
	 * @cfg nodeFilter {Function} (optional) A function, which when passed the node, returns true or false to include
	 * or exclude the node.
	 */
	 
	/*
	 * @cfg attributeFilter {Function} (optional) A function, which when passed an attribute name, and an attribute value,
	 * returns true or false to include or exclude the attribute.
	 */
	 
	/*
	 * @cfg attributeMap {Array} (Optional) An associative array mapping Node attribute names to XML attribute names.
	 */

	/* @private
	 * Array of node attributes to ignore.
	 */
	standardAttributes: ["loader","expanded", "allowDrag", "allowDrop", "disabled", "icon",
	"cls", "iconCls", "href", "hrefTarget", "qtip", "singleClickExpand", "uiProvider", "allowChildren", "expandable"],
    
	jsonAttributes: [],

	/** @private
	 * Default attribute filter.
	 * Rejects functions and standard attributes.
	 */
	defaultAttributeFilter: function(attName, attValue) {
		return	(typeof attValue != 'function') &&
		(this.standardAttributes.indexOf(attName) == -1);
	},
	
	jsonAttributeFilter: function(attName, attValue) {
		return	(typeof attValue != 'function') &&
		(this.jsonAttributes.indexOf(attName) == -1);
	},

	/** @private
	 * Default node filter.
	 * Accepts all nodes.
	 */
	defaultNodeFilter: function(node) {
		return true;
	}
};

/**
 * @class Ext.tree.XmlTreeSerializer
 * An implementation of Ext.tree.TreeSerializer which serializes an
 * {@link Ext.tree.TreePanel} to an XML string.
 */
Ext.tree.XmlTreeSerializer = function(tree, config){
	Ext.tree.XmlTreeSerializer.superclass.constructor.apply(this, arguments);
};

Ext.extend(Ext.tree.XmlTreeSerializer, Ext.tree.TreeSerializer, {
	/**
	 * Returns a string of XML that represents the tree
	 * @return {String}
	 */
	toString: function(){
		return '<?xml version="1.0"?><tree>' +
		this.nodeToString(this.tree.getRootNode()) + '</tree>';
	},

	/**
	 * Returns a string of XML that represents the node
	 * @param {Object} node The node to serialize
	 * @return {String}
	 */
	nodeToString: function(node){
		if (!this.nodeFilter(node)) {
			return '';
		}
		var result = '<node';
	    
		/**
	     *  This doesn't appear necessary. Since the iteration below will include id, 
	     *  this block simply includes it twice
	     
	    if (this.attributeFilter("id", node.id)) {
	        result += ' id="' + node.id + '"';
	    }
	    ***/

		//		Add all user-added attributes unless rejected by the attributeFilter.
		for(var key in node.attributes) {
			if (this.attributeFilter(key, node.attributes[key])) {
				result += ' ' + (this.attributeMap ? (this.attributeMap[key] || key) : key) + '="' + node.attributes[key] + '"';
			}
		}

		//		Add child nodes if any
		var children = node.childNodes;
		var clen = children.length;
		if(clen == 0){
			result += '/>';
		}else{
			result += '>';
			for(var i = 0; i < clen; i++){
				result += this.nodeToString(children[i]);
			}
			result += '</node>';
		}
		return result;
	}

});

/**
 * @class Ext.tree.JsonTreeSerializer
 * An implementation of Ext.tree.TreeSerializer which serializes an
 * {@link Ext.tree.TreePanel} to a Json string.
 */
Ext.tree.JsonTreeSerializer = function(tree, config){
	Ext.tree.JsonTreeSerializer.superclass.constructor.apply(this, arguments);
};

Ext.extend(Ext.tree.JsonTreeSerializer, Ext.tree.TreeSerializer, {

	/**
	 * Returns a string of Json that represents the tree
	 * @return {String}
	 */
	toString: function(skip_root){
		return this.nodeToString(this.tree.getRootNode(), skip_root);
	},

	/**
	 * Returns a string of Json that represents the node
	 * @param {Object} node The node to serialize
	 */
	nodeToString: function(node, skip_root){
		//		Exclude nodes based on caller-supplied filtering function
		if (!this.nodeFilter(node)) {
			return '';
		}
		var c = false, result = "{";
		if (skip_root) {
			result = "["
		}
		var value = '';
		/** don't double-include id
	    
	    if (this.attributeFilter("id", node.id)) {
	        result += '"id":"' + node.id + '"';
	        c = true;
	    }
	    
	    */

		//		Add all user-added attributes unless rejected by the attributeFilter.
		if (!skip_root) {
			for(var key in node.attributes) {
				if (this.attributeFilter(key, node.attributes[key])) {
					if (c) {
						result += ',';
					}
					if (typeof node.attributes[key] === 'object') {
						value = Ext.util.JSON.encode(node.attributes[key]);
					}
					else {
						value = node.attributes[key];
						if (this.jsonAttributeFilter(key)) {
							value = Ext.util.JSON.encode(value);
						}
						else if (typeof node.attributes[key] === 'string') {
							value = '"' + value + '"';
						}
						
					}
					

					result += '"' + (this.attributeMap ? (this.attributeMap[key] || key) : key) + '":' + value;
					c = true;
				}
			}
		}
		
	
		//		Add child nodes if any
		var children = node.childNodes;
		var clen = children.length;
		if(clen != 0){
			if (c) {
				result += ',';
			}
			if (!skip_root) {
				result += '"children":[';
			}
			for(var i = 0; i < clen; i++){
				if (i > 0) {
					result += ',';
				}
				result += this.nodeToString(children[i]);
			}
			if (!skip_root) {
				result += ']';
			}
		}
		return result + (skip_root?']':"}");
	}
});