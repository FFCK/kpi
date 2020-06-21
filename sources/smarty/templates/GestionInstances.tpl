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
                <b>{#RC#}</b> : 
                    <span class='{$directInput} arbitre' data-type="text" data-target="Responsable_insc" data-id="{$arrayJournee.Id}" data-value="{$arrayJournee.Responsable_insc}">{$arrayJournee.Responsable_insc}</span>
                    <br>
                    {section name=i loop=$arrayRC}
                        <a class="rcpick badge" title="{$arrayRC[i].Prenom|upper} {$arrayRC[i].Nom|upper} ({$arrayRC[i].Matric})">{$arrayRC[i].Ordre}</a>&nbsp;
                    {/section}

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
                        <th>{#Rep_athletes#}</th>
                        <td>
                            <br />
                            <span class='{$directInput} arbitre' data-type="text" data-target="Rep_athletes" data-id="{$arrayJournee.Id}" data-value="{$arrayJournee.Rep_athletes}">{$arrayJournee.Rep_athletes}</span>
                            <br />
                        </td>
                    </tr>
                </table>
			</div>
			<div class='blocBottom centre'>
                <a href="FeuilleInstances.php?idJournee={$arrayJournee.Id}" title="pdf" target="_blank"><img src="../img/pdf.gif" alt="pdf"></a>
            </div>
            <br>
            <br>
            <table class="tableau tableau60">
                <thead>
                    <tr>
                        <th colspan="2">{#Arb_nj#}</th>
                    </tr>
                </thead>
                <tr>
                    <th>{#Arb_nj#}</th>
                    <td>
                        <br />
                        <span class='{$directInput} arbitre' data-type="text" data-target="Arb_nj1" data-id="{$arrayJournee.Id}" data-value="{$arrayJournee.Arb_nj1}">{$arrayJournee.Arb_nj1}</span>
                        <br />
                    </td>
                </tr>
                <tr>
                    <th>{#Arb_nj#}</th>
                    <td>
                        <br />
                        <span class='{$directInput} arbitre' data-type="text" data-target="Arb_nj2" data-id="{$arrayJournee.Id}" data-value="{$arrayJournee.Arb_nj2}">{$arrayJournee.Arb_nj2}</span>
                        <br />
                    </td>
                </tr>
                <tr>
                    <th>{#Arb_nj#}</th>
                    <td>
                        <br />
                        <span class='{$directInput} arbitre' data-type="text" data-target="Arb_nj3" data-id="{$arrayJournee.Id}" data-value="{$arrayJournee.Arb_nj3}">{$arrayJournee.Arb_nj3}</span>
                        <br />
                    </td>
                </tr>
                <tr>
                    <th>{#Arb_nj#}</th>
                    <td>
                        <br />
                        <span class='{$directInput} arbitre' data-type="text" data-target="Arb_nj4" data-id="{$arrayJournee.Id}" data-value="{$arrayJournee.Arb_nj4}">{$arrayJournee.Arb_nj4}</span>
                        <br />
                    </td>
                </tr>
                <tr>
                    <th>{#Arb_nj#}</th>
                    <td>
                        <br />
                        <span class='{$directInput} arbitre' data-type="text" data-target="Arb_nj5" data-id="{$arrayJournee.Id}" data-value="{$arrayJournee.Arb_nj5}">{$arrayJournee.Arb_nj5}</span>
                        <br />
                    </td>
                </tr>
            </table>
		</form>			
				
	</div>	  	   
