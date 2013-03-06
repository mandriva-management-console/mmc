
  // func SHOWPROGRESS
  showing = 0
  function showprog(evt)
  {
    return
    mouseX=evt.pageX?evt.pageX:evt.clientX
    mouseY=evt.pageY?evt.pageY:evt.clientY
    document.getElementById('showp').style.left=mouseX+'px'
    document.getElementById('showp').style.top=mouseY+'px'
    showing = 1
    Effect.Appear('showp', {to:0.7})
  }
  function updatebox(evt)
  {
    return
    if (showing != 1)
      return 

    mouseX=evt.pageX?evt.pageX:evt.clientX
    mouseY=evt.pageY?evt.pageY:evt.clientY
    document.getElementById('showp').style.left=mouseX+'px'
    document.getElementById('showp').style.top=mouseY+'px'
  }
  function closeprog()
  {
    return
    showing = 0
    document.getElementById('showp').style.display='none';
  }

    relat_type = 'log'
    default_critery = 'log'
    relatorio = <%= @log_rel %>

  //document.body.onmousemove=updatebox(document.body.event)
  //document.body.onunload=closeprog()

