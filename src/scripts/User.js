function changeFollowedList() {
    var btn = document.getElementById("btnFollowOwner");
    var idUser = document.getElementById("ownerId").value;
    var imgUser = document.getElementById("ownerImage").src;
    console.log(imgUser);
    var nameUser = document.getElementById("ownerName").innerText;

    // If we follow
    if (Array.from(btn.classList).indexOf("user-followed") > -1) addFollowed(idUser, imgUser, nameUser);
    // If we unfollow
    else removeFollowed(idUser);
}
function editDesc(cancel = false) {
    var div = document.getElementById("desc");
    var txt = document.getElementById("descTxt");
    var btn = document.getElementById("btnEditDesc");
    var btnCancel = document.getElementById("btnCancelDesc");

    // We want to edit
    if (Array.from(div.classList).indexOf("user-hidden") == -1) {
        txt.value = div.children[0].innerText;
        div.classList.toggle("user-hidden");
        txt.classList.toggle("user-hidden");
        btn.innerText = "Save"
        btnCancel.classList.toggle("user-hidden");
    }
    // We want to save new desc
    else {
        div.classList.toggle("user-hidden");
        txt.classList.toggle("user-hidden");
        btn.innerText = "Edit"
        btnCancel.classList.toggle("user-hidden");
        if (!cancel) {
            div.children[0].innerText = txt.value;
            document.getElementById("descHiddenInput").value = txt.value;
            submitForm(null, "formEditDesc");
        }
    }
}
function changeUserPage(btn) {
    var divCreate = document.getElementById("divCreate");
    var divSearch = document.getElementById("divSearch");

    // We want to edit / create
    if (Array.from(btn.classList).indexOf("bg-primary-color") > -1) {
        btn.innerText = "Cancel";
    }
    // We want to cancel
    else {
        btn.innerText = "Create or edit videos";
        document.getElementById("txtTitle").value = "";
        document.getElementById("imgNewVideo").src = "https://static.thenounproject.com/png/340719-200.png";
        document.getElementById("txtUrlImg").value = "https://static.thenounproject.com/png/340719-200.png";
        document.getElementById("txtNewDesc").value = "";
        document.getElementById("txtPrice").value = 0;
        document.getElementById("selectTheme").selectedIndex = 0;
        document.getElementById("spanFileName").innerText = "File name:";
        document.getElementById("spanFileType").innerText = "Type:";
        document.getElementById("inputEdit").value = "-1";
        document.getElementById("inputDelete").value = "-1";
    }

    btn.classList.toggle("bg-primary-color");
    btn.classList.toggle("bg-warning");
    divCreate.classList.toggle("user-hidden");
    divSearch.classList.toggle("user-hidden");
}
function openFile() {
    document.getElementById("file").click();
}
function loadFile() {
    var file = document.getElementById("file").files[0];
    if (file == undefined) return;
    var name = file.name.split(".");
    document.getElementById("spanFileName").innerText = "File name: " + name.slice(0, -1).join(".");
    document.getElementById("spanFileType").innerText = "Type: " + name[name.length-1];
    var p = document.getElementById("txtPrice");
    p.disabled = false;
    document.getElementById("typeVideo").value = "file";
}
function useUrl(cancel = false) {
    document.getElementById("divUrl").classList.toggle("user-hidden");

    if (cancel) return;
    document.getElementById("spanFileName").innerText = "Link: " + document.getElementById("txtUrl").value;
    document.getElementById("spanFileType").innerText = "Type: Youtube video";
    var p = document.getElementById("txtPrice");
    p.value = 0;
    p.disabled = true;
    document.getElementById("typeVideo").value = "youtube";
}
function useUrlImg(cancel = false) {
    document.getElementById("divUrlImg").classList.toggle("user-hidden");

    if (cancel) return;
    document.getElementById("imgNewVideo").src = document.getElementById("txtUrlImg").value;
}
function subForm() {
    document.getElementById("txtPrice").disabled = false;
}
function editVideo() {
    var div = videoItemInContext;
    document.getElementById("txtTitle").value = div.children[1].innerText;
    document.getElementById("imgNewVideo").src = div.children[0].getElementsByTagName("img")[0].src;
    document.getElementById("txtUrlImg").value = div.children[0].getElementsByTagName("img")[0].src;
    document.getElementById("txtNewDesc").value = div.children[0].children[2].innerText;
    document.getElementById("txtPrice").value = div.getAttribute("data-price");
    var select = document.getElementById("selectTheme");
    var th = div.getAttribute("data-theme");
    for (var i = 0; i < select.options.length; i++) {
        if (select.options[i].value == th) select.selectedIndex = i;
    }
    document.getElementById("btnEditVid").click();
    document.getElementById("inputEdit").value = div.getAttribute("data-id");
}
function deleteVideo() {
    if (confirm("Do you really want to delete your video?")) {
        document.getElementById("inputDelete").value = videoItemInContext.getAttribute("data-id");
        document.getElementById("btnSave").click();
    }
}




// ===========================================================================
// Contextual Menu ===========================================================
// ===========================================================================
"use strict";

// Variables ==============================================================
var contextMenuClassName = "context-menu";
var contextMenuItemClassName = "context-menu__item";
var contextMenuLinkClassName = "context-menu__link";
var contextMenuActive = "context-menu--active";

var videoItemClassName = "video";
var videoItemInContext;

var videoItems = document.querySelectorAll(".video"); // video
var menu = document.querySelector("#context-menu");

var clickCoords;
var clickCoordsX;
var clickCoordsY;

var menu = document.querySelector("#context-menu");
var menuItems = menu.querySelector(".context-menu__item");
var menuState = 0;
var menuWidth;
var menuHeight;
var menuPosition;
var menuPositionX;
var menuPositionY;

var windowWidth;
var windowHeight;
// ========================================================================



// Helper functions =======================================================
function clickInsideElement(e, className) {
    var el = e.srcElement || e.target;

    if (el.classList.contains(className)) {
        return el;
    } else {
        while (el = el.parentNode) {
            if (el.classList && el.classList.contains(className)) {
                return el;
            }
        }
    }
    return false;
}
// ========================================================================



function init() {
    contextListener();
    clickListener();
    keyupListener();
    resizeListener();
}
function contextListener() {
    document.addEventListener("contextmenu", e => {
        videoItemInContext = clickInsideElement(e, videoItemClassName);

        if (videoItemInContext) {
            e.preventDefault();
            toggleMenuOn();
            positionMenu(e);
        }
        else {
            videoItemInContext = null;
            toggleMenuOff();
        }
    });
}
function clickListener() {
    document.addEventListener( "click", function(e) {
        var clickeElIsLink = clickInsideElement( e, contextMenuLinkClassName );

        if ( clickeElIsLink ) {
            e.preventDefault();
            menuItemListener( clickeElIsLink );
        } else {
            var button = e.which || e.button;
            if ( button === 1 ) {
                toggleMenuOff();
            }
        }
    });
}
function keyupListener() {
    window.onkeyup = function(e) {
        if ( e.keyCode === 27 ) {
            toggleMenuOff();
        }
    }
}
function toggleMenuOn() {
    if (menuState !== 1) {
        menuState = 1;
        menu.classList.add(contextMenuActive);
    }
}
function toggleMenuOff() {
    if (menuState !== 0) {
        menuState = 0;
        menu.classList.remove(contextMenuActive);
    }
}
function getPosition(e) {
    var posx = 0;
    var posy = 0;

    if (!e) var e = window.event;

    if (e.pageX || e.pageY) {
        posx = e.pageX;
        posy = e.pageY;
    } else if (e.clientX || e.clientY) {
        posx = e.clientX + document.body.scrollLeft +
                           document.documentElement.scrollLeft;
        posy = e.clientY + document.body.scrollTop +
                           document.documentElement.scrollTop;
    }

    return {
        x: posx,
        y: posy
    }
}
function positionMenu(e) {
    clickCoords = getPosition(e);
    clickCoordsX = clickCoords.x;
    clickCoordsY = clickCoords.y;

    menuWidth = menu.offsetWidth + 4;
    menuHeight = menu.offsetHeight + 4;

    windowWidth = window.innerWidth;
    windowHeight = window.innerHeight;

    if ( (windowWidth - clickCoordsX) < menuWidth ) {
        menu.style.left = windowWidth - menuWidth + "px";
    } else {
        menu.style.left = clickCoordsX + "px";
    }

    if ( (windowHeight - clickCoordsY) < menuHeight ) {
        menu.style.top = clickCoordsY - menuHeight + "px";
    } else {
        menu.style.top = clickCoordsY + "px";
    }
}
function resizeListener() {
    window.onresize = function(e) {
        toggleMenuOff();
    };
}
function menuItemListener( link ) {
    //console.log( "Task ID - " +
    //            videoItemInContext.getAttribute("data-id") +
    //            ", Task action - " + link.getAttribute("data-action"));
    toggleMenuOff();
}
init();
