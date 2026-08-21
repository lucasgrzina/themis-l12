import moment from 'moment'
const urlParser = document.createElement('a')

export function domain (url) {
  urlParser.href = url
  return urlParser.hostname
}

export function count (arr) {
  return arr.length
}

export function prettyDate (date) {
  var a = new Date(date)
  return a.toDateString()
}

export function pluralize (time, label) {
  if (time === 1) {
    return time + label
  }

  return time + label + 's'
}

export function jsonToSearcheable(json) {
  let out = [];
  for (var key in json){
    if (json[key]) {
    	out.push(key+":"+json[key]);	
    }
   }
   return out.join(";");	
}

export function dateFormat(value,format_o,format_i) {
  let _format_o = format_o || 'DD/MM/YYYY'
  let _format_i = format_i || 'YYYY-MM-DD'

  if (value) {
    return moment(value,_format_i).format(_format_o)  
  } else {
    return null;
  }
  
}

export function datetimeFormat(value,format_o,format_i) {
  let _format_o = format_o || 'DD/MM/YYYY HH:mm'
  let _format_i = format_i || 'YYYY-MM-DD HH:mm:ss'
  if (value) {
    return moment(value,_format_i).format(_format_o)
  } else {
    return null;
  }
}

export function currency(value, decimals, separators){
    if (typeof decimals === 'undefined') {
        decimals = 2;
    }
    if (typeof separators === 'undefined') {
        separators = ['.', "'", ','];
    }    
    return '$ ' + _currency(value,decimals, separators);
}

export function booleanLabel(value) {
  return (value === true || value == 1)
          ? '<span class="label bg-green">SI</span>'
          : '<span class="label bg-red">NO</span>'  
}

export function tagAssoc (value,attrs) {
      let _attrs = attrs;
      if (typeof _attrs[value] !== 'undefined') {
        return '<span class="label ' + _attrs[value][1] + '">' + _attrs[value][0] + '</span>';
      } 
      return '';
}

export function areaLabel(value) {
  switch (value) {
    case 1: return '<span class="label bg-navy">Previsional</span>';
    case 2: return '<span class="label bg-teal">Laboral</span>';
    case 3: return '<span class="label bg-purple">Civil</span>';
    case 4: return '<span class="label bg-orange">Comercial</span>';
    case 5: return '<span class="label bg-maroon">Societario</span>';
  }
}

export function parte(id) {
  switch(id) {
    case 'A':
      return 'Actora';
    case 'D':
      return 'Demandada';
    case 'T':
      return 'Tercero citado';
  };
}

export function minutesToHours(a) { 
  var hours = Math.trunc(a/60); 
  var minutes = a % 60; 
  console.log(hours +":"+ minutes); 

  return hours +":"+ minutes;
}

function _currency(value, decimals, separators) {
    decimals = decimals >= 0 ? parseInt(decimals, 0) : 2;
    separators = separators || ['.', "'", ','];
    var number = (parseFloat(value) || 0).toFixed(decimals);
    if (number.length <= (4 + decimals))
        return number.replace('.', separators[separators.length - 1]);
    var parts = number.split(/[-.]/);
    value = parts[parts.length > 1 ? parts.length - 2 : 0];
    var result = value.substr(value.length - 3, 3) + (parts.length > 1 ?
        separators[separators.length - 1] + parts[parts.length - 1] : '');
    var start = value.length - 6;
    var idx = 0;
    while (start > -3) {
        result = (start > 0 ? value.substr(start, 3) : value.substr(0, 3 + start)) + separators[idx] + result;
        idx = (++idx) % 2;
        start -= 3;
    }
    return (parts.length == 3 ? '-' : '') + result;
}

