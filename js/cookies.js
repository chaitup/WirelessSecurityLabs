function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}
function deleteCookie () {
    document.cookie="name= ";
    document.cookie="email= ";
    document.cookie="user_id= ";
    document.cookie="isadmin= ";
    checkCookie();
}
function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
    }
    return "";
}
function checkCookie() {
    if (!getCookie('name')) window.location = "login.html?ckattempt=1";
}
function goAdmin () {
    window.location = "admin-manageusers.html?page=1";
}
function goUser () {
    window.location = "user-managematerials.html?page=1";
}
function goFaculty () {
    window.location = "faculty-managematerials.html?page=1";
}
