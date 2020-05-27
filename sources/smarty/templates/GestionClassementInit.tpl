		<div class="main">
			<form method="POST" action="GestionClassementInit.php" name="formClassementInit" id="formClassementInit" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' id='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd'  id='ParamCmd' Value=''/>
				<input type='hidden' name='AjaxTableName' id='AjaxTableName' Value='gickp_Competitions_Equipes_Init'/>
				<input type='hidden' name='AjaxWhere' id='AjaxWhere' Value='Where Id = '/>
				<input type='hidden' name='AjaxUser' id='AjaxUser' Value='{$user}'/>

				<div class='blocLeft'>
					<div class='titrePage'>{#Classement_initial#} {$codeCompet}</div>
					<button id='actuButton' type="button" ><img src="../img/actualiser.gif">{#Recharger#}</button>
					<input type='button' id='raz' value='{#Remise_a_zero#}'>
					<div class='blocTable'>
						<table id='tableauJQ' class='tableau'>
							<thead>
								<tr class='header'>
									<th>{#Clt#}</th>
									<th>{#Equipe#}</th>
									<th>{#Pts#}</th>
									<th>{#J#}</th>
									<th>{#G#}</th>
									<th>{#N#}</th>
									<th>{#P#}</th>
									<th>{#F#}</th>
									<th>+</th>
									<th>-</th>
									<th>{#Diff#}</th>
								</tr>
							</thead>
							<tbody>
								{section name=i loop=$arrayEquipe}
									{if $profile <= 4}
										<tr height="17" class='{cycle values="impair,pair"}'>
											<td width="30"><a href="#" Id="Clt-{$arrayEquipe[i].Id}" tabindex="1{$smarty.section.i.iteration}0">{$arrayEquipe[i].Clt}</a></td>
											<td width="200">{$arrayEquipe[i].Libelle}</td>
											<td width="40"><a href="#" Id="Pts-{$arrayEquipe[i].Id}" tabindex="1{$smarty.section.i.iteration}1">{$arrayEquipe[i].Pts}</a></td>
											<td width="30"><a href="#" Id="J-{$arrayEquipe[i].Id}" tabindex="1{$smarty.section.i.iteration}2">{$arrayEquipe[i].J}</a></td>
											<td width="30"><a href="#" Id="G-{$arrayEquipe[i].Id}" tabindex="1{$smarty.section.i.iteration}3">{$arrayEquipe[i].G}</a></td>
											<td width="30"><a href="#" Id="N-{$arrayEquipe[i].Id}" tabindex="1{$smarty.section.i.iteration}4">{$arrayEquipe[i].N}</a></td>
											<td width="30"><a href="#" Id="P-{$arrayEquipe[i].Id}" tabindex="1{$smarty.section.i.iteration}5">{$arrayEquipe[i].P}</a></td>
											<td width="30"><a href="#" Id="F-{$arrayEquipe[i].Id}" tabindex="1{$smarty.section.i.iteration}6">{$arrayEquipe[i].F}</a></td>
											<td width="40"><a href="#" Id="Plus-{$arrayEquipe[i].Id}" tabindex="1{$smarty.section.i.iteration}7">{$arrayEquipe[i].Plus}</a></td>
											<td width="40"><a href="#" Id="Moins-{$arrayEquipe[i].Id}" tabindex="1{$smarty.section.i.iteration}8">{$arrayEquipe[i].Moins}</a></td>
											<td width="40"><a href="#" Id="Diff-{$arrayEquipe[i].Id}" tabindex="1{$smarty.section.i.iteration}9">{$arrayEquipe[i].Diff}</a></td>
										</tr>
									{else}
										<tr height="17" class='{cycle values="impair,pair"}'>
											<td width="30">{$arrayEquipe[i].Clt}</td>
											<td width="200">{$arrayEquipe[i].Libelle}</td>
											<td width="40">{$arrayEquipe[i].Pts}</td>
											<td width="30">{$arrayEquipe[i].J}</td>
											<td width="30">{$arrayEquipe[i].G}</td>
											<td width="30">{$arrayEquipe[i].N}</td>
											<td width="30">{$arrayEquipe[i].P}</td>
											<td width="30">{$arrayEquipe[i].F}</td>
											<td width="40">{$arrayEquipe[i].Plus}</td>
											<td width="40">{$arrayEquipe[i].Moins}</td>
											<td width="40">{$arrayEquipe[i].Diff}</td>
										</tr>
									{/if}
								{/section}
							</tbody>
						</table>
					</div>

				</div>
					
			</form>			
					
		</div>	  	   

