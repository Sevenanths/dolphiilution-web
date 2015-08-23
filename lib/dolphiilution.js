function getParameterByName(name)
{
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function escape(string)
{
	return string.replace(/\\n/g, "\\n")
                 .replace(/\\'/g, "\\'")
                 .replace(/\\"/g, '\\"')
                 .replace(/\\&/g, "\\&")
                 .replace(/\\r/g, "\\r")
                 .replace(/\\t/g, "\\t")
                 .replace(/\\b/g, "\\b")
                 .replace(/\\f/g, "\\f");
}

function initCoverflow()
{
    // EVENT HANDLERS
    document.addEventListener('keydown', keyDown, false);

    // ACTIVE LAYER
    patching = false;
    layer = 0;
    coverflowpane   = document.getElementsByClassName("pane coverflow")[0];
    optionspane     = document.getElementsByClassName("pane options")[0];
    patchingpane    = document.getElementsByClassName("pane patching")[0];
    stateelement = document.getElementById('state');
    loadingelement = document.getElementById('loading');

    // COVERFLOW
    _index = 0,
    _coverflow = null,
    _prevLink = null,
    _nextLink = null,
    _albums = [],

    // Constants
    OFFSET = 60; // pixels
    ROTATION = 35; // degrees
    BASE_ZINDEX = 10; // 
    MAX_ZINDEX = 50; //

    _coverflow = document.getElementById("coverflow");

    Object.keys(games).forEach(function (id)
    {
        var opt = document.createElement("section");
         opt.setAttribute("data-cover", games[id].boxart)
         opt.setAttribute("id", id);
        
        _coverflow.appendChild(opt);
    });

    _albums = Array.prototype.slice.call(document.querySelectorAll('section'));
    _index = Math.floor(_albums.length / 2);

    ratio =  window.innerHeight / 1000;

    _coverflow.style.transform = "scale(" + ratio + ", " + ratio + ")";

    // display covers
    for( var i = 0; i < _albums.length; i++ )
    {
        var url = _albums[i].getAttribute("data-cover");
        _albums[i].style.backgroundImage = "url("+ url  +")";
    }

    // do important stuff
    render();
}

function render()
{
    // loop through albums & transform positions
    for( var i = 0; i < _albums.length; i++ ) {
 
        // before 
        if( i < _index ) {
            _albums[i].style.transform = "translateX( -"+ ( OFFSET * ( _index - i  ) ) +"% ) rotateY( "+ ROTATION +"deg )";
            _albums[i].style.zIndex = BASE_ZINDEX + i;  
        } 

        // current
         if( i === _index ) {
            _albums[i].style.transform = "rotateY( 0deg ) translateZ( 140px )";
            _albums[i].style.zIndex = MAX_ZINDEX;
            document.getElementById('info').innerHTML = games[_albums[i].id].title;  
        } 

         // after
        if( i > _index ) {
            _albums[i].style.transform = "translateX( "+ ( OFFSET * ( i - _index  ) ) +"% ) rotateY( -"+ ROTATION +"deg )";
            _albums[i].style.zIndex = BASE_ZINDEX + ( _albums.length - i  ); 
        }         
    
    }
    id = _albums[_index].id;
};

function keyDown(event)
{
    if (!patching)
    {
        switch (event.keyCode)
        {
            case 38: //up
                shiftPaneUp();
                event.preventDefault();
            break; 
            case 40: //down
                shiftPaneDown();
                event.preventDefault();
            break;
            case 37: // left
                if (layer == 0)
                {
                    flowRight();
                    event.preventDefault();
                }
            break; 
            case 39: // right
                if (layer == 0)
                {
                    flowLeft();
                    event.preventDefault();
                }
            break;
            case 13: // enter/accept
                switch (layer)
                {
                    case -1:
                        update();
                    break;
                    case 0:
                        selectGame();
                    break;
                    case 1:
                        patch();
                    break;
                }
                event.preventDefault();
            break;
        }
    }
}

function flowRight()
{
   // check if has albums 
   // on the right side
   if( _index ) {
        _index--;
        render();
   }  
};

/**
 * Flow to the left
 **/
function flowLeft()
{

    // check if has albums 
   // on the left side
   if( _albums.length > ( _index + 1)  ) {
        _index++;
        render();
   }
  
};

function shiftPaneDown()
{
    switch (layer)
    {
        case 0:
            layer = -1;
            coverflowpane.style.top = "100%";
        break;
        case 1:
            layer = 0;
            patchingpane.style.top  = "100%";
        break;
    }
}

function shiftPaneUp()
{
    switch (layer)
    {
        case -1:
            layer = 0;
            coverflowpane.style.top = "0%";
        break;
        case 0:
            selectGame();
            layer = 1;
            patchingpane.style.top = "0%";
        break;
    }
}

function selectGame()
{
    patchingpane.style.backgroundImage = "url(" + games[id].boxart + ")";

    var xhr = new XMLHttpRequest();
    xhr.open('GET', './?api=game&id=' + id, true);
    xhr.onload = function ()
    {
        // do something to response
        patchingpane.innerHTML = this.responseText;
    }
    xhr.send();
}

function update()
{
    updateStatus("Updating library..");

    var xhr = new XMLHttpRequest();
    xhr.open('GET', './?api=update', true);
    xhr.onload = function ()
    {
        // do something to response
        var response = JSON.parse(this.responseText);
        updateResponseStatus(response, "Library updated.", "Error whilst updating..", true);
    }
    xhr.send();
}

function patch()
{
    shiftPaneDown();
    shiftPaneDown();

    updateStatus("Patching..");

    oFormElement = document.getElementById("patch");

    var xhr = new XMLHttpRequest();
    xhr.onload = function()
    {
        updateResponseStatus(JSON.parse(xhr.responseText), "Patching successful!", "Patching failed :(", false);
    }
    xhr.open (oFormElement.method, oFormElement.action, true);
    xhr.send (new FormData (oFormElement));
}

function updateStatus(message)
{
    patching = true;
    loading.style.display = "inline";

    stateelement.style.display = "none";
    stateelement.innerHTML = message;
}

function updateResponseStatus(response, successmessage, failmessage, refresh)
{
    stateelement.style.display     = "inline-block";
        switch (response['state'])
        {
            case "success":
                stateelement.innerHTML = successmessage;
                if (refresh)
                location.reload();
            break;
            default:
                stateelement.innerHTML = failmessage;
            break;
        }
    loading.style.display  = "none";
    patching = false;
}