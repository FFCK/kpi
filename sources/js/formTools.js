//if ( typeof(jq) == "undefined" ) {
jq = jQuery.noConflict();
$ = jq;
//}
if ( typeof(lang) == "undefined" ) {
	lang = 'en';
}
var langue_tools = [];

if(lang == 'en')  {
    langue_tools['Afficher'] = 'Show banner';
    langue_tools['Annuler'] = 'Cancel';
    langue_tools['Aucune_ligne_selectionnee'] = 'No row selected';
    langue_tools['Cliquez_pour_modifier'] = 'Click to edit';
    langue_tools['Confirm_delete'] = 'Confirm remove ?';
    langue_tools['Confirm_update'] = 'Confirm update ?';
    langue_tools['MAJ_impossible'] = 'Unable to update';
    langue_tools['Rien_a_supprimer'] = 'Nothing to remove';
    langue_tools['Valider'] = 'Valid';
    langue_tools['Valide'] = 'Validated, locked (public score)';
    langue_tools['Vider'] = 'Empty';
} else {
    langue_tools['Afficher'] = 'Afficher la bannière';
    langue_tools['Annuler'] = 'Annuler';
    langue_tools['Aucune_ligne_selectionnee'] = 'Aucune ligne sélectionnée';
    langue_tools['Confirm_delete'] = 'Confirmez la suppression ?';
    langue_tools['Cliquez_pour_modifier'] = 'Cliquez pour modifier';
    langue_tools['Confirm_update'] = 'Confirmez le changement ?';
    langue_tools['MAJ_impossible'] = 'Mise à jour impossible';
    langue_tools['Rien_a_supprimer'] = 'Rien à supprimer';
    langue_tools['Valider'] = 'Valider';
    langue_tools['Valide'] = 'Validé / verrouillé (score public)';
    langue_tools['Vider'] = 'Vider';
}


jq(document).ready(function() {
//	jq("*").tooltip({
//		showURL: false
//	});
    
    jq("#masquer").click(function(event){
        event.preventDefault();
        jq('#banniere, .Left3, .Right4').hide();
        jq('#nav').append('<li id="afficher"><a href=""><img height="14" src="../img/afficher.png" alt="' + langue_tools['Afficher'] + '" title="' + langue_tools['Afficher'] + '"></a></li>');
        jq.post( "ajax_masquer.php", { masquer: 1 } );
    });
    jq("body").delegate("#afficher", "click", function(event){
        event.preventDefault();
        jq('#banniere, .Left3, .Right4').show();
        jq('#afficher').remove();
        jq.post( "ajax_masquer.php", { masquer: 0 } );
        
    });
    if(masquer == 1) {
        jq("#masquer").click();
    }
    
    jq("body").delegate(".hideall a", "click", function(e) {
        e.preventDefault();
        jq('.titrePage, #formFiltres, .blocTop, .blocMiddle').hide();
        jq(this).parent().addClass('showall').removeClass('hideall');
        jq('#blocMatchs').css('height', '550');
    });
    jq("body").delegate(".showall a", "click", function(e) {
        e.preventDefault();
        jq('.titrePage, #formFiltres, .blocTop, .blocMiddle').show();
        jq(this).parent().removeClass('showall').addClass('hideall');
        jq('#blocMatchs').css('height', '500');
    });
});

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
		alert(langue_tools['Rien_a_supprimer'] + ' !');
		return false;
	}
	else
	{
		if (!confirm(langue_tools['Confirm_delete']))
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
		alert(langue_tools['Aucune_ligne_selectionnee']);
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
	if (!confirm(langue_tools['Confirm_delete']))
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
		alert(langue_tools['Aucune_ligne_selectionnee']);
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

/*
highlight v3  !! Modified by Jon Raasch (http://jonraasch.com) to fix IE6 bug !!
Highlights arbitrary terms.
<http://johannburkard.de/blog/programming/javascript/highlight-javascript-text-higlighting-jquery-plugin.html>
MIT license.
Johann Burkard
<http://johannburkard.de>
<mailto:jb@eaio.com>
*/
jQuery.fn.highlight = function(pat) {
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
/*
highlight v3  !! Modified by Jon Raasch (http://jonraasch.com) to fix IE6 bug !!
Highlights arbitrary terms.
<http://johannburkard.de/blog/programming/javascript/highlight-javascript-text-higlighting-jquery-plugin.html>
MIT license.
Johann Burkard
<http://johannburkard.de>
<mailto:jb@eaio.com>
*/
jQuery.fn.highlight2 = function(pat) {
 function innerHighlight(node, pat) {
  var skip = 0;
  if (node.nodeType == 3) {
   var pos = node.data.toUpperCase().indexOf(pat);
   if (pos >= 0) {
    var spannode = document.createElement('span');
    spannode.className = 'highlight5';
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
jQuery.fn.removeHighlight2 = function() {
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
 return this.find("span.highlight5").each(function() {
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
	if(age <= 10){
		categorie = 'POU';
	}
	if(age >= 11){
		categorie = 'BEN';
	}
	if(age >= 13){
		categorie = 'MIN';
	}
	if(age >= 15){
		categorie = 'CAD';
	}
	if(age >= 17){
		categorie = 'JUN';
	}
	if(age >= 19){
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
	if(age >= 50){
		categorie = 'V4';
	}
	if(age >= 55){
		categorie = 'V5';
	}
	if(age >= 60){
		categorie = 'V6';
	}
	if(age >= 65){
		categorie = 'V7';
	}
	if(age >= 70){
		categorie = 'V8';
	}
	if(age >= 75){
		categorie = 'V9';
	}
	return categorie;

}


