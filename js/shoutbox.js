var t;
function startcountdown(time) {
    parent.document.getElementById('countdown').innerHTML=time;
    time=time-1;
    t=setTimeout("startcountdown("+time+")",1000);
}

function countdown(time) {
    if (time <= 0){
	parent.document.getElementById("hbtext").disabled=false;
	parent.document.getElementById("hbsubmit").disabled=false;
	parent.document.getElementById("hbsubmit").value=parent.document.getElementById("sbword").innerHTML;
    }
    else {
	parent.document.getElementById("hbsubmit").value=time;
	time=time-1;
	setTimeout("countdown("+time+")", 1000); 
    }
}

function hbquota() {
    parent.document.getElementById("hbtext").disabled=true;
    parent.document.getElementById("hbsubmit").disabled=true;
    var time=10;
    countdown(time);
}