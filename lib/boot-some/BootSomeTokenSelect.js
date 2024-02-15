/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
var BootSomeTokenSelect = (function(){
	if(typeof TinyTemplate == 'undefined') return {
		'remove':function(){},
		'set':function(){}
	};
	function set(select){
		if(select.value=='') return;
		var tokenfield = select.parentElement.querySelector('.bootsome-token-container');
		var token = tokenfield.querySelector('[data-token-value="'+select.value+'"]');
		if(!token) return insert_token(select, tokenfield);
		select.value='';
	}
	function remove(token, event){
		event.preventDefault();
		var component = token.parentElement.parentElement;
		var key = token.dataset.tokenValue;
		var option = component.querySelector('select option[value=\"'+key+'\"]');
		if(option) option.disabled=false;
		token.remove();
	}
	function insert_token(select, tokenfield){
		var data = {
			'value':select.value,
			'label':select.selectedOptions[0].textContent
		}
		var div = TinyTemplate.activate('bootsome-token-template',data,tokenfield,true);
		tokenfield.appendChild(div);
		select.selectedOptions[0].disabled=true;
		select.value='';
		return div;
	}
	return {
		'remove': remove,
		'set':set
	};
})();
