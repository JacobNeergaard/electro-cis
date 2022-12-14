/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
var BootSomeForms = (function(){
	function initialize(className){
		return function(source){
			if(typeof source.removeAttribute=='function'){
				source.removeAttribute('onclick');
			}
			if(typeof Popper=='function'){
				initClasses()
				if(classes[className]) new classes[className](...arguments);
			}
		}
	}

	var classes = {};
	var monthLengths = {1:31,2:28,3:31,4:30,5:31,6:30,7:31,8:31,9:30,10:31,11:30,12:31};
	var initClasses = function(){
		var DatePickerTemplate = (()=>{
			var template = document.createElement('template');
			template.innerHTML =
			'<div class="bs-datepicker popover fade bs-popover-bottom" style="display: none;">'+
				'<div class="arrow"></div>'+
				'<div class="popover-header">'+
					'<div class="btn-group d-flex">'+
						'<button class="btn btn-primary" data-action="prev" type="button" tabindex="0"><span class="fas fa-chevron-left"></span></button>'+
						'<button class="btn btn-secondary w-100" disabled="" data-content="month_year"></button>'+
						'<button class="btn btn-primary" data-action="next" type="button" tabindex="0"><span class="fas fa-chevron-right"></span></button>'+
					'</div>'+
				'</div>'+
				'<div class="popover-body">'+
					'<div class="table-responsive">'+
						'<table class="table bs-datepicker text-center"><tbody data-calendar="">'+
							'<tr>'+
								'<th></th>'+
								'<th>M</th><th>T</th><th>W</th><th>T</th><th>F</th>'+
								'<th class="table-light">S</th>'+
								'<th class="table-light">S</th>'+
							'</tr>'+
							'<template data-name="calendar-row">'+
								'<tr data-calendar-row="">'+
									'<th></th>'+
									'<td></td><td></td><td></td><td></td><td></td>'+
									'<td class="table-light"></td>'+
									'<td class="table-light"></td>'+
								'</tr>'+
			'</template></tbody></table></div></div></div>';
			return template;
		})()

		class DatePicker {
			constructor(source, onWrite){
				if(typeof Intl == 'object' && typeof Intl.DateTimeFormat == 'function'){
					this.monthFormatter = new Intl.DateTimeFormat('en-US',{month:'long',year:'numeric'});
				} else {
					this.monthFormatter = {format:(date)=>date.getMonth()+'/'+date.getYear()};
				}

				this.onWrite = onWrite;
				this.source = source;
				source.addEventListener('click',()=>this.show());
				source.addEventListener('input',()=>this.update());

				this.show();
			}

			build(month, year){
				if(typeof this.popover == 'undefined'){
					this.popover = DatePickerTemplate.content.cloneNode(true).firstChild;
					this.source.parentElement.appendChild(this.popover);
				}
				var template = this.popover.querySelector('template[data-name="calendar-row"]');
				var calendar = this.popover.querySelector('[data-calendar]');
				if(!template || !calendar) return;
				var rows = calendar.querySelectorAll('[data-calendar-row]');
				for(var i=0;i<rows.length;i++){
					rows[i].parentElement.removeChild(rows[i]);
				}
				var last_month = month-1;
				var last_month_year = year;
				var next_month = month+1;
				var next_month_year = year;
				if(month==1){
					last_month = 12;
					last_month_year = year-1;
				} else if(month==12){
					next_month = 1;
					next_month_year = year+1;
				}
				var month_end = this.monthLength(month,year);
				var last_month_end = this.monthLength(last_month,last_month_year);

				const build_row=(week_number, start_date)=>{
					var row = template.content.cloneNode(true);
					var cells = Array.from(row.firstChild.children);
					cells[0].textContent = week_number
					var date = start_date;
					for(var i=1;i<8;i++){
						var button_class = undefined;
						if(date <= 0){
							var real_date = date + last_month_end;
							var real_month = last_month;
							var real_year = last_month_year;
							button_class = 'table-secondary';
						} else if(date > month_end){
							real_date = date - month_end;
							real_month = next_month;
							real_year = next_month_year;
							button_class = 'table-secondary';
						} else {
							real_date = date;
							real_month = month;
							real_year = year;
						}
						if(real_year==this.value.year && real_month==this.value.month && real_date==this.value.date){
							button_class = 'table-primary';
							cells[i].focus();
						} else if(real_year==this.today.year && real_month==this.today.month && real_date==this.today.date){
							button_class = 'table-info';
						}
						cells[i].textContent = real_date;
						this.setAction(cells[i],real_date,real_month,real_year);
						if(button_class){
							cells[i].className = button_class;
						}
						date++;
					}
					calendar.appendChild(row);
				}

				var first = new Date(year,month-1,1);
				// get week day of first of this month, shifted so 0-6 is monday-sunday
				var first_weekday = (first.getDay()+6)%7;
				// set date to monday of the first week of the month
				// values < 1 is a date of previous month; 0 = last day of previous month
				var date = 1-first_weekday;
				var week_number = first.getWeek();
				while(date <= month_end){
					build_row(week_number,date);
					week_number += 1;
					date += 7;
				}

				var title = this.popover.querySelector('[data-content=month_year]');
				var prev = this.popover.querySelector('[data-action=prev]');
				var next = this.popover.querySelector('[data-action=next]');

				title.textContent = this.monthFormatter.format(first);
				prev.onclick=()=>{this.build(last_month, last_month_year);this.popper.update()};
				next.onclick=()=>{this.build(next_month, next_month_year);this.popper.update()};
			}

			monthLength(month,year){
				if(month==2 && year%4==0&&year%100!=0||year%400==0) return 29;
				else return monthLengths[month];
			}

			setAction(element,date,month,year){
				element.addEventListener('keypress',event=>{
					if(event.charCode===32||event.keyCode===13) event.target.dispatchEvent(new Event('click'));
				});
				element.addEventListener('click',()=>this.write(date,month,year));
				element.setAttribute('role','button');
				element.setAttribute('tabindex',0);
			}

			write(date,month,year){
				if(month<10) month='0'+month;
				if(date<10) date='0'+date;
				var value = year+'-'+month+'-'+date;
				if(this.source && this.source.nodeName=='INPUT'){
					this.source.value = value;
					this.source.dispatchEvent(new Event('change'));
				}
				if(typeof this.onWrite=='function')this.onWrite(value);
				this.hide();
			}

			getValue(){
				if(this.source){
					if(this.source.nodeName=='INPUT' && this.source.value){
						var raw = this.source.value;
					} else if(this.source.dataset && this.source.dataset.date){
						raw = this.source.dataset.date;
					} else if(this.source.textContent){
						raw = this.source.textContent;
					} else {
						raw = String(this.source)
					}
					var value = new Date(raw);
				}
				if(typeof value != 'undefined') return {date:value.getDate(),month:value.getMonth()+1,year:value.getFullYear()};
				else return {date:undefined,month:undefined,year:undefined};
			}

			getSuggest(){
				if(this.source && this.source.dataset && this.source.dataset.suggest){
					var suggest = this.source.dataset.suggest;
					var suggest_element = document.getElementById(suggest);
					if(suggest_element){
						var str_date = suggest_element.value;
					} else {
						var str_date = suggest;
						var date = str_date.split('.');
						if(date.length == 3){
							var month = Number(date[1]);
							var year = Number(date[2]);
						}
					}
					if(typeof str_date == 'undefined') return;
					if(!month || !year){
						var value = new Date(str_date);
						month = value.getMonth()+1;
						year = value.getFullYear();
					}
					if(!isNaN(month) && !isNaN(year)){
						return {month:month, year:year};
					}
				}
			}

			show(){
				if(this.isShown) return;
				if(this.hide_timeout) clearTimeout(this.hide_timeout);
				if(this.source.nodeName=='INPUT') this.source.type='text';
				var today = new Date();
				this.today = {date:today.getDate(),month:today.getMonth()+1,year:today.getFullYear()};
				this.value = this.getValue();
				if(this.value.month && this.value.year){
					var anchor = this.value;
				} else {
					anchor = this.getSuggest() || this.today;
				}
				this.build(anchor.month,anchor.year);
				this.popper = new Popper(this.source,this.popover,{
					placement:'bottom-start',
					onCreate:data=>this.popperUpdate(data),
					onUpdate:data=>this.popperUpdate(data)
				});
				this.popover.style.display='';
				this.popover.classList.add('show');
				this.windowEventListener = event=>this.clickHandler(event);
				window.addEventListener('click',this.windowEventListener);
				this.isShown = true;
			}

			hide(){
				this.isShown = false;
				if(this.source.nodeName=='INPUT') this.source.type='date';
				this.popover.classList.remove('show');
				this.hide_timeout = setTimeout(()=>{
					this.popover.style.display='none';
					this.popper.destroy();
					delete this.popper;
					delete this.hide_timeout;
				},150);
				if(this.windowEventListener){
					window.removeEventListener('click',this.windowEventListener);
					delete this.windowEventListener
				}
			}

			update(){
				var value = this.getValue();
				if(isNaN(value.date)) return;
				this.value = value;
				this.build(this.value.month||this.today.month,this.value.year||this.today.year);
				this.popper.update();
			}

			popperUpdate(data){
				if(data.flipped != this.isFlipped){
					this.popover.classList.toggle('bs-popover-bottom', !data.flipped);
					this.popover.classList.toggle('bs-popover-top', data.flipped);
					this.isFlipped = data.flipped;
				}
			}

			clickHandler(event){
				var elem = event.target;
				while(elem){
					if(elem==this.popover||elem==this.source) return;
					elem = elem.parentElement;
				}
				this.hide();
			}
		}
		classes.DatePicker=DatePicker;
		initClasses=function(){};
	}

	return {
		date: initialize('DatePicker')
	};
})();


// Copy pasting weeknumber.js
// ==========================

// This script is released to the public domain and may be used, modified and
// distributed without restrictions. Attribution not necessary but appreciated.
// Source: http://weeknumber.net/how-to/javascript 

// Returns the ISO week of the date.
Date.prototype.getWeek = function() {
  var date = new Date(this.getTime());
   date.setHours(0, 0, 0, 0);
  // Thursday in current week decides the year.
  date.setDate(date.getDate() + 3 - (date.getDay() + 6) % 7);
  // January 4 is always in week 1.
  var week1 = new Date(date.getFullYear(), 0, 4);
  // Adjust to Thursday in week 1 and count number of weeks from date to week1.
  return 1 + Math.round(((date.getTime() - week1.getTime()) / 86400000
                        - 3 + (week1.getDay() + 6) % 7) / 7);
}

// Returns the four-digit year corresponding to the ISO week of the date.
Date.prototype.getWeekYear = function() {
  var date = new Date(this.getTime());
  date.setDate(date.getDate() + 3 - (date.getDay() + 6) % 7);
  return date.getFullYear();
}
