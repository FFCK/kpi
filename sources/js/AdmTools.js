// Drag and Drop (class='drag')
// CSS: .drag {position:relative; cursor:pointer;}
var dragobject={
	ox:null, oy:null, trgObj:null, okDrg:0,sx:0, sy:0,
	initialize:function(){
		document.onmousedown=this.drag
		document.onmouseup=function(){this.okDrg=0;
		// alert('Position: left: '+this.trgObj.offsetLeft+', top: '+this.trgObj.offsetTop)
		}}
	,drag:function(e){
		var evtobj=window.event? window.event:e
		this.trgObj=window.event? event.srcElement:e.target
		if (this.trgObj.className=="drag"){
			this.okDrg=1
			if (isNaN(parseInt(this.trgObj.style.left))){this.trgObj.style.left=0}
			if (isNaN(parseInt(this.trgObj.style.top))){this.trgObj.style.top=0}
			this.ox=parseInt(this.trgObj.style.left)
			this.oy=parseInt(this.trgObj.style.top)
			this.sx=evtobj.clientX
			this.sy=evtobj.clientY
			if (evtobj.preventDefault) evtobj.preventDefault()
			document.onmousemove=dragobject.moveit}}
	,moveit:function(e){
		var evtobj=window.event? window.event:e
		if (this.okDrg==1){
			this.trgObj.style.left=this.ox+evtobj.clientX-this.sx+"px"
			this.trgObj.style.top=this.oy+evtobj.clientY-this.sy+"px"
		return false}}
}
dragobject.initialize()


function setCheckboxes(formName, checkName, do_check)
{
	var elts = document.forms[formName].elements[checkName];
	var elts_count = (typeof(elts.length) != 'undefined') ? elts.length : 0;

	if (elts_count) 
	{
		for (var i = 0; i < elts_count; i++) 
		{
			const elt_display = window.getComputedStyle(elts[i].parentElement.parentElement, null).display
			if (elt_display != 'none') {
				elts[i].checked = do_check;
			}
		} 
	} 
	else 
	{
		elts.checked = do_check;
	} 
}
    
    
function RemoveCheckboxes(formName, checkName)
{
	var elts = document.forms[formName].elements[checkName];
	var elts_count = (typeof(elts.length) != 'undefined') ? elts.length : 0;

	var str = '';
	if (elts_count) 
	{
		for (var i = 0; i < elts_count; i++) 
		{
			if (elts[i].checked)
			{
				if (str.length > 0)
					str += ',';
			
				str += elts[i].value;
			}
		} 
	}
	else
	{
		if (elts.checked)
			str = elts.value;
	}
	  
	if (str.length == 0)
	{
		alert("Rien à supprimer !, aucune ligne sélectionnée !!!! ...");
		return false;
	}
	else
	{
		if (!confirm('Confirmation de la Suppression ? '))
			return false;
 		if (!confirm('ATTENTION : Confirmation de la Suppression des éléments '+str+' ?'))
			return false;
		document.forms[formName].elements['Cmd'].value = 'Remove';
		document.forms[formName].elements['ParamCmd'].value = str;
		document.forms[formName].submit();
		return true;
	}
}


function AddCheckboxes(formName, checkName)
{
	var elts = document.forms[formName].elements[checkName];
	var elts_count = (typeof(elts.length) != 'undefined') ? elts.length : 0;
	var str = '';
	if (elts_count) 
	{
		for (var i = 0; i < elts_count; i++) 
		{
			if (elts[i].checked)
			{
				if (str.length > 0)
					str += ',';
				str += elts[i].value;
			}
		} 
	}
	else
	{
		str = elts.value;
	}
	if (str.length == 0)
	{
		alert("Rien à ajouter !, Aucune ligne sélectionnée ...");
		return false;
	}
	else
	{
		if (!confirm('Confirmation de l\'ajout ? '))
			return false;
  
		document.forms[formName].elements['Cmd'].value = 'Add';
		document.forms[formName].elements['ParamCmd'].value = str;
		document.forms[formName].submit();
		return true;
	}
}
  	
 	
function RemoveCheckbox(formName, checkCode)
{
	if (!confirm('Confirmation de la Suppression individuelle ? '))
		return false;
  
	document.forms[formName].elements['Cmd'].value = 'Remove';
	document.forms[formName].elements['ParamCmd'].value = checkCode;
	document.forms[formName].submit();
	return true;
}
  	
 	
function AddCheckbox(formName, checkCode)
{
	if (!confirm('Confirmation de l\'Ajout individuel ? '))
		return false;
  
	document.forms[formName].elements['Cmd'].value = 'Add';
	document.forms[formName].elements['ParamCmd'].value = checkCode;
	document.forms[formName].submit();
	return true;
}

	  	  
function changeCombo(formName, comboName, idHidden, bSubmit)
{
	var sel = document.forms[formName].elements[comboName].selectedIndex;
	if(sel != undefined) 
		document.forms[formName].elements[idHidden].value = document.forms[formName].elements[comboName].options[sel].value;
	
	if (bSubmit)
		document.forms[formName].submit();
}
  	
		
function numbersonly(myfield, e, dec) //?
{
	var key;
	var keychar;
	
	if (window.event)
	   key = window.event.keyCode;
	else if (e)
	   key = e.which;
	else
	   return true;
	keychar = String.fromCharCode(key);
	
	// control keys
	if ((key==null) || (key==0) || (key==8) || 
		(key==9) || (key==13) || (key==27) )
	   return true;
	
	// numbers
	else if ((("0123456789").indexOf(keychar) > -1))
	   return true;
	
	// decimal point jump
	else if (dec && (keychar == "."))
	   {
	   myfield.form.elements[dec].focus();
	   return false;
	   }
	else
	   return false;
}


function alertMsg(msg)
{
	if(msg != '')
	{
		alert(msg);
	}
}

function SelectedCheckboxes(formName, checkName)
{
	var elts = document.forms[formName].elements[checkName];
	var elts_count = (typeof(elts.length) != 'undefined') ? elts.length : 0;

	var str = '';
	if (elts_count) 
	{
		for (var i = 0; i < elts_count; i++) 
		{
			if (elts[i].checked)
			{
				if (str.length > 0)
					str += ',';
			
				str += elts[i].value;
			}
		} 
	}
	else
	{
		if (elts.checked)
			str = elts.value;
	}
	  
	if (str.length == 0)
	{
		alert("Aucune ligne sélectionnée !!!! ...");
		return false;
	}
	else
	{
		document.forms[formName].elements['ParamCmd'].value = str;
		return true;
	}
}

function testframe()
{	
	if (top.location != self.document.location)
	{
		document.getElementById('banniere').style.display='none';
		//top.location = self.document.location;
	}
}

$(function() {		// $(document).ready(function() {
	$( document ).tooltip({
		content: function () {
			return $(this).prop('title');
		}
	});
	$.widget( "custom.catcomplete", $.ui.autocomplete, { // Widget autocomplete avec gestion des categories
		_create: function() {
			this._super();
			this.widget().menu( "option", "items", "> :not(.ui-autocomplete-category)" );
		},
		_renderMenu: function( ul, items ) {
			var that = this,
			currentCategory = "";
			$.each( items, function( index, item ) {
				var li;
				if ( item.category != currentCategory ) {
					ul.append( "<li class='ui-autocomplete-category'>" + item.category + "</li>" );
					currentCategory = item.category;
				}
				li = that._renderItemData( ul, item );
				if ( item.category ) {
					li.attr( "aria-label", item.category + " : " + item.label );
				}
			});
		}
	});

});

/*
highlight v3  !! Modified by Jon Raasch (http://jonraasch.com) to fix IE6 bug !!
Highlights arbitrary terms.
<http://johannburkard.de/blog/programming/javascript/highlight-javascript-text-higlighting-jquery-plugin.html>
MIT license.
Johann Burkard
<http://johannburkard.de>
<mailto:jb@eaio.com>
*/
$.fn.highlight = function(pat) {
 function innerHighlight(node, pat) {
  var skip = 0;
  if (node.nodeType == 3) {
   var pos = node.data.toUpperCase().indexOf(pat);
   if (pos >= 0) {
    var spannode = document.createElement('span');
    spannode.className = 'highlight';
    var middlebit = node.splitText(pos);
    var endbit = middlebit.splitText(pat.length);
    var middleclone = middlebit.cloneNode(true);
    spannode.appendChild(middleclone);
    middlebit.parentNode.replaceChild(spannode, middlebit);
    skip = 1;
   }
  }
  else if (node.nodeType == 1 && node.childNodes && !/(script|style)/i.test(node.tagName)) {
   for (var i = 0; i < node.childNodes.length; ++i) {
    i += innerHighlight(node.childNodes[i], pat);
   }
  }
  return skip;
 }
 return this.each(function() {
  innerHighlight(this, pat.toUpperCase());
 });
};
jQuery.fn.removeHighlight = function() {
 function newNormalize(node) {
    for (var i = 0, children = node.childNodes, nodeCount = children.length; i < nodeCount; i++) {
        var child = children[i];
        if (child.nodeType == 1) {
            newNormalize(child);
            continue;
        }
        if (child.nodeType != 3) { continue; }
        var next = child.nextSibling;
        if (next == null || next.nodeType != 3) { continue; }
        var combined_text = child.nodeValue + next.nodeValue;
        new_node = node.ownerDocument.createTextNode(combined_text);
        node.insertBefore(new_node, child);
        node.removeChild(child);
        node.removeChild(next);
        i--;
        nodeCount--;
    }
 }
 return this.find("span.highlight").each(function() {
    var thisParent = this.parentNode;
    thisParent.replaceChild(this.firstChild, this);
    newNormalize(thisParent);
 }).end();
};
/***************************************************************************/

function calculCategorie(naissance, saison) {
	naissance = naissance.substring(0, 4);
	var age = saison - naissance;
	var categorie = age;
	if(age >= 12){
		categorie = 'MIN';
	}
	if(age >= 14){
		categorie = 'CAD';
	}
	if(age >= 16){
		categorie = 'JUN';
	}
	if(age >= 18){
		categorie = 'SEN';
	}
	if(age >= 35){
		categorie = 'V1';
	}
	if(age >= 40){
		categorie = 'V2';
	}
	if(age >= 45){
		categorie = 'V3';
	}
	return categorie;

}


