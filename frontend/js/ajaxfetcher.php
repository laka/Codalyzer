<script type="text/javascript">
document.getElementById('weapon').onchange = function (){
	var xmlhttp=false;
	/*@cc_on @*/
	/*@if (@_jscript_version >= 5)
	// JScript gives us Conditional compilation, we can cope with old IE versions.
	// and security blocked creation of the objects.
	 try {
	  xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	 } catch (e) {
	  try {
	   xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	  } catch (E) {
	   xmlhttp = false;
	  }
	 }
	@end @*/
	if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
		try {
			xmlhttp = new XMLHttpRequest();
		} catch (e) {
			xmlhttp=false;
		}
	}
	if (!xmlhttp && window.createRequest) {
		try {
			xmlhttp = window.createRequest();
		} catch (e) {
			xmlhttp=false;
		}
	}
	
	var wp_select = document.weaponselector.weapon;
	user_input = wp_select.options[wp_select.selectedIndex].value

	xmlhttp.open("GET", "<?php echo FRONTEND_URL; ?>/inc/hitfetcher.php?h=<?php echo $uid; ?>&w=" + user_input, true);

	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4) {
			eval(xmlhttp.responseText)
			if(hits.length > 0){
				document.getElementById('hitdiagram').src = '<?php echo FRONTEND_URL; ?>/graphs/hitgraph.php?h=<?php echo $uid; ?>&t=' + hits;
				var hittable = document.getElementById("hittable")
				var hitarray = hits.split(",");
				var antallrader = hittable.rows.length;

				for(var i = antallrader-1; i>0; i--){
					hittable.deleteRow(i);
				}			
				for(var i=0; i<((hitarray.length-1)/2); i++){
					if(i==10){
						break;
					}
					percentage = Math.round(hitarray[i*2+1]*10000/(total+1),2)/100;
					newrow = hittable.insertRow(-1)
					newcell = newrow.insertCell(0)
					newcell.innerHTML=percentage + "%";
					newcell = newrow.insertCell(0)
					newcell.innerHTML=hitarray[i*2+1]
					newcell = newrow.insertCell(0)
					newcell.innerHTML=hitarray[i*2]
				}
			}
		}
	}
	xmlhttp.send(null);	
}
</script>