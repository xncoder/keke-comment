function reg(){
    obj = document.getElementById("frm_reg");
    url = obj.action;
    i = checkForm(obj, false);
    if (i) {
        document.getElementById("reg_msg").style.color = "red";
        ajaxpost('frm_reg', 'reg_msg', 'ajaxwaitid', '', 'sbt_reg');
    }
    return false;
}

function redirect_login(){
   var furl = window.location.href;
   var tourl = "index.php?do=user_login";
   var pos = furl.search(/index.php\?do=user_login/);
   if(pos != -1){
   	   setcookie('kekerefer',furl,20);
   }
   window.location.href = tourl;
}

function ajax_login(){

    var i = checkForm(document.getElementById("frm_login"), false);
    if (i) {
        document.getElementById("login_msg").className = "";
        document.getElementById("login_msg").style.color = "red";
        ajaxpost('frm_login', 'login_msg', 'ajaxwaitid', '', 'login_submit');
    }
    return false;
}
