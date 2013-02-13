var BrowserDetect = {
	init: function () {
		this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
		this.version = this.searchVersion(navigator.userAgent)
			|| this.searchVersion(navigator.appVersion)
			|| "an unknown version";
		this.OS = this.searchString(this.dataOS) || "an unknown OS";
	},
	searchString: function (data) {
		for (var i=0;i<data.length;i++)	{
			var dataString = data[i].string;
			var dataProp = data[i].prop;
			this.versionSearchString = data[i].versionSearch || data[i].identity;
			if (dataString) {
				if (dataString.indexOf(data[i].subString) != -1)
					return data[i].identity;
			}
			else if (dataProp)
				return data[i].identity;
		}
	},
	searchVersion: function (dataString) {
		var index = dataString.indexOf(this.versionSearchString);
		if (index == -1) return;
		return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
	},
	dataBrowser: [
		{ 	string: navigator.userAgent,
			subString: "OmniWeb",
			versionSearch: "OmniWeb/",
			identity: "OmniWeb"
		},
		{
			string: navigator.vendor,
			subString: "Apple",
			identity: "Safari"
		},
		{
			prop: window.opera,
			identity: "Opera"
		},
		{
			string: navigator.vendor,
			subString: "iCab",
			identity: "iCab"
		},
		{
			string: navigator.vendor,
			subString: "KDE",
			identity: "Konqueror"
		},
		{
			string: navigator.userAgent,
			subString: "Firefox",
			identity: "Firefox"
		},
		{
			string: navigator.vendor,
			subString: "Camino",
			identity: "Camino"
		},
		{		// for newer Netscapes (6+)
			string: navigator.userAgent,
			subString: "Netscape",
			identity: "Netscape"
		},
		{
			string: navigator.userAgent,
			subString: "MSIE",
			identity: "Explorer",
			versionSearch: "MSIE"
		},
		{
			string: navigator.userAgent,
			subString: "Gecko",
			identity: "Mozilla",
			versionSearch: "rv"
		},
		{ 		// for older Netscapes (4-)
			string: navigator.userAgent,
			subString: "Mozilla",
			identity: "Netscape",
			versionSearch: "Mozilla"
		}
	],
	dataOS : [
		{
			string: navigator.platform,
			subString: "Win",
			identity: "Windows"
		},
		{
			string: navigator.platform,
			subString: "Mac",
			identity: "Mac"
		},
		{
			string: navigator.platform,
			subString: "Linux",
			identity: "Linux"
		}
	]

};
BrowserDetect.init();

function check_browser()
{

   // Check Browser Name/Version
   //alert(BrowserDetect.browser);
   if (BrowserDetect.browser != "Firefox" && BrowserDetect.browser != "Mozilla")
   {
      document.getElementById('errmsg').innerHTML = "<font color='red'>Acesso desativado"+
                                                    " devido a incompatibilidade com o browser."+
                                                    " Por favor, faça o download do Mozilla Firefox em <a "+
                                                    " target='_blank' href='http://br.mozdev.org/'>http://br.mozdev.org/</a>.</font>"

      alert("Você está utilizando o "+ BrowserDetect.browser +' '+ BrowserDetect.version +' no '+ BrowserDetect.OS +'.\n'+
            "O sistema Rsys Tinecon requer o Mozilla Firefox ou\n"+
            "Mozilla Iceape.\n\n"+
            "Se não possuir um desses browsers instalado, recomendamos\n"+
            "que faça o download em http://br.mozdev.org/.\n\n"+
            "contato@tinecon.com.br\n"+
            "http://www.tinecon.com.br/consultorialinux");
      
      return;

     /* check for a cookie */
   } else if (document.cookie == "")  
   {
      document.getElementById('errmsg').innerHTML = "<font color='red'>Acesso desativado."+
                                                    " Habilite o suporte a Cookies em seu browser e tente novamente."+
                                                    " Veja <a target='_blank' href='http://www.ead.ufms.br/marcelo/help-moodle/firefox_cookie.html'>"+
                                                    "como habilitar cookies no Mozilla Firefox</a>.</font>"
      alert("Seu browser não está com o suporte a Cookies habilitado.\n"+
            "O sistema Rsys Tinecon requer esta funcionalidade para seu funcionanmento.\n\n"+
            "contato@tinecon.com.br\n"+
            "http://www.tinecon.com.br/consultorialinux");

      return;

   } else
   {
      document.getElementById('name').disabled = false;
      document.getElementById('password').disabled = false;
      document.getElementById('commit').disabled = false;
   }

}


