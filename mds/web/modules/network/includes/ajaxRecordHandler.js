/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2010 Mandriva, http://www.mandriva.com
 * (c) 2011 http://www.osinit.ru
 *
 * $Id:
 *
 * This file is part of Mandriva Management Console (MMC).
 *
 * MMC is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * MMC is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MMC; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

function onRecordTypeChanged(zone){
    var cb = document.getElementById("recordtype");
    var type = cb.options[cb.selectedIndex].innerHTML;
    var container = document.getElementById("typecontentdiv");
    while(container.hasChildNodes()){
	container.removeChild(container.firstChild);
    }
    sendAjaxRequest("getRecordTypeContent",type + " " + zone);

}

function createRequestObject() {
    if (typeof XMLHttpRequest === 'undefined') {
	XMLHttpRequest = function() {
	    try { return new ActiveXObject("Msxml2.XMLHTTP.6.0"); }
            catch(e) {}
            try { return new ActiveXObject("Msxml2.XMLHTTP.3.0"); }
            catch(e) {}
            try { return new ActiveXObject("Msxml2.XMLHTTP"); }
            catch(e) {}
            try { return new ActiveXObject("Microsoft.XMLHTTP"); }
            catch(e) {}
            throw new Error("This browser does not support XMLHttpRequest.");
        };
    }
    return new XMLHttpRequest();
}
                                                                            
                                        
var http = createRequestObject();

                                        
function sendAjaxRequest(request,params) {
    var suffix = 'request=' + request;
    if (params)
	suffix += "&params="+params;
    http.open('get', 'modules/network/network/ajaxRecordTypeContentRequests.php?'+suffix);
    http.onreadystatechange = handleResponse;
    http.send(null);
}
                                                    
function handleResponse() {
    if(http.readyState != 4)
	return;
    
    var response = http.responseText;
    var re = /id=(\w+)&value=(.*)/;
    var params = re.exec(response);
    
    var id = params[1];
    var value = params[2] + response.substr(params[0].length+1);
    //alert(params[0] + " " + (params[0].length+1));
    
    var container = document.getElementById("typecontentdiv");
    //document.title = "lzzlzz";
    switch (id){
        case 'getRecordTypeContent':
            container.innerHTML = value;
    	    var sd = document.createElement('scriptsdiv'); 
    	    sd.innerHTML = value;
    	    extractScripts(sd,container);
    	    
    	    break;
    }
}

function extractScripts(e, dest) {
    if (e.nodeType != 1) return; //if it's not an element node, return
    if (e.tagName.toLowerCase() == 'script') {
	var $s = document.createElement('script'); 
	if (e.attributes.getNamedItem("src"))
	    $s.setAttribute("src",e.attributes.getNamedItem("src").value);
	$s.text = e.text; 
	dest.appendChild($s);
    } else {
	var n = e.firstChild;
    	while ( n ) {
    	    if ( n.nodeType == 1 ) 
    		extractScripts(n, dest); //if it's an element node, recurse
    	    n = n.nextSibling;
    	}
    }
}
                                                    