var NoindDialog = {
init : function() {
 var f = document.forms[0];
 f.mtext.value = tinyMCEPopup.editor.selection.getContent({
 format : 'html'
 });
 f.mhref.value = '';
 },
 
 insert : function() {
  mlink= "<noindex>"+document.forms[0].mtext.value+"</noindex>";
  tinyMCEPopup.editor.execCommand('mceInsertContent', false, mlink);
  tinyMCEPopup.close();
 }
};
tinyMCEPopup.onInit.add(NoindDialog.init, NoindDialog);
