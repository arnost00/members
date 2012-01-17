<!--
/*
Image w/ description tooltip- By Dynamic Web Coding (www.dyn-web.com)
Copyright 2002 by Sharon Paine
Visit http://www.dynamicdrive.com for this script
*/
/* IMPORTANT: Put script after tooltip div or 
put tooltip div just before </BODY>. */
var dom = (document.getElementById) ? true : false;
var ns5 = ((navigator.userAgent.indexOf("Gecko")>-1) && dom) ? true: false;
var ie5 = ((navigator.userAgent.indexOf("MSIE")>-1) && dom) ? true : false;
var ns4 = (document.layers && !dom) ? true : false;
var ie4 = (document.all && !dom) ? true : false;
var nodyn = (!ns5 && !ns4 && !ie4 && !ie5) ? true : false;
// resize fix for ns4
var origWidth, origHeight;
if (ns4) {
origWidth = window.innerWidth; origHeight = window.innerHeight;
window.onresize = function() { if (window.innerWidth != origWidth || window.innerHeight != origHeight) history.go(0); }
}
// avoid error of passing event object in older browsers
if (nodyn) { event = "nope" }
/////////////////////// CUSTOMIZE HERE ////////////////////
// settings for tooltip 
// Do you want tip to move when mouse moves over link?
var tipFollowMouse= true; 
// Be sure to set tipWidth wide enough for widest image
var tipWidth= 200;
var offX= 10; // how far from mouse to show tip
var offY= 5; 
var tipFontFamily= "sans-serif";
var tipFontSize= "10pt";
// set default text color and background color for tooltip here
// individual tooltips can have their own (set in messages arrays)
// but don't have to
var tipFontColor= "#000000";
var tipBgColor= "#CCCCCC"; 
var tipBorderColor= "#FF9900";
var tipBorderWidth= 2;
var tipBorderStyle= "solid";
var tipPadding= 4;
var messages = new Array();
//////////////////// END OF CUSTOMIZATION AREA ///////////////////
////////////////////////////////////////////////////////////
// initTip - initialization for tooltip.
// Global variables for tooltip. 
// Set styles for all but ns4. 
// Set up mousemove capture if tipFollowMouse set true.
////////////////////////////////////////////////////////////
var tooltip, tipcss;
function initTip() {
if (nodyn) return;
tooltip = (ns4)? document.tipDiv.document: (ie4)? document.all['tipDiv']: (ie5||ns5)? document.getElementById('tipDiv'): null;
tipcss = (ns4)? document.tipDiv: tooltip.style;
if (ie4||ie5||ns5) { // ns4 would lose all this on rewrites
tipcss.width = tipWidth+"px";
tipcss.fontFamily = tipFontFamily;
tipcss.fontSize = tipFontSize;
tipcss.color = tipFontColor;
tipcss.backgroundColor = tipBgColor;
tipcss.borderColor = tipBorderColor;
tipcss.borderWidth = tipBorderWidth+"px";
tipcss.padding = tipPadding+"px";
tipcss.borderStyle = tipBorderStyle;
}
if (tooltip&&tipFollowMouse) {
if (ns4) document.captureEvents(Event.MOUSEMOVE);
document.onmousemove = trackMouse;
}
}
window.onload = initTip;
/////////////////////////////////////////////////
// doTooltip function
// Assembles content for tooltip and writes 
// it to tipDiv
/////////////////////////////////////////////////
var t1,t2; // for setTimeouts
var tipOn = false; // check if over tooltip link
function doTooltip(evt,num) {
if (!tooltip) return;
if (t1) clearTimeout(t1); if (t2) clearTimeout(t2);
tipOn = true;
// set colors if included in messages array
if (messages[num][1]) var curBgColor = messages[num][1];
else curBgColor = tipBgColor;
if (messages[num][2]) var curFontColor = messages[num][2];
else curFontColor = tipFontColor;
if (ns4) {
var tip = '<table bgcolor="' + tipBorderColor + '" width="' + tipWidth + '" cellspacing="0" cellpadding="' + tipBorderWidth + '" border="0"><tr><td><table bgcolor="' + curBgColor + '" width="100%" cellspacing="0" cellpadding="' + tipPadding + '" border="0"><tr><td><span style="font-family:' + tipFontFamily + '; font-size:' + tipFontSize + '; color:' + curFontColor + ';">' + messages[num][0] + '</span></td></tr></table></td></tr></table>';
tooltip.write(tip);
tooltip.close();
} else if (ie4||ie5||ns5) {
var tip = '<span style="font-family:' + tipFontFamily + '; font-size:' + tipFontSize + '; color:' + curFontColor + ';">' + messages[num][0] + '</span>';
tipcss.backgroundColor = curBgColor;
tooltip.innerHTML = tip;
}
if (!tipFollowMouse) positionTip(evt);
else t1=setTimeout("tipcss.visibility='visible'",100);
}
var mouseX, mouseY;
function trackMouse(evt) {
mouseX = (ns4||ns5)? evt.pageX: window.event.clientX + document.body.scrollLeft;
mouseY = (ns4||ns5)? evt.pageY: window.event.clientY + document.body.scrollTop;
if (tipOn) positionTip(evt);
}
/////////////////////////////////////////////////////////////
// positionTip function
// If tipFollowMouse set false, so trackMouse function
// not being used, get position of mouseover event.
// Calculations use mouseover event position, 
// offset amounts and tooltip width to position
// tooltip within window.
/////////////////////////////////////////////////////////////
function positionTip(evt) {
if (!tipFollowMouse) {
mouseX = (ns4||ns5)? evt.pageX: window.event.clientX + document.body.scrollLeft;
mouseY = (ns4||ns5)? evt.pageY: window.event.clientY + document.body.scrollTop;
}
// tooltip width and height
var tpWd = (ns4)? tooltip.width: (ie4||ie5)? tooltip.clientWidth: tooltip.offsetWidth;
var tpHt = (ns4)? tooltip.height: (ie4||ie5)? tooltip.clientHeight: tooltip.offsetHeight;
// document area in view (subtract scrollbar width for ns)
var winWd = (ns4||ns5)? window.innerWidth-20+window.pageXOffset: document.body.clientWidth+document.body.scrollLeft;
var winHt = (ns4||ns5)? window.innerHeight-20+window.pageYOffset: document.body.clientHeight+document.body.scrollTop;
// check mouse position against tip and window dimensions
// and position the tooltip 
if ((mouseX+offX+tpWd)>winWd) 
tipcss.left = (ns4)? mouseX-(tpWd+offX): mouseX-(tpWd+offX)+"px";
else tipcss.left = (ns4)? mouseX+offX: mouseX+offX+"px";
if ((mouseY+offY+tpHt)>winHt) 
tipcss.top = (ns4)? winHt-(tpHt+offY): winHt-(tpHt+offY)+"px";
else tipcss.top = (ns4)? mouseY+offY: mouseY+offY+"px";
if (!tipFollowMouse) t1=setTimeout("tipcss.visibility='visible'",100);
}
function hideTip() {
if (!tooltip) return;
t2=setTimeout("tipcss.visibility='hidden'",100);
tipOn = false;
}
//-->
