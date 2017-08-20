		<span class='repere'>&nbsp;(<a href="#">{#Retour#}</a>)
		<br></span>
		<div class="main">
			<form method="POST" action="Competitions.php" name="formCompetitions" id="formCompetitions" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' Value=''/>
				
				<div class='titrePage'>Compétition : {$the_title}</div>
				<div class='blocMiddle soustitrePage'>
					{$the_content}
				</div>
			</form>
		</div>
		
		