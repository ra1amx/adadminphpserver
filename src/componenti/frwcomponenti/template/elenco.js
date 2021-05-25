	function confermaDelete(id) {
		if (confirm("Confermi l'eliminazione definitiva di questo componente?\n\nIl componente verrà rimosso insieme alle\nsue funzionalità, che non saranno più\nutilizzabili da alcun utente.\n"))
			document.location.href = "index.php?op=eliminac&id="+id
	}

	function abilitaComponente(id) {
		if (confirm("Quest'operazione (ri)assegna le funzionalità di questo componente\nagli utenti del sistema che corrispondono ai profili specificati\nnella configurazione del componente.\n\nQuesta operazione può determinare cambiamenti negli utenti attualmente\nconnessi e può richiedere l'esecuzione di molte query se si distribuisce una nuova\nfunzionalità a centinaia di utenti."))
			document.location.href = "index.php?op=profila&id="+id
	}

