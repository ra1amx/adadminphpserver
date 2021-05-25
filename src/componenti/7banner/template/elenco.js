/* AUTO UPDATE NUMBERS */
ricor = function(td){
	
	n0 = parseInt( jQuery(td).html() ); 
	n1 = parseInt( jQuery(td).attr("data-rel") );
	delta = n1 - n0;
	if (delta != 0) {
		var step = delta > 0 ? 1 : -1;
		var timer = Math.floor( 20000 / Math.abs(delta) );
		if(timer < 50) {
			if(delta>0) step++; else step--;     // accelero un po'
			timer = 50;
		}
		n0 = n0 + step;
		if(n0 > 0) {
			jQuery(td).html( n0 );
			setTimeout(function(){
				ricor(td);
			},timer);
		}
	}
}

function ln () {
	console.log("Fetch new numbers.");
	jQuery('#tempdati').load(document.location + ' #tab_7banner',function(){
		jQuery('#tempdati td').each(function(){
			var h = jQuery(this).html();
			var c = jQuery(this).attr("id");
			var reg = new RegExp('^([0-9]*)$');
			if(typeof(c)!="undefined" && reg.test(h) && jQuery(this).hasClass("numero")){
				console.log("id = " + c + "  ==> " + h);
				//console.log("si");
				jQuery('.corpo #'+c).attr("data-rel",h);
				//$('#'+c).css("background-color","red");
			} else {
				if(typeof(c)!="undefined") {
				//console.log("id = " + c + "  ==> " + h);
				//console.log("no");
					jQuery('.corpo #'+c).html(h);
					//$('#'+c).css("background-color","yellow");
				}
			}
		});
		jQuery('#tempdati').html("");



		jQuery('.corpo td.numero').each(function(){
			var h = jQuery(this).html();
			var reg = new RegExp('^([0-9]*)$');
			
			if(reg.test(h)){
				// Math.random() * (200 - 100) + 100);
				n0 = parseInt(h); 
				n1 = parseInt( jQuery(this).attr("data-rel") );
				//console.log("n0=" + n0);
				//console.log("n1=" + n1);
				if(jQuery(this).html() != n1 ) {

					ricor(this);

				}
			}
		});

	
	
	});
}

jQuery(document).ready(function($) {
	var m = setInterval(function(){ 
		ln(); 
	},20000);
	sel = "#tab_7banner th:first";
	jQuery(sel).css("position","relative");
	jQuery(sel).append("<span id='wait' style='position:absolute;top:0;left:0;text-align:left;color:#aaa;font-weight:normal'></span>");
	setTimeout(function(){ln();clearInterval(timerWait); jQuery("#wait").html( "realtime data" );},10000);
	var timerWait = setInterval(function() {
		s = jQuery("#wait").html();
		if(s=="") s="analyzing data stream"; else s="";
		jQuery("#wait").html( s );
	},500
	);
} );