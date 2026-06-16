/* formatNumber: PARAMETERS... 
num: the decimal number to convert (must be numeric) 
dec: number of resulting decimal places to keep 
thou: the character to use for thousands separator 
pnt: the character to use for decimal point 
curr1: Preceding Currency Symbol 
curr2: Trailing Currency symbol 
n1: Preceding Negative character 
n2: Trailing Negative character 
*/  
function Format_Nbr(num, dec, thou, pnt, curr1, curr2, n1, n2) {  
    var x = Math.round(num * Math.pow(10,dec));  
    if (x >= 0) n1 = n2 = '';  
    var y = (''+ Math.abs(x)).split('');  
    var z = y.length - dec;  
    if (z<0) z--;  
    for (var i = z; i < 0; i++) y.unshift('0');  
    if (z<0) z = 1;  
    y.splice(z, 0, pnt);  
    if (y[0] == pnt) y.unshift('0');  
    while (z > 3) { z-=3; y.splice(z,0,thou); }  
    var r = curr1 + n1 + y.join('') + n2 + curr2;  
    return r;  
}  