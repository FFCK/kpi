	<div class="main">
		<form method="POST" action="FeuilleInstances.php" name="formInstances" id="formInstances" enctype="multipart/form-data">
			<input type='hidden' name='Cmd' Value=''/>
			<input type='hidden' name='ParamCmd' Value=''/>
            <input type='hidden' name='AjaxTableName' id='AjaxTableName' Value='gickp_Journees'/>
            <input type='hidden' name='AjaxWhere' id='AjaxWhere' Value='Where Id = '/>
            <input type='hidden' name='AjaxUser' id='AjaxUser' Value='{$user}'/>

			<div class='titrePage'>{#Instances_de_la_journee#}</div>
			<div class='blocTop centre'>
				<table width="100%">
                    <tr>
                        <td>
                            <h3 align="center">{$arrayJournee.Nom}<h3>
                            <input type="hidden" name="NomJournee" value="{$arrayJournee.Nom}" >           
                            <h4 align="center">{$arrayJournee.Lieu} ({$arrayJournee.Departement})
                                <input type="hidden" name="Lieu" value="{$arrayJournee.Lieu}" >
                                <input type="hidden" name="Departement" value="{$arrayJournee.Departement}" >
                                <br />{$arrayJournee.Date_debut|date_format:"%d/%m/%Y"} - {$arrayJournee.Date_fin|date_format:"%d/%m/%Y"}
                            </h4>
                            <input type="hidden" name="Date_debut" value="{$arrayJournee.Date_debut|date_format:"%d/%m/%Y"}" >
                            <input type="hidden" name="Date_fin" value="{$arrayJournee.Date_fin|date_format:"%d/%m/%Y"}" >
                        </td>
					</tr>
				</table>
			</div>
            {if $profile <= 3 && $AuthModif == 'O' && $bAutorisation}
                {assign var="directInput" value="directInput"}
            {/if}

			<div class='blocMiddle centre'>
                {#RC#} : 
                    <span class='{$directInput} arbitre' data-type="text" data-target="Responsable_insc" data-id="{$arrayJournee.Id}" data-value="{$arrayJournee.Responsable_insc}">{$arrayJournee.Responsable_insc}</span>
                <br />
                <br />
                <table class="tableau tableau60">
                    <thead>
                        <tr>
                            <th colspan="2">{#Comite_de_competition#}</th>
                        </tr>
                    </thead>
                    <tr>
                        <th>{#R1#}</th>
                        <td>
                            <br />
                            <span class='{$directInput} arbitre' data-type="text" data-target="Responsable_R1" data-id="{$arrayJournee.Id}" data-value="{$arrayJournee.Responsable_R1}">{$arrayJournee.Responsable_R1}</span>
                            <br />
                        </td>
                    </tr>
                    <tr>
                        <th>{#Delegue_federal#}</th>
                        <td>
                            <br />
                            <span class='{$directInput} arbitre' data-type="text" data-target="Delegue" data-id="{$arrayJournee.Id}" data-value="{$arrayJournee.Delegue}">{$arrayJournee.Delegue}</span>
                            <br />
                        </td>
                    </tr>
                    <tr>
                        <th>{#Chef_arbitres#}</th>
                        <td>
                            <br />
                            <span class='{$directInput} arbitre' data-type="text" data-target="ChefArbitre" data-id="{$arrayJournee.Id}" data-value="{$arrayJournee.ChefArbitre}">{$arrayJournee.ChefArbitre}</span>
                            <br />
                        </td>
                    </tr>
                    <thead>
                    <tr>
                        <th colspan="2">{#Jury_appel#}</th>
                    </tr>
                    </thead>
                    <tr>
                        <th>{#Delegue_federal#} ({#President#})</th>
                        <td>
                            <br />
                            <span id="Delegueb">{$arrayJournee.Delegue}</span>
                            <br />
                        </td>
                    </tr>
                    <tr>
                        <th>{#R1#}</th>
                        <td>
                            <br />
                            <span id="Responsable_R1b">{$arrayJournee.Responsable_R1}</span>
                            <br />
                        </td>
                    </tr>
                    <tr>
                        <th>{#Representant_des_competiteurs#}</th>
                        <td>
                            <br />
                            <input type="text" name="Representant" id="Representant" size="60" placeholder="{#Nom#} {#Prenom#} {#Licence#}" class="ac_input" />
                            <br />
                        </td>
                    </tr>
                </table>
			</div>
			<div class='blocBottom centre'>
                <a href="FeuilleInstances.php?idJournee={$arrayJournee.Id}" title="pdf" target="_blank"><img src="../img/pdf.gif" alt="pdf"></a>
            </div>
		</form>			
				
	</div>	  	   
