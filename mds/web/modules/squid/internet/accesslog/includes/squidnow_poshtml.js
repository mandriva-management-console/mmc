
       var thetable = document.getElementById('tblbox')

        function getWindowWidth()
        { 
          if (document.all) // IE
          { 
            return document.body.offsetWidth; 
          } 
          else // FF
          { 
            return window.innerWidth; 
          }
        }
        //wWidth = window.innerWidth
        wWidth = getWindowWidth()
        space = 290
        initial_left = (wWidth - space) + 'px'
        document.getElementById('controlcontainer').style.left = initial_left

        //document.getElementById('controlcontainer').style.visibility = 'visible'

        //objwidth = 0 //document.getElementById('control').style.width
        //wHeight = window.innerHeight;
        //document.getElementById('boxA').style.top = '10px'

        function reposition(element)
        {
                element.style.top = '60px'
                element.style.left = initial_left
        }

        trans_state = -1
        function trans (obj)
        {
                trans_state = -trans_state
                //alert(trans_state)
                if (trans_state != 1)
                {
                        obj.style.opacity = 1.0
                        document.getElementById('transbutton').value = "opacity"
                }
                else
                {
                        obj.style.opacity = 0.2
                        document.getElementById('transbutton').value = "      show     "
                }

        }

        setTimeout("timer();Effect.Appear('controlcontainer',{duration: 3.0});",2000)

