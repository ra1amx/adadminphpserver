HTMLSelectElement.prototype.getComboValore = function () {
	return this.options[ this.options.selectedIndex ].value ;
}
HTMLSelectElement.prototype.getComboStringa = function () {
	return this.options[ this.options.selectedIndex ].innerHTML ;
}
function saveAndLoad() {
	if ($('op').value=='modificaStep2') $('op').value = "modificaStep2reload";
	if ($('op').value=='aggiungiStep2') $('op').value = "aggiungiStep2reload";
	checkForm();
}
function checkConStato() {
	if ($('op').value=='modificaStep2reload') $('op').value = "modificaStep2";
	if ($('op').value=='aggiungiStep2reload') $('op').value = "aggiungiStep2";
	checkForm();
}
function confermaDeleteComponente(idc,idm) {
	if (gconfirm("Confermi l'eliminazione definitiva di questo record?","document.location.href = 'indexcomponenti.php?op=elimina&id="+idc+"&cd_item="+idm+"'")) {}
}

function elimina(s,divid) {
	if (confirm('sicuro di eliminare l\'immagine adesso?'))
	{
		//chiamata ajax per cancellazione file.
		new Ajax.Request('deleteimg.php?f='+escape(s), {
		method:'get',
		onSuccess: function(transport){
			var response = transport.responseText;
			if (response.indexOf("ok")==0) {
				//$(divid).innerHTML = "";
				Effect.Puff(divid);
			} else {
				alert('Non sono riuscito a cancellare.'+ response);
			}
		},
		onFailure: function(){ alert('Errore Http, file non cancellato.'); }
		});
	}
}
