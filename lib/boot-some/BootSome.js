/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
var BootSome = (function(){
	function load() {
		Ufo.callback_add('dialog','get','dialog_get');
		Ufo.callback_add('dialog','abort','dialog_abort');
		Ufo.callback_add('dialog','post','dialog_post');
		Ufo.callback_add('main','get','main_get');
		document.body.addEventListener('keyup',dialog_keyup);
	}
	function dialog_keyup(event){
		if(event.keyCode === 27){
			if(window.dialogopen === true){
				Ufo.abort('dialog');
			}
		}
	}
	function el_delete(elem) {
		elem.parentNode.removeChild(elem);
	}
	function el_down(elem) {
		var next = elem.nextElementSibling;
		if(!next) return;
		el_swap(elem,next);
	}
	function el_up(elem) {
		var prev = elem.previousElementSibling;
		if(!prev) return;
		el_swap(prev,elem);
	}
	function el_swap(first,second){
		var second_parent = second.parentNode;
		var following = second.nextSibling;
		first.parentNode.insertBefore(second, first); // moving second to first's position
		if(following) second_parent.insertBefore(first, following); // moving first to second's position
		else second_parent.appendChild(first);
	}
	function active() {
		var group = event.target.parentElement.parentElement.querySelectorAll('li');
		group.forEach(a => {
		  a.querySelectorAll('a')[0].classList.remove('active');
		});
		event.target.classList.add('active');
	}

	var exportobj = {
		load: load,
		el_delete: el_delete,
		el_delete: el_delete,
		el_up: el_up,
		el_down: el_down,
		active: active,
		debug: false,
	};

	return exportobj;
})();

Ufo.callback_functions.dialog_get = function(){
	if(!window.dialogopen){
		document.body.classList.add('modal-open');
		document.getElementById('dialog').setAttribute('open','');
	}
	window.dialogopen = true;
}
Ufo.callback_functions.dialog_abort = function(){
	if(window.dialogopen){
		document.getElementById('dialog').removeAttribute('open');
		document.getElementById('dialog').innerHTML='';
		document.body.classList.remove('modal-open');
	}
	window.dialogopen = false;
}
Ufo.callback_functions.alert = function(string){
	alert(string);
}
Ufo.callback_functions.focus = function(string,select = true){
	document.getElementById(string).focus();
	if(select) {
		document.getElementById(string).select();
	}
	else {
		var end = document.getElementById(string).value.length;
		document.getElementById(string).setSelectionRange(end,end);
	}
}
Ufo.callback_functions.reload = function(url = false){
	if(url===false) {
		location.reload();
	}
	else {
		window.location.href = url;
	}
}
Ufo.callback_functions.dialog_post = function(form){
	window.dialog_form = form;
	if(form){
		for(var i = 0, element; element = form.elements[i++];) {
			if(element.type === "submit") {
				element.style.width = element.getBoundingClientRect()['width']+'px';
				element.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
				if(!BootSome.debug) {
					element.disabled = true;
				}
			}
		}
	}
}
Ufo.callback_functions.dialog_enable = function(string){
	if(window.dialog_form){
		for(var i = 0, element; element = window.dialog_form.elements[i++];) {
			if(element.type === "submit") {
				element.innerHTML = string;
				element.disabled = false;
			}
		}
	}
}
Ufo.callback_functions.main_get = function(){
	document.cookie = "lastpage="+Ufo.url('main');
}
Ufo.callback_functions.audio = function(soundfile){
	var audio = new Audio(soundfile);
	audio.autoplay = true;
	audio.play();
}
Ufo.callback_functions.savefile = function(string,filename,type = 'text/plain'){
	var a = document.createElement('a');
	a.href = window.URL.createObjectURL(new Blob([string], {type: type}));
	a.download = filename;
	a.click();
}
