		function sessionSaison(codeSaison)
		{
		    document.forms['formSaison'].elements['Cmd'].value = 'Session';
			document.forms['formSaison'].elements['ParamCmd'].value = codeSaison;
			document.forms['formSaison'].submit();
		}
		
