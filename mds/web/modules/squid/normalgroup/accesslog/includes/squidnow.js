
/*
  Visualização dos logs do squid em tempo real
  Antonio Lobato 
  lobato@tinecon.com.br
  02/Jul/2007        
*/

/*try {
  netscape.security.PrivilegeManager.enablePrivilege("UniversalBrowserRead");
} catch (e) {
  alert("Permission UniversalBrowserRead denied.");
}*/

var update = 1
var interval = 3
var counter = 0
var param = '/cgi-bin/squidnow.pl'
var wating = 0
var answered
var type = 'text/plain'
var req;
var toutid;
var data_history = new Array();
data_history[0] = "";
var hist_max_size = 20;
var hist_position = 0;

function retr(url)
{ 
	if (window.XMLHttpRequest)
	{
		req = new XMLHttpRequest()
		if (req.overrideMimeType) 
		{
			req.overrideMimeType(type)
		}
	} 
	else if (window.ActiveXObject) 
	{
		try {
			req = new ActiveXObject("Msxml2.XMLHTTP")
		} catch (e)
		{
			try {
				req = new ActiveXObject("Microsoft.XMLHTTP")
			} catch (e) {}
		}
	}


	req.onreadystatechange = function()
	{ 

		if(req.readyState == 4)
		{
			if(req.status != 200)
			{
				alert ("error code: "+ req.status +"error msg: "+ req.statusText)
			} else
			{
				if (data_history[0] != req.responseText)
				{
					data_history.unshift(req.responseText)
					if (data_history[1] == "") data_history[1] = null;
					data_history.length = hist_max_size
					check_history()
				}
                                //alert(req.responseText)
				genhtml(req.responseText)
				update_bar();
			}	
		}
	} 
	req.open("GET", url, true)
	req.send(null)
}

function go_history(direction)
{

	if (update == 1 )
		pausecont(document.getElementById('pausebutton'))

	if (direction == -1)
	{
		hist_position++
	}
	else if (direction == 1)
	{
		hist_position--
	}
	else
	{
		return
	}
	check_history()
	genhtml(data_history[hist_position])
	update_bar();
}

function check_history()
{
	if (hist_position >= check_array(data_history))
		document.getElementById('prev').disabled = true
	else
		document.getElementById('prev').disabled = false
	if (hist_position <= 0)
		document.getElementById('next').disabled = true
	else
		document.getElementById('next').disabled = false
	//document.getElementById('debug').innerHTML = check_array(data_history)+"-"+hist_position
}

function check_array(data_history)
{
	count = 20
	for (i=0; i<=20; i++)
	{
		if (data_history[i] == null)
			count--
	}
	return count
}

function update_bar()
{
	bar = document.getElementById('bar')
	filled = check_array(data_history)
	bar_html = "<img src='modules/squid/normalgroup/accesslog/images/fillet.gif'>"
	for (i=0; i<20; i++)
	{
		actual = "<img src='modules/squid/normalgroup/accesslog/images/blank.gif'>"
		if (i <= filled)
			actual = "<img src='modules/squid/normalgroup/accesslog/images/full.gif'>"
		if (hist_position == i)
			actual = "<img src='modules/squid/normalgroup/accesslog/images/mark.gif'>"
		bar_html = actual + bar_html;
	}
	bar_html = "<modules/squid/normalgroup/accesslog/img src='images/fillet.gif'>" + bar_html;
	bar.innerHTML = bar_html;
}

function timer()
{
	retr(param)
	toutid = setTimeout('timer()',interval*1000);
}

function setinterval(selvalue)
{
	interval = selvalue;
	if (update == 1)
	{
		clearTimeout(toutid);
		toutid = setTimeout('timer()',interval*1000);
	}
}

function pausecont(button)
{
	update = -update

	if (update == 1) // Inicia o refresh
	{
		hist_position = 0
		document.getElementById('next').disabled = true
		update_bar()
		button.value = '   Pausar    '
        	document.getElementById('img').title = "Refresh on"
        	document.getElementById('img').src = "modules/squid/normalgroup/accesslog/images/on.gif"
		timer()
	
	} else // Pausa o refresh
	{
		button.value = 'Continuar'
        	document.getElementById('img').title = "Refresh off"
        	document.getElementById('img').src = "modules/squid/normalgroup/accesslog/images/pause.gif"
		clearTimeout(toutid);
		if (req)
			req.abort();
	}
}

function resetall()
{
	document.form1.formuser.value = ''
	document.form1.formip.value = ''
	document.form1.formurl.value = ''
}

function genhtml(text)
{
        lines = text.split("\n")
        
	for (k= thetable.rows.length-1; k>0; k--)
		thetable.deleteRow(k);
       
        form1 = document.getElementById('form1');
        
        formuser = form1.formuser.value
	notuser = 0
	if( formuser.match(/^-.+/) )
	{	
		notuser = 1
		formuser = formuser.replace(/^-/, "");
	}
	
	formip = form1.formip.value;
	notip = 0
	if( formip.match(/^-.+/) )
		notip = 1
	if( formip.match(/^-/) )
		formip = formip.replace(/^-/, "");

	formurl = form1.formurl.value;
	noturl = 0
	if( formurl.match(/^-.+/) )
	{	
		noturl = 1
		formurl = formurl.replace(/^-/, "");
	}

        document.getElementById('wait_warn').innerHTML = ''
        document.getElementById('tbheader').innerHTML = '<TH>Hour</TH><TH>User</TH><TH>IP</TH><TH align=left>Object</TH>'

	for (i=0; i<lines.length-2; i++)
        {
                linha = lines[i]
                linha = linha.replace(/\ +/,' ')
                fields = linha.split(" ")
		hour = fields[0];
		user = fields[1];
		ip = fields[2];
		url = fields[3];		
	
		if (formuser != "")
			if(notuser == 1)
			{
				if (user.match(formuser))
					{
						continue;
					}
			}
			else
			{
				if (! user.match(formuser))
					{
						continue;
					}
			}		
		if (formip != "")
			if(notip == 1)
			{
				if (ip.match(formip))
					{
						continue;
					}
			}
			else
			{
				if (! ip.match(formip))
					{
						continue;
					}
			}		
		
		if (formurl != "")
			if(noturl == 1)
			{
				if (url.match(formurl))
					{
						continue;
					}
			}
			else
			{
				if (! url.match(formurl))
					{
						continue;
					}
			}		
	
                cellclass = ''
                if (fields[4] == 1) cellclass = 'redfont'
                fundo = '#f3f3f3'
                if (i%2) fundo = ''
        
		row = thetable.tBodies[0].appendChild(document.createElement('tr'));
		row.setAttribute('bgcolor', fundo,0);
		if ( BrowserDetect.browser == "Explorer" )
			row.setAttribute('className', cellclass,0);
		else
			row.setAttribute('class', cellclass,0);

		// divide em colunas
                for (j=0; j<4; j++) // j<fields.length para os campos pertinentes
                {
			cell = fields[j];
			var y = document.createElement('td');
			//y.setAttribute('class', cellclass,0);
			y.setAttribute('style', 'font-size: 13;',0);
			if (j<3) y.setAttribute('align', 'center',0);
			y.appendChild(document.createTextNode(cell));
			row.appendChild(y);
                }
        }
        
}


