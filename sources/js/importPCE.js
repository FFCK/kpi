jq = jQuery.noConflict();

var theLstEvt = '-1';
var theLocalUrl = 'http://localhost/KPI2';
var theDistantUrl = "https://www.kayak-polo.info";

function showDataConnector(data)
{
//	alert('showDataConnector = '+data);

	jq('#result').html("<h1>Importation</h1>");
	jq('#result').append("<h2>Les donnees suivantes sont enregistr&eacute;es dans la base locale ...</h2>");
	
	jq('#result').append("<div>"+data+"</div>");
}

function showExportReturn(data)
{
	alert('showExportReturn = '+data);

	jq('#result').html('<h1>Exportation</h1>');
	jq('#result').append('<div>'+data+'</div>');
}

function submitJsonData(json) 
{
	var txtJSON = JSON.stringify(json);
  	
   	var pos = txtJSON.indexOf('ERREUR');
    if ((pos >= 0) && (pos <= 2))
 	{
    	alert(txtJSON);
    	return;
    }
    
	jq('#json_data').attr('value', txtJSON);
	document.forms['ImportPCE'].submit();
}

function getRemoteData(url) 
{
    var script = document.createElement("script"); 
	script.type = "text/javascript"; 
	script.src = url + "&callback=submitJsonData"; //ajout de la fonction de retour
	jq("head")[0].appendChild(script);
}

function OnImport()
{
	theLstEvt = jq('#lstEvent').attr('value');
	var user = jq('#user').attr('value');
	var pwd = jq('#pwd').attr('value');
	
	if (theLstEvt.length == 0)
	{
	    alert("Erreur : Aucun Evenement ...");
	    return;
	}

	if (user.length == 0)
	{
	    alert("Erreur : Utilisateur Vide ...");
	    return;
	}
	
	if (pwd.length == 0)
	{
	    alert("Erreur : Mot de Passe Vide ...");
	    return;
	}
  
	jq.ajax({
		url : theLocalUrl+'/connector/ajax_md5.php?user='+user+'&pwd='+pwd,
		type: 'GET',
		dataType: 'text',
		cache: false,
		async: false,
		crossDomain:true,
		success: OnImportMD5
	});
}

function OnImportMD5(session)
{
//	alert('OnImportMD5 = '+session); //Revoir le texte d'alert
	getRemoteData(theDistantUrl+'/connector/get_evenement.php?lst='+theLstEvt+'&session='+session);
}

function OnImportServer()
{
    theLstEvt = jq('#lstEvent').attr('value');
    
	if (theLstEvt.length == 0)
	{
	    alert("Erreur : Aucun Evenement !");
	    return;
	}
	
    jq.ajax({
		url : theDistantUrl+'/connector/ajax_okevent.php?lst='+theLstEvt,
		type: 'GET',
		dataType: 'text',
		cache: false,
		async: false,
		crossDomain:false,
		success: OnImportServerOk
	});
}

function OnImportServerOk(msg)
{
    var pos = msg.indexOf('OK');
    if (pos == 0)
    {
		//alert(theLocalUrl+'/connector/get_evenement.php?lst='+theLstEvt);
        getRemoteData(theLocalUrl+'/connector/get_evenement.php?lst='+theLstEvt);
        return;
    }

    alert("ERREUR Evènement ou Login ... : "+msg);
}

function Init()
{
}

jq(document).ready(function() { 
	jq('#importPCE2').click(function(){
		jq('#json_msg').prepend( "Traitement en cours (patientez 15 à 20 secondes)..." );
		jq('#Control').val('importPCE2');
		jq('#ImportPCE').submit();
	});
	jq('#validNomImg').click(function(){
		var nomImg = jq('#TypeImg').val() + jq('#CompImg').val() + jq('#SaisonImg').val() + '.jpg';
		jq('#titre').val(nomImg);
	});

// "myAwesomeDropzone" is the camelized version of the HTML element's ID
	Dropzone.options.myAwesomeDropzone = {
		//paramName: "file", // The name that will be used to transfer the file
		maxFilesize: 2, // MB
	};

	jq('#btnImportServer').click(function() {
		OnImportServer();
	});

	jq('#btnImport').click(function() {
		OnImport();
	});
	
});

/*
#
# More info at [www.dropzonejs.com](http://www.dropzonejs.com)
# 
# Copyright (c) 2012, Matias Meno  
# 
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
# 
# The above copyright notice and this permission notice shall be included in
# all copies or substantial portions of the Software.
# 
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
# THE SOFTWARE.
#
*/


!function(){function a(b,c,d){var e=a.resolve(b);if(null==e){d=d||b,c=c||"root";var f=new Error('Failed to require "'+d+'" from "'+c+'"');throw f.path=d,f.parent=c,f.require=!0,f}var g=a.modules[e];if(!g._resolving&&!g.exports){var h={};h.exports={},h.client=h.component=!0,g._resolving=!0,g.call(this,h.exports,a.relative(e),h),delete g._resolving,g.exports=h.exports}return g.exports}a.modules={},a.aliases={},a.resolve=function(b){"/"===b.charAt(0)&&(b=b.slice(1));for(var c=[b,b+".js",b+".json",b+"/index.js",b+"/index.json"],d=0;d<c.length;d++){var b=c[d];if(a.modules.hasOwnProperty(b))return b;if(a.aliases.hasOwnProperty(b))return a.aliases[b]}},a.normalize=function(a,b){var c=[];if("."!=b.charAt(0))return b;a=a.split("/"),b=b.split("/");for(var d=0;d<b.length;++d)".."==b[d]?a.pop():"."!=b[d]&&""!=b[d]&&c.push(b[d]);return a.concat(c).join("/")},a.register=function(b,c){a.modules[b]=c},a.alias=function(b,c){if(!a.modules.hasOwnProperty(b))throw new Error('Failed to alias "'+b+'", it does not exist');a.aliases[c]=b},a.relative=function(b){function c(a,b){for(var c=a.length;c--;)if(a[c]===b)return c;return-1}function d(c){var e=d.resolve(c);return a(e,b,c)}var e=a.normalize(b,"..");return d.resolve=function(d){var f=d.charAt(0);if("/"==f)return d.slice(1);if("."==f)return a.normalize(e,d);var g=b.split("/"),h=c(g,"deps")+1;return h||(h=0),d=g.slice(0,h+1).join("/")+"/deps/"+d},d.exists=function(b){return a.modules.hasOwnProperty(d.resolve(b))},d},a.register("component-emitter/index.js",function(a,b,c){function d(a){return a?e(a):void 0}function e(a){for(var b in d.prototype)a[b]=d.prototype[b];return a}c.exports=d,d.prototype.on=function(a,b){return this._callbacks=this._callbacks||{},(this._callbacks[a]=this._callbacks[a]||[]).push(b),this},d.prototype.once=function(a,b){function c(){d.off(a,c),b.apply(this,arguments)}var d=this;return this._callbacks=this._callbacks||{},b._off=c,this.on(a,c),this},d.prototype.off=d.prototype.removeListener=d.prototype.removeAllListeners=function(a,b){this._callbacks=this._callbacks||{};var c=this._callbacks[a];if(!c)return this;if(1==arguments.length)return delete this._callbacks[a],this;var d=c.indexOf(b._off||b);return~d&&c.splice(d,1),this},d.prototype.emit=function(a){this._callbacks=this._callbacks||{};var b=[].slice.call(arguments,1),c=this._callbacks[a];if(c){c=c.slice(0);for(var d=0,e=c.length;e>d;++d)c[d].apply(this,b)}return this},d.prototype.listeners=function(a){return this._callbacks=this._callbacks||{},this._callbacks[a]||[]},d.prototype.hasListeners=function(a){return!!this.listeners(a).length}}),a.register("dropzone/index.js",function(a,b,c){c.exports=b("./lib/dropzone.js")}),a.register("dropzone/lib/dropzone.js",function(a,b,c){(function(){var a,d,e,f,g,h,i,j,k={}.hasOwnProperty,l=function(a,b){function c(){this.constructor=a}for(var d in b)k.call(b,d)&&(a[d]=b[d]);return c.prototype=b.prototype,a.prototype=new c,a.__super__=b.prototype,a},m=[].slice;d="undefined"!=typeof Emitter&&null!==Emitter?Emitter:b("emitter"),i=function(){},a=function(a){function b(a,d){var e,f,g;if(this.element=a,this.version=b.version,this.defaultOptions.previewTemplate=this.defaultOptions.previewTemplate.replace(/\n*/g,""),this.clickableElements=[],this.listeners=[],this.files=[],"string"==typeof this.element&&(this.element=document.querySelector(this.element)),!this.element||null==this.element.nodeType)throw new Error("Invalid dropzone element.");if(this.element.dropzone)throw new Error("Dropzone already attached.");if(b.instances.push(this),this.element.dropzone=this,e=null!=(g=b.optionsForElement(this.element))?g:{},this.options=c({},this.defaultOptions,e,null!=d?d:{}),this.options.forceFallback||!b.isBrowserSupported())return this.options.fallback.call(this);if(null==this.options.url&&(this.options.url=this.element.getAttribute("action")),!this.options.url)throw new Error("No URL provided.");if(this.options.acceptedFiles&&this.options.acceptedMimeTypes)throw new Error("You can't provide both 'acceptedFiles' and 'acceptedMimeTypes'. 'acceptedMimeTypes' is deprecated.");this.options.acceptedMimeTypes&&(this.options.acceptedFiles=this.options.acceptedMimeTypes,delete this.options.acceptedMimeTypes),this.options.method=this.options.method.toUpperCase(),(f=this.getExistingFallback())&&f.parentNode&&f.parentNode.removeChild(f),this.previewsContainer=this.options.previewsContainer?b.getElement(this.options.previewsContainer,"previewsContainer"):this.element,this.options.clickable&&(this.clickableElements=this.options.clickable===!0?[this.element]:b.getElements(this.options.clickable,"clickable")),this.init()}var c;return l(b,a),b.prototype.events=["drop","dragstart","dragend","dragenter","dragover","dragleave","addedfile","removedfile","thumbnail","error","errormultiple","processing","processingmultiple","uploadprogress","totaluploadprogress","sending","sendingmultiple","success","successmultiple","canceled","canceledmultiple","complete","completemultiple","reset","maxfilesexceeded","maxfilesreached"],b.prototype.defaultOptions={url:null,method:"post",withCredentials:!1,parallelUploads:2,uploadMultiple:!1,maxFilesize:256,paramName:"file",createImageThumbnails:!0,maxThumbnailFilesize:10,thumbnailWidth:100,thumbnailHeight:100,maxFiles:null,params:{},clickable:!0,ignoreHiddenFiles:!0,acceptedFiles:null,acceptedMimeTypes:null,autoProcessQueue:!0,addRemoveLinks:!1,previewsContainer:null,dictDefaultMessage:"Drop files here to upload",dictFallbackMessage:"Your browser does not support drag'n'drop file uploads.",dictFallbackText:"Please use the fallback form below to upload your files like in the olden days.",dictFileTooBig:"File is too big ({{filesize}}MiB). Max filesize: {{maxFilesize}}MiB.",dictInvalidFileType:"You can't upload files of this type.",dictResponseError:"Server responded with {{statusCode}} code.",dictCancelUpload:"Cancel upload",dictCancelUploadConfirmation:"Are you sure you want to cancel this upload?",dictRemoveFile:"Remove file",dictRemoveFileConfirmation:null,dictMaxFilesExceeded:"You can not upload any more files.",accept:function(a,b){return b()},init:function(){return i},forceFallback:!1,fallback:function(){var a,c,d,e,f,g;for(this.element.className=""+this.element.className+" dz-browser-not-supported",g=this.element.getElementsByTagName("div"),e=0,f=g.length;f>e;e++)a=g[e],/(^| )dz-message(jq| )/.test(a.className)&&(c=a,a.className="dz-message");return c||(c=b.createElement('<div class="dz-message"><span></span></div>'),this.element.appendChild(c)),d=c.getElementsByTagName("span")[0],d&&(d.textContent=this.options.dictFallbackMessage),this.element.appendChild(this.getFallbackForm())},resize:function(a){var b,c,d;return b={srcX:0,srcY:0,srcWidth:a.width,srcHeight:a.height},c=a.width/a.height,d=this.options.thumbnailWidth/this.options.thumbnailHeight,a.height<this.options.thumbnailHeight||a.width<this.options.thumbnailWidth?(b.trgHeight=b.srcHeight,b.trgWidth=b.srcWidth):c>d?(b.srcHeight=a.height,b.srcWidth=b.srcHeight*d):(b.srcWidth=a.width,b.srcHeight=b.srcWidth/d),b.srcX=(a.width-b.srcWidth)/2,b.srcY=(a.height-b.srcHeight)/2,b},drop:function(){return this.element.classList.remove("dz-drag-hover")},dragstart:i,dragend:function(){return this.element.classList.remove("dz-drag-hover")},dragenter:function(){return this.element.classList.add("dz-drag-hover")},dragover:function(){return this.element.classList.add("dz-drag-hover")},dragleave:function(){return this.element.classList.remove("dz-drag-hover")},paste:i,reset:function(){return this.element.classList.remove("dz-started")},addedfile:function(a){var c,d,e,f,g,h,i,j,k,l,m,n,o,p=this;for(this.element===this.previewsContainer&&this.element.classList.add("dz-started"),a.previewElement=b.createElement(this.options.previewTemplate.trim()),a.previewTemplate=a.previewElement,this.previewsContainer.appendChild(a.previewElement),l=a.previewElement.querySelectorAll("[data-dz-name]"),f=0,i=l.length;i>f;f++)c=l[f],c.textContent=a.name;for(m=a.previewElement.querySelectorAll("[data-dz-size]"),g=0,j=m.length;j>g;g++)c=m[g],c.innerHTML=this.filesize(a.size);for(this.options.addRemoveLinks&&(a._removeLink=b.createElement('<a class="dz-remove" href="javascript:undefined;" data-dz-remove>'+this.options.dictRemoveFile+"</a>"),a.previewElement.appendChild(a._removeLink)),d=function(c){return c.preventDefault(),c.stopPropagation(),a.status===b.UPLOADING?b.confirm(p.options.dictCancelUploadConfirmation,function(){return p.removeFile(a)}):p.options.dictRemoveFileConfirmation?b.confirm(p.options.dictRemoveFileConfirmation,function(){return p.removeFile(a)}):p.removeFile(a)},n=a.previewElement.querySelectorAll("[data-dz-remove]"),o=[],h=0,k=n.length;k>h;h++)e=n[h],o.push(e.addEventListener("click",d));return o},removedfile:function(a){var b;return null!=(b=a.previewElement)&&b.parentNode.removeChild(a.previewElement),this._updateMaxFilesReachedClass()},thumbnail:function(a,b){var c,d,e,f,g;for(a.previewElement.classList.remove("dz-file-preview"),a.previewElement.classList.add("dz-image-preview"),f=a.previewElement.querySelectorAll("[data-dz-thumbnail]"),g=[],d=0,e=f.length;e>d;d++)c=f[d],c.alt=a.name,g.push(c.src=b);return g},error:function(a,b){var c,d,e,f,g;for(a.previewElement.classList.add("dz-error"),"String"!=typeof b&&b.error&&(b=b.error),f=a.previewElement.querySelectorAll("[data-dz-errormessage]"),g=[],d=0,e=f.length;e>d;d++)c=f[d],g.push(c.textContent=b);return g},errormultiple:i,processing:function(a){return a.previewElement.classList.add("dz-processing"),a._removeLink?a._removeLink.textContent=this.options.dictCancelUpload:void 0},processingmultiple:i,uploadprogress:function(a,b){var c,d,e,f,g;for(f=a.previewElement.querySelectorAll("[data-dz-uploadprogress]"),g=[],d=0,e=f.length;e>d;d++)c=f[d],g.push(c.style.width=""+b+"%");return g},totaluploadprogress:i,sending:i,sendingmultiple:i,success:function(a){return a.previewElement.classList.add("dz-success")},successmultiple:i,canceled:function(a){return this.emit("error",a,"Upload canceled.")},canceledmultiple:i,complete:function(a){return a._removeLink?a._removeLink.textContent=this.options.dictRemoveFile:void 0},completemultiple:i,maxfilesexceeded:i,maxfilesreached:i,previewTemplate:'<div class="dz-preview dz-file-preview">\n  <div class="dz-details">\n    <div class="dz-filename"><span data-dz-name></span></div>\n    <div class="dz-size" data-dz-size></div>\n    <img data-dz-thumbnail />\n  </div>\n  <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>\n  <div class="dz-success-mark"><span>?</span></div>\n  <div class="dz-error-mark"><span>?</span></div>\n  <div class="dz-error-message"><span data-dz-errormessage></span></div>\n</div>'},c=function(){var a,b,c,d,e,f,g;for(d=arguments[0],c=2<=arguments.length?m.call(arguments,1):[],f=0,g=c.length;g>f;f++){b=c[f];for(a in b)e=b[a],d[a]=e}return d},b.prototype.getAcceptedFiles=function(){var a,b,c,d,e;for(d=this.files,e=[],b=0,c=d.length;c>b;b++)a=d[b],a.accepted&&e.push(a);return e},b.prototype.getRejectedFiles=function(){var a,b,c,d,e;for(d=this.files,e=[],b=0,c=d.length;c>b;b++)a=d[b],a.accepted||e.push(a);return e},b.prototype.getQueuedFiles=function(){var a,c,d,e,f;for(e=this.files,f=[],c=0,d=e.length;d>c;c++)a=e[c],a.status===b.QUEUED&&f.push(a);return f},b.prototype.getUploadingFiles=function(){var a,c,d,e,f;for(e=this.files,f=[],c=0,d=e.length;d>c;c++)a=e[c],a.status===b.UPLOADING&&f.push(a);return f},b.prototype.init=function(){var a,c,d,e,f,g,h,i=this;for("form"===this.element.tagName&&this.element.setAttribute("enctype","multipart/form-data"),this.element.classList.contains("dropzone")&&!this.element.querySelector(".dz-message")&&this.element.appendChild(b.createElement('<div class="dz-default dz-message"><span>'+this.options.dictDefaultMessage+"</span></div>")),this.clickableElements.length&&(d=function(){return i.hiddenFileInput&&document.body.removeChild(i.hiddenFileInput),i.hiddenFileInput=document.createElement("input"),i.hiddenFileInput.setAttribute("type","file"),(null==i.options.maxFiles||i.options.maxFiles>1)&&i.hiddenFileInput.setAttribute("multiple","multiple"),null!=i.options.acceptedFiles&&i.hiddenFileInput.setAttribute("accept",i.options.acceptedFiles),i.hiddenFileInput.style.visibility="hidden",i.hiddenFileInput.style.position="absolute",i.hiddenFileInput.style.top="0",i.hiddenFileInput.style.left="0",i.hiddenFileInput.style.height="0",i.hiddenFileInput.style.width="0",document.body.appendChild(i.hiddenFileInput),i.hiddenFileInput.addEventListener("change",function(){var a,b,c,e;if(b=i.hiddenFileInput.files,b.length)for(c=0,e=b.length;e>c;c++)a=b[c],i.addFile(a);return d()})},d()),this.URL=null!=(g=window.URL)?g:window.webkitURL,h=this.events,e=0,f=h.length;f>e;e++)a=h[e],this.on(a,this.options[a]);return this.on("uploadprogress",function(){return i.updateTotalUploadProgress()}),this.on("removedfile",function(){return i.updateTotalUploadProgress()}),this.on("canceled",function(a){return i.emit("complete",a)}),this.on("complete",function(){return 0===i.getUploadingFiles().length&&0===i.getQueuedFiles().length?setTimeout(function(){return i.emit("queuecomplete")},0):void 0}),c=function(a){return a.stopPropagation(),a.preventDefault?a.preventDefault():a.returnValue=!1},this.listeners=[{element:this.element,events:{dragstart:function(a){return i.emit("dragstart",a)},dragenter:function(a){return c(a),i.emit("dragenter",a)},dragover:function(a){var b;try{b=a.dataTransfer.effectAllowed}catch(d){}return a.dataTransfer.dropEffect="move"===b||"linkMove"===b?"move":"copy",c(a),i.emit("dragover",a)},dragleave:function(a){return i.emit("dragleave",a)},drop:function(a){return c(a),i.drop(a)},dragend:function(a){return i.emit("dragend",a)}}}],this.clickableElements.forEach(function(a){return i.listeners.push({element:a,events:{click:function(c){return a!==i.element||c.target===i.element||b.elementInside(c.target,i.element.querySelector(".dz-message"))?i.hiddenFileInput.click():void 0}}})}),this.enable(),this.options.init.call(this)},b.prototype.destroy=function(){var a;return this.disable(),this.removeAllFiles(!0),(null!=(a=this.hiddenFileInput)?a.parentNode:void 0)&&(this.hiddenFileInput.parentNode.removeChild(this.hiddenFileInput),this.hiddenFileInput=null),delete this.element.dropzone,b.instances.splice(b.instances.indexOf(this),1)},b.prototype.updateTotalUploadProgress=function(){var a,b,c,d,e,f,g,h;if(d=0,c=0,a=this.getAcceptedFiles(),a.length){for(h=this.getAcceptedFiles(),f=0,g=h.length;g>f;f++)b=h[f],d+=b.upload.bytesSent,c+=b.upload.total;e=100*d/c}else e=100;return this.emit("totaluploadprogress",e,c,d)},b.prototype.getFallbackForm=function(){var a,c,d,e;return(a=this.getExistingFallback())?a:(d='<div class="dz-fallback">',this.options.dictFallbackText&&(d+="<p>"+this.options.dictFallbackText+"</p>"),d+='<input type="file" name="'+this.options.paramName+(this.options.uploadMultiple?"[]":"")+'" '+(this.options.uploadMultiple?'multiple="multiple"':void 0)+' /><input type="submit" value="Upload!"></div>',c=b.createElement(d),"FORM"!==this.element.tagName?(e=b.createElement('<form action="'+this.options.url+'" enctype="multipart/form-data" method="'+this.options.method+'"></form>'),e.appendChild(c)):(this.element.setAttribute("enctype","multipart/form-data"),this.element.setAttribute("method",this.options.method)),null!=e?e:c)},b.prototype.getExistingFallback=function(){var a,b,c,d,e,f;for(b=function(a){var b,c,d;for(c=0,d=a.length;d>c;c++)if(b=a[c],/(^| )fallback(jq| )/.test(b.className))return b},f=["div","form"],d=0,e=f.length;e>d;d++)if(c=f[d],a=b(this.element.getElementsByTagName(c)))return a},b.prototype.setupEventListeners=function(){var a,b,c,d,e,f,g;for(f=this.listeners,g=[],d=0,e=f.length;e>d;d++)a=f[d],g.push(function(){var d,e;d=a.events,e=[];for(b in d)c=d[b],e.push(a.element.addEventListener(b,c,!1));return e}());return g},b.prototype.removeEventListeners=function(){var a,b,c,d,e,f,g;for(f=this.listeners,g=[],d=0,e=f.length;e>d;d++)a=f[d],g.push(function(){var d,e;d=a.events,e=[];for(b in d)c=d[b],e.push(a.element.removeEventListener(b,c,!1));return e}());return g},b.prototype.disable=function(){var a,b,c,d,e;for(this.clickableElements.forEach(function(a){return a.classList.remove("dz-clickable")}),this.removeEventListeners(),d=this.files,e=[],b=0,c=d.length;c>b;b++)a=d[b],e.push(this.cancelUpload(a));return e},b.prototype.enable=function(){return this.clickableElements.forEach(function(a){return a.classList.add("dz-clickable")}),this.setupEventListeners()},b.prototype.filesize=function(a){var b;return a>=109951162777.6?(a/=109951162777.6,b="TiB"):a>=107374182.4?(a/=107374182.4,b="GiB"):a>=104857.6?(a/=104857.6,b="MiB"):a>=102.4?(a/=102.4,b="KiB"):(a=10*a,b="b"),"<strong>"+Math.round(a)/10+"</strong> "+b},b.prototype._updateMaxFilesReachedClass=function(){return null!=this.options.maxFiles&&this.getAcceptedFiles().length>=this.options.maxFiles?(this.getAcceptedFiles().length===this.options.maxFiles&&this.emit("maxfilesreached",this.files),this.element.classList.add("dz-max-files-reached")):this.element.classList.remove("dz-max-files-reached")},b.prototype.drop=function(a){var b,c;a.dataTransfer&&(this.emit("drop",a),b=a.dataTransfer.files,b.length&&(c=a.dataTransfer.items,c&&c.length&&null!=c[0].webkitGetAsEntry?this._addFilesFromItems(c):this.handleFiles(b)))},b.prototype.paste=function(a){var b,c;if(null!=(null!=a?null!=(c=a.clipboardData)?c.items:void 0:void 0))return this.emit("paste",a),b=a.clipboardData.items,b.length?this._addFilesFromItems(b):void 0},b.prototype.handleFiles=function(a){var b,c,d,e;for(e=[],c=0,d=a.length;d>c;c++)b=a[c],e.push(this.addFile(b));return e},b.prototype._addFilesFromItems=function(a){var b,c,d,e,f;for(f=[],d=0,e=a.length;e>d;d++)c=a[d],null!=c.webkitGetAsEntry&&(b=c.webkitGetAsEntry())?b.isFile?f.push(this.addFile(c.getAsFile())):b.isDirectory?f.push(this._addFilesFromDirectory(b,b.name)):f.push(void 0):null!=c.getAsFile?null==c.kind||"file"===c.kind?f.push(this.addFile(c.getAsFile())):f.push(void 0):f.push(void 0);return f},b.prototype._addFilesFromDirectory=function(a,b){var c,d,e=this;return c=a.createReader(),d=function(a){var c,d,f;for(d=0,f=a.length;f>d;d++)c=a[d],c.isFile?c.file(function(a){return e.options.ignoreHiddenFiles&&"."===a.name.substring(0,1)?void 0:(a.fullPath=""+b+"/"+a.name,e.addFile(a))}):c.isDirectory&&e._addFilesFromDirectory(c,""+b+"/"+c.name)},c.readEntries(d,function(a){return"undefined"!=typeof console&&null!==console?"function"==typeof console.log?console.log(a):void 0:void 0})},b.prototype.accept=function(a,c){return a.size>1024*this.options.maxFilesize*1024?c(this.options.dictFileTooBig.replace("{{filesize}}",Math.round(a.size/1024/10.24)/100).replace("{{maxFilesize}}",this.options.maxFilesize)):b.isValidFile(a,this.options.acceptedFiles)?null!=this.options.maxFiles&&this.getAcceptedFiles().length>=this.options.maxFiles?(c(this.options.dictMaxFilesExceeded.replace("{{maxFiles}}",this.options.maxFiles)),this.emit("maxfilesexceeded",a)):this.options.accept.call(this,a,c):c(this.options.dictInvalidFileType)},b.prototype.addFile=function(a){var c=this;return a.upload={progress:0,total:a.size,bytesSent:0},this.files.push(a),a.status=b.ADDED,this.emit("addedfile",a),this._enqueueThumbnail(a),this.accept(a,function(b){return b?(a.accepted=!1,c._errorProcessing([a],b)):c.enqueueFile(a),c._updateMaxFilesReachedClass()})},b.prototype.enqueueFiles=function(a){var b,c,d;for(c=0,d=a.length;d>c;c++)b=a[c],this.enqueueFile(b);return null},b.prototype.enqueueFile=function(a){var c=this;if(a.accepted=!0,a.status!==b.ADDED)throw new Error("This file can't be queued because it has already been processed or was rejected.");return a.status=b.QUEUED,this.options.autoProcessQueue?setTimeout(function(){return c.processQueue()},0):void 0},b.prototype._thumbnailQueue=[],b.prototype._processingThumbnail=!1,b.prototype._enqueueThumbnail=function(a){var b=this;return this.options.createImageThumbnails&&a.type.match(/image.*/)&&a.size<=1024*this.options.maxThumbnailFilesize*1024?(this._thumbnailQueue.push(a),setTimeout(function(){return b._processThumbnailQueue()},0)):void 0},b.prototype._processThumbnailQueue=function(){var a=this;if(!this._processingThumbnail&&0!==this._thumbnailQueue.length)return this._processingThumbnail=!0,this.createThumbnail(this._thumbnailQueue.shift(),function(){return a._processingThumbnail=!1,a._processThumbnailQueue()})},b.prototype.removeFile=function(a){return a.status===b.UPLOADING&&this.cancelUpload(a),this.files=j(this.files,a),this.emit("removedfile",a),0===this.files.length?this.emit("reset"):void 0},b.prototype.removeAllFiles=function(a){var c,d,e,f;for(null==a&&(a=!1),f=this.files.slice(),d=0,e=f.length;e>d;d++)c=f[d],(c.status!==b.UPLOADING||a)&&this.removeFile(c);return null},b.prototype.createThumbnail=function(a,b){var c,d=this;return c=new FileReader,c.onload=function(){var e;return e=document.createElement("img"),e.onload=function(){var c,f,g,i,j,k,l,m;return a.width=e.width,a.height=e.height,g=d.options.resize.call(d,a),null==g.trgWidth&&(g.trgWidth=d.options.thumbnailWidth),null==g.trgHeight&&(g.trgHeight=d.options.thumbnailHeight),c=document.createElement("canvas"),f=c.getContext("2d"),c.width=g.trgWidth,c.height=g.trgHeight,h(f,e,null!=(j=g.srcX)?j:0,null!=(k=g.srcY)?k:0,g.srcWidth,g.srcHeight,null!=(l=g.trgX)?l:0,null!=(m=g.trgY)?m:0,g.trgWidth,g.trgHeight),i=c.toDataURL("image/png"),d.emit("thumbnail",a,i),null!=b?b():void 0},e.src=c.result},c.readAsDataURL(a)},b.prototype.processQueue=function(){var a,b,c,d;if(b=this.options.parallelUploads,c=this.getUploadingFiles().length,a=c,!(c>=b)&&(d=this.getQueuedFiles(),d.length>0)){if(this.options.uploadMultiple)return this.processFiles(d.slice(0,b-c));for(;b>a;){if(!d.length)return;this.processFile(d.shift()),a++}}},b.prototype.processFile=function(a){return this.processFiles([a])},b.prototype.processFiles=function(a){var c,d,e;for(d=0,e=a.length;e>d;d++)c=a[d],c.processing=!0,c.status=b.UPLOADING,this.emit("processing",c);return this.options.uploadMultiple&&this.emit("processingmultiple",a),this.uploadFiles(a)},b.prototype._getFilesWithXhr=function(a){var b,c;return c=function(){var c,d,e,f;for(e=this.files,f=[],c=0,d=e.length;d>c;c++)b=e[c],b.xhr===a&&f.push(b);return f}.call(this)},b.prototype.cancelUpload=function(a){var c,d,e,f,g,h,i;if(a.status===b.UPLOADING){for(d=this._getFilesWithXhr(a.xhr),e=0,g=d.length;g>e;e++)c=d[e],c.status=b.CANCELED;for(a.xhr.abort(),f=0,h=d.length;h>f;f++)c=d[f],this.emit("canceled",c);this.options.uploadMultiple&&this.emit("canceledmultiple",d)}else((i=a.status)===b.ADDED||i===b.QUEUED)&&(a.status=b.CANCELED,this.emit("canceled",a),this.options.uploadMultiple&&this.emit("canceledmultiple",[a]));return this.options.autoProcessQueue?this.processQueue():void 0},b.prototype.uploadFile=function(a){return this.uploadFiles([a])},b.prototype.uploadFiles=function(a){var d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,A,B,C,D,E,F,G,H,I=this;for(s=new XMLHttpRequest,t=0,x=a.length;x>t;t++)d=a[t],d.xhr=s;s.open(this.options.method,this.options.url,!0),s.withCredentials=!!this.options.withCredentials,p=null,f=function(){var b,c,e;for(e=[],b=0,c=a.length;c>b;b++)d=a[b],e.push(I._errorProcessing(a,p||I.options.dictResponseError.replace("{{statusCode}}",s.status),s));return e},q=function(b){var c,e,f,g,h,i,j,k,l;if(null!=b)for(e=100*b.loaded/b.total,f=0,i=a.length;i>f;f++)d=a[f],d.upload={progress:e,total:b.total,bytesSent:b.loaded};else{for(c=!0,e=100,g=0,j=a.length;j>g;g++)d=a[g],(100!==d.upload.progress||d.upload.bytesSent!==d.upload.total)&&(c=!1),d.upload.progress=e,d.upload.bytesSent=d.upload.total;if(c)return}for(l=[],h=0,k=a.length;k>h;h++)d=a[h],l.push(I.emit("uploadprogress",d,e,d.upload.bytesSent));return l},s.onload=function(c){var d;if(a[0].status!==b.CANCELED&&4===s.readyState){if(p=s.responseText,s.getResponseHeader("content-type")&&~s.getResponseHeader("content-type").indexOf("application/json"))try{p=JSON.parse(p)}catch(e){c=e,p="Invalid JSON response from server."}return q(),200<=(d=s.status)&&300>d?I._finished(a,p,c):f()}},s.onerror=function(){return a[0].status!==b.CANCELED?f():void 0},o=null!=(D=s.upload)?D:s,o.onprogress=q,i={Accept:"application/json","Cache-Control":"no-cache","X-Requested-With":"XMLHttpRequest"},this.options.headers&&c(i,this.options.headers);for(g in i)h=i[g],s.setRequestHeader(g,h);if(e=new FormData,this.options.params){E=this.options.params;for(m in E)r=E[m],e.append(m,r)}for(u=0,y=a.length;y>u;u++)d=a[u],this.emit("sending",d,s,e);if(this.options.uploadMultiple&&this.emit("sendingmultiple",a,s,e),"FORM"===this.element.tagName)for(F=this.element.querySelectorAll("input, textarea, select, button"),v=0,z=F.length;z>v;v++)if(j=F[v],k=j.getAttribute("name"),l=j.getAttribute("type"),"SELECT"===j.tagName&&j.hasAttribute("multiple"))for(G=j.options,w=0,A=G.length;A>w;w++)n=G[w],n.selected&&e.append(k,n.value);else(!l||"checkbox"!==(H=l.toLowerCase())&&"radio"!==H||j.checked)&&e.append(k,j.value);for(C=0,B=a.length;B>C;C++)d=a[C],e.append(""+this.options.paramName+(this.options.uploadMultiple?"[]":""),d,d.name);return s.send(e)},b.prototype._finished=function(a,c,d){var e,f,g;for(f=0,g=a.length;g>f;f++)e=a[f],e.status=b.SUCCESS,this.emit("success",e,c,d),this.emit("complete",e);return this.options.uploadMultiple&&(this.emit("successmultiple",a,c,d),this.emit("completemultiple",a)),this.options.autoProcessQueue?this.processQueue():void 0},b.prototype._errorProcessing=function(a,c,d){var e,f,g;for(f=0,g=a.length;g>f;f++)e=a[f],e.status=b.ERROR,this.emit("error",e,c,d),this.emit("complete",e);return this.options.uploadMultiple&&(this.emit("errormultiple",a,c,d),this.emit("completemultiple",a)),this.options.autoProcessQueue?this.processQueue():void 0},b}(d),a.version="3.8.4",a.options={},a.optionsForElement=function(b){return b.getAttribute("id")?a.options[e(b.getAttribute("id"))]:void 0},a.instances=[],a.forElement=function(a){if("string"==typeof a&&(a=document.querySelector(a)),null==(null!=a?a.dropzone:void 0))throw new Error("No Dropzone found for given element. This is probably because you're trying to access it before Dropzone had the time to initialize. Use the `init` option to setup any additional observers on your Dropzone.");return a.dropzone},a.autoDiscover=!0,a.discover=function(){var b,c,d,e,f,g;for(document.querySelectorAll?d=document.querySelectorAll(".dropzone"):(d=[],b=function(a){var b,c,e,f;for(f=[],c=0,e=a.length;e>c;c++)b=a[c],/(^| )dropzone(jq| )/.test(b.className)?f.push(d.push(b)):f.push(void 0);return f},b(document.getElementsByTagName("div")),b(document.getElementsByTagName("form"))),g=[],e=0,f=d.length;f>e;e++)c=d[e],a.optionsForElement(c)!==!1?g.push(new a(c)):g.push(void 0);return g},a.blacklistedBrowsers=[/opera.*Macintosh.*version\/12/i],a.isBrowserSupported=function(){var b,c,d,e,f;if(b=!0,window.File&&window.FileReader&&window.FileList&&window.Blob&&window.FormData&&document.querySelector)if("classList"in document.createElement("a"))for(f=a.blacklistedBrowsers,d=0,e=f.length;e>d;d++)c=f[d],c.test(navigator.userAgent)&&(b=!1);else b=!1;else b=!1;return b},j=function(a,b){var c,d,e,f;for(f=[],d=0,e=a.length;e>d;d++)c=a[d],c!==b&&f.push(c);return f},e=function(a){return a.replace(/[\-_](\w)/g,function(a){return a[1].toUpperCase()})},a.createElement=function(a){var b;return b=document.createElement("div"),b.innerHTML=a,b.childNodes[0]},a.elementInside=function(a,b){if(a===b)return!0;for(;a=a.parentNode;)if(a===b)return!0;return!1},a.getElement=function(a,b){var c;if("string"==typeof a?c=document.querySelector(a):null!=a.nodeType&&(c=a),null==c)throw new Error("Invalid `"+b+"` option provided. Please provide a CSS selector or a plain HTML element.");return c},a.getElements=function(a,b){var c,d,e,f,g,h,i,j;if(a instanceof Array){e=[];try{for(f=0,h=a.length;h>f;f++)d=a[f],e.push(this.getElement(d,b))}catch(k){c=k,e=null}}else if("string"==typeof a)for(e=[],j=document.querySelectorAll(a),g=0,i=j.length;i>g;g++)d=j[g],e.push(d);else null!=a.nodeType&&(e=[a]);if(null==e||!e.length)throw new Error("Invalid `"+b+"` option provided. Please provide a CSS selector, a plain HTML element or a list of those.");return e},a.confirm=function(a,b,c){return window.confirm(a)?b():null!=c?c():void 0},a.isValidFile=function(a,b){var c,d,e,f,g;if(!b)return!0;for(b=b.split(","),d=a.type,c=d.replace(/\/.*jq/,""),f=0,g=b.length;g>f;f++)if(e=b[f],e=e.trim(),"."===e.charAt(0)){if(-1!==a.name.toLowerCase().indexOf(e.toLowerCase(),a.name.length-e.length))return!0}else if(/\/\*jq/.test(e)){if(c===e.replace(/\/.*jq/,""))return!0}else if(d===e)return!0;return!1},"undefined"!=typeof jQuery&&null!==jQuery&&(jQuery.fn.dropzone=function(b){return this.each(function(){return new a(this,b)})}),"undefined"!=typeof c&&null!==c?c.exports=a:window.Dropzone=a,a.ADDED="added",a.QUEUED="queued",a.ACCEPTED=a.QUEUED,a.UPLOADING="uploading",a.PROCESSING=a.UPLOADING,a.CANCELED="canceled",a.ERROR="error",a.SUCCESS="success",g=function(a){var b,c,d,e,f,g,h,i,j,k;for(h=a.naturalWidth,g=a.naturalHeight,c=document.createElement("canvas"),c.width=1,c.height=g,d=c.getContext("2d"),d.drawImage(a,0,0),e=d.getImageData(0,0,1,g).data,k=0,f=g,i=g;i>k;)b=e[4*(i-1)+3],0===b?f=i:k=i,i=f+k>>1;return j=i/g,0===j?1:j},h=function(a,b,c,d,e,f,h,i,j,k){var l;return l=g(b),a.drawImage(b,c,d,e,f,h,i,j,k/l)},f=function(a,b){var c,d,e,f,g,h,i,j,k;if(e=!1,k=!0,d=a.document,j=d.documentElement,c=d.addEventListener?"addEventListener":"attachEvent",i=d.addEventListener?"removeEventListener":"detachEvent",h=d.addEventListener?"":"on",f=function(c){return"readystatechange"!==c.type||"complete"===d.readyState?(("load"===c.type?a:d)[i](h+c.type,f,!1),!e&&(e=!0)?b.call(a,c.type||c):void 0):void 0},g=function(){var a;try{j.doScroll("left")}catch(b){return a=b,setTimeout(g,50),void 0}return f("poll")},"complete"!==d.readyState){if(d.createEventObject&&j.doScroll){try{k=!a.frameElement}catch(l){}k&&g()}return d[c](h+"DOMContentLoaded",f,!1),d[c](h+"readystatechange",f,!1),a[c](h+"load",f,!1)}},a._autoDiscoverFunction=function(){return a.autoDiscover?a.discover():void 0},f(window,a._autoDiscoverFunction)}).call(this)}),a.alias("component-emitter/index.js","dropzone/deps/emitter/index.js"),a.alias("component-emitter/index.js","emitter/index.js"),"object"==typeof exports?module.exports=a("dropzone"):"function"==typeof define&&define.amd?define(function(){return a("dropzone")}):this.Dropzone=a("dropzone")}();