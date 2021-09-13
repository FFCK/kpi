		<div class="main">
		  <form method="POST" action="GestionEquipe.php" name="formEquipe" id="formEquipe" enctype="multipart/form-data">
		    <input type='hidden' name='Cmd' id='Cmd' Value='' />
		    <input type='hidden' name='ParamCmd' id='ParamCmd' Value='' />
		    <input type='hidden' name='AjaxTableName' id='AjaxTableName' Value='kp_competition_equipe' />
		    <input type='hidden' name='AjaxWhere' id='AjaxWhere' Value='Where Id = ' />
		    <input type='hidden' name='AjaxUser' id='AjaxUser' Value='{$user}' />
		    <input type='hidden' name='Saison' id='Saison' Value='{$codeSaison}' />
		    <input type='hidden' name='Compet' id='Compet' value='{$codeCompet}' />

		    <div class='blocLeft Left2'>
		      <div class='titrePage'>{#Equipes_engagees#}</div>
		      <label for="competition">{#Competition#} :</label>
		      <select name='competition' id='competition' onChange="changeCompetition();">
		        {section name=i loop=$arrayCompetition}
  		        {assign var='options' value=$arrayCompetition[i].options}
  		        {assign var='label' value=$arrayCompetition[i].label}
  		        <optgroup label="{$smarty.config.$label|default:$label}">
  		          {section name=j loop=$options}
    		          {assign var='optionLabel' value=$options[j].Code}
    		          <Option Value="{$options[j].Code}" {$options[j].selected}>{$options[j].Code} -
    		            {$smarty.config.$optionLabel|default:$options[j].Libelle}</Option>
  		          {/section}
  		        </optgroup>
		        {/section}
		        <optgroup label="{#Arbitres#}">
		          <Option Value="POOL" {if $codeCompet == 'POOL'}selected{/if}>{#Pool_arbitres#}</Option>
		        </optgroup>
		      </select>

		      <div class='liens'>
		        <table>
		          <tr>
		            <td width=200>
		              <fieldset>
		                <a href="#" title="{#Tous#}"
		                  onclick="setCheckboxes('formEquipe', 'checkEquipe', true);return false;"><img height="22"
		                    src="../img/glyphicons-155-more-checked.png" /></a>
		                <a href="#" title="{#Aucun#}"
		                  onclick="setCheckboxes('formEquipe', 'checkEquipe', false);return false;"><img height="22"
		                    src="../img/glyphicons-155-more-windows.png" /></a>
		                {if $profile <=6 && $AuthModif == 'O' && $bProd}
  		                <a href="#" title="{#Supprimer#}" onclick="RemoveCheckboxes('formEquipe', 'checkEquipe')"><img
  		                    height="25" src="../img/glyphicons-17-bin.png" /></a>
		                {/if}
		                {*										<a href="#" onclick=""><img src="../img/map.gif" height="25" alt="Cartographier la s&eacute;lection (en construction)" title="Cartographier la s&eacute;lection (en construction)" /></a>*}
		                &nbsp;&nbsp;&nbsp;
		              </fieldset>
		            </td>
		            <td>
		              <a href="FeuilleGroups.php" target="_blank" title="{#Poules#}"><img height="25"
		                  src="../img/pdf.png" /></a>
		              <a href="FeuillePresence.php" target="_blank" title="{#Feuilles_de_presence#} (FR)"><img height="25"
		                  src="../img/pdf2.png" /></a>
		              <a href="FeuillePresenceEN.php" target="_blank" title="{#Feuilles_de_presence#} (EN)"><img height="25"
		                  src="../img/pdfEN.png" /></a>
		              <a href="FeuillePresenceCat.php" target="_blank" title="{#Feuilles_de_presence_par_categorie#}"><img
		                  height="25" src="../img/pdf2.png" />Cat</a>
		              <img class="cliquable" id="actuButton" title="{#Recharger#}" height="25"
		                src="../img/glyphicons-82-refresh.png">
		              {if $profile <= 4 && $Statut == 'ON'}
  		              <img class="cliquable" data-verrou="{$Verrou}" height="25" src="../img/verrou2{$Verrou}.gif"
  		                id="verrouCompet" title='{#Verrouiller#}'>
  		              &nbsp;
  		              <img class="cliquable" height="25" src="../img/b_update.png" id="InitTitulaireCompet"
  		                title="{#InitTitulaireCompet#}">
		              {/if}
		            </td>
		          </tr>
		        </table>
		      </div>
		      <div class='blocTable'>
		        <table class='tableau' id='tableEquipes'>
		          <thead>
		            <tr>
		              <th>&nbsp;</th>
		              <th>{#Poule#}</th>
		              <th># {#Tirage#}</th>
		              <th>&nbsp;</th>
		              <th>{#Equipe#}</th>
		              <th>{#Presents#}</th>
		              <th>Club</th>
		              <th>{#Nb_matchs#}</th>
		              <th>&nbsp;</th>
		            </tr>
		          </thead>
		          <tbody>
		            {section name=i loop=$arrayEquipe}
  		            {if $PouleX != $arrayEquipe[i].Poule && $arrayEquipe[i].Poule != ''}
    		            <tr class='colorO'>
    		              <th colspan=9><b>{#Poule#} {$arrayEquipe[i].Poule}</b></th>
    		            </tr>
  		            {/if}
  		            <tr class='{cycle values="impair,pair"}'>
  		              {assign var='PouleX' value=$arrayEquipe[i].Poule}
  		              <td><input type="checkbox" name="checkEquipe" value="{$arrayEquipe[i].Id}"
  		                  id="checkDelete{$smarty.section.i.iteration}" /></td>
  		              <td><span {if $profile <=6 && $AuthModif == 'O'}class='directInput textPoule'
  		                  {/if}tabindex='1{$smarty.section.i.iteration|string_format:"%02d"}0'
  		                  Id="Poule-{$arrayEquipe[i].Id}-text">{$arrayEquipe[i].Poule}</span></td>
  		              <td><span {if $profile <=6 && $AuthModif == 'O'}class='directInput textTirage'
  		                  {/if}tabindex='1{$smarty.section.i.iteration|string_format:"%02d"}1'
  		                  Id="Tirage-{$arrayEquipe[i].Id}-text">{if $arrayEquipe[i].Tirage == '0'}{else}{$arrayEquipe[i].Tirage}{/if}</span>
  		              </td>
  		              <td>
  		                {if $arrayEquipe[i].logo}<img src="/img/{$arrayEquipe[i].logo}" width="20" />{/if}
  		              </td>
  		              <td class="cliquableNomEquipe">
  		                <a href="./GestionEquipeJoueur.php?idEquipe={$arrayEquipe[i].Id}"
  		                  title="{#Feuille_de_presence#}">{$arrayEquipe[i].Libelle}</a>
  		              </td>
  		              <td><a href="./GestionEquipeJoueur.php?idEquipe={$arrayEquipe[i].Id}"
  		                  title="{#Feuille_de_presence#}"><img height="25" src="../img/b_sbrowse.png" /></A></td>
  		              <td title="{$arrayEquipe[i].Club}">{$arrayEquipe[i].Code_club}</td>
  		              <td>{$arrayEquipe[i].nbMatchs}</td>
  		              {if $profile <= 3 && $AuthModif == 'O' && $bProd}
    		              <td><a href="#" onclick="RemoveCheckbox('formEquipe', '{$arrayEquipe[i].Id}');return false;"><img
    		                    height="20" src="../img/glyphicons-17-bin.png" title="{#Supprimer#}" /></a></td>
  		              {else}<td>&nbsp;</td>
  		              {/if}
  		            </tr>
		            {/section}
		          </tbody>
		        </table>
		      </div>
		      <b>TOTAL = {$smarty.section.i.iteration-1|replace:-1:0} {#Equipes#}</b>
		    </div>

		    {if $profile <=3 && $AuthModif == 'O' && $bProd}
  		    <div class='blocRight Right2'>
  		      <table width=100%>
  		        <tr>
  		          <th class='titreForm' colspan=2>
  		            <label>{#Ajouter_une_equipe#}</label>
  		          </th>
  		        </tr>
  		        <tr>
  		          <td>
  		            <label><b>{#Chercher#} :</b></label><input type="text" name="choixEquipe" id="choixEquipe"
  		              style="width:60%">
  		            <br>
  		            <div name="ShowCompo" id="ShowCompo">
  		              <input type="hidden" name="EquipeNum" id="EquipeNum">
  		              <input type="hidden" name="EquipeNumero" id="EquipeNumero">
  		              <input type="text" name="EquipeNom" id="EquipeNom" style="width:100%" readonly>
  		              <label title="A - ZZZ">{#Poule#}:</label>
  		              <input type="text" name="plEquipe" title="A - ZZZ" id="plEquipe" style="width:8%" size=2>
  		              <label title="1 - 99">{#Tirage#}:</label>
  		              <input type="text" name="tirEquipe" id="tirEquipe" title="1 - 99" style="width:8%" size=2>
  		              {if $user =='42054'}
    		              &nbsp;
    		              <label title="Classement Championnat" alt="Classement Championnat">Clt.Chpt:</label>
    		              <input type="text" name="cltChEquipe" id="cltChEquipe" style="width:8%" size=2>
    		              <label title="Classement Coupe" alt="Classement Coupe">Clt.CP:</label>
    		              <input type="text" name="cltCpEquipe" id="cltCpEquipe" style="width:8%" size=2>
  		              {/if}
  		              <br><b>{#Reprise_presence_precedentes#} :</b><br />
  		              <span name="GetCompo" id="GetCompo"></span>
  		              <input type="button" onclick="Add2();" name="addEquipe2" id="addEquipe2" value="<< {#Ajouter#}"
  		                style="width:45%">
  		              <input type="button" name="annulEquipe2" id="annulEquipe2" value="{#Annuler#}" style="width:45%">
  		            </div>
  		          </td>
  		        </tr>
  		      </table>
  		      <table width=100%>
  		        <tr>
  		          <th class='titreForm'>
  		            <label>{#Recherche_avancee#}</label>
  		          </th>
  		        </tr>
  		        <tr>
  		          <td>
  		            <label for="comiteReg">{#Comite_regional#} : </label>
  		            <select name="comiteReg" id="comiteReg" onChange="changeComiteReg();">
  		              {section name=i loop=$arrayComiteReg}
    		              <Option Value="{$arrayComiteReg[i].Code}" {$arrayComiteReg[i].Selected}>{$arrayComiteReg[i].Libelle}
    		              </Option>
  		              {/section}
  		            </select>
  		          </td>
  		        </tr>
  		        <tr>
  		          <td>
  		            <label for="comiteDep">{#Comite_departemental#} : </label>
  		            <select name="comiteDep" id="comiteDep" onChange="changeComiteDep();">
  		              {section name=i loop=$arrayComiteDep}
    		              <Option Value="{$arrayComiteDep[i].Code}" {$arrayComiteDep[i].Selected}>{$arrayComiteDep[i].Libelle}
    		              </Option>
  		              {/section}
  		            </select>
  		          </td>
  		        </tr>
  		        <tr>
  		          <td>
  		            <label for="club">Club / Structure : </label>
  		            <select name="club" id="club" onChange="changeClub();">
  		              {section name=i loop=$arrayClub}
    		              <Option Value="{$arrayClub[i].Code}" {$arrayClub[i].Selected}>{$arrayClub[i].Libelle}</Option>
  		              {/section}
  		            </select>
  		          </td>
  		        </tr>
  		        <tr>
  		          <td>
  		            <label for="histoEquipe">{#Equipes#} :</label>
  		            <img title="{#Choix_equipe_title#}" src="../img/b_help.png" onclick="alert('{#Choix_equipe_title#}')" />
  		            <select name="histoEquipe[]" id="histoEquipe" class="histoEquip" onChange="changeHistoEquipe();" size="20"
  		              multiple>
  		              {section name=i loop=$arrayHistoEquipe}
    		              {if $arrayHistoEquipe[i].Numero == '' || $arrayHistoEquipe[i].Numero == 0}
      		              <Option Value="{$arrayHistoEquipe[i].Numero}">{$arrayHistoEquipe[i].Libelle}</Option>
    		              {else}
      		              <Option Value="{$arrayHistoEquipe[i].Numero}">{$arrayHistoEquipe[i].Code_club} -
      		                {$arrayHistoEquipe[i].Libelle}</Option>
    		              {/if}
  		              {/section}
  		            </select>
  		          </td>
  		        </tr>
  		        <tr>
  		          <td>
  		            <label for="libelleEquipe"><b>{#Creation#} :</b></label>
  		            <img title="{#Formalisme_creation_equipe#}" src="../img/b_help.png">
  		            <input type="text" name="libelleEquipe" maxlength=40 id="libelleEquipe" />
  		          </td>
  		        </tr>
  		        <tr>
  		          <td>
  		            <input type="button" onclick="Add();" name="addEquipe" id="addEquipe" value="<< {#Ajouter#}">
  		          </td>
  		        </tr>
  		      </table>
		      {/if}
		      {if $profile <=4 && $AuthModif == 'O'}
  		      <br>
  		      <table width=100%>
  		        <tr>
  		          <th class='titreForm' colspan=3>
  		            <label>{#Poules#} - {#Tirage#}</label>
  		          </th>
  		        </tr>
  		        <tr>
  		          <td>
  		            <label for="equipeTirage">{#Equipe#} :</label>
  		            <select name="equipeTirage" id="equipeTirage">
  		              {section name=i loop=$arrayEquipe}
    		              <Option Value="{$arrayEquipe[i].Id}">{$arrayEquipe[i].Libelle}</Option>
  		              {/section}
  		            </select>
  		          </td>
  		          <td>
  		            <label for="pouleTirage">{#Poule#} :</label>
  		            <select name="pouleTirage" id="pouleTirage">
  		              <Option Value="">nc</Option>
  		              <Option Value="A">A</Option>
  		              <Option Value="B">B</Option>
  		              <Option Value="C">C</Option>
  		              <Option Value="D">D</Option>
  		              <Option Value="E">E</Option>
  		              <Option Value="F">F</Option>
  		              <Option Value="G">G</Option>
  		              <Option Value="H">H</Option>
  		              <Option Value="I">I</Option>
  		              <Option Value="J">J</Option>
  		              <Option Value="K">K</Option>
  		              <Option Value="L">L</Option>
  		              <Option Value="M">M</Option>
  		              <Option Value="N">N</Option>
  		              <Option Value="O">O</Option>
  		              <Option Value="P">P</Option>
  		              <Option Value="Q">Q</Option>
  		              <Option Value="R">R</Option>
  		              <Option Value="S">S</Option>
  		              <Option Value="T">T</Option>
  		              <Option Value="U">U</Option>
  		              <Option Value="V">V</Option>
  		              <Option Value="W">W</Option>
  		              <Option Value="X">X</Option>
  		              <Option Value="Y">Y</Option>
  		              <Option Value="Z">Z</Option>
  		            </select>
  		          </td>
  		          <td>
  		            <label for="ordreTirage">{#Tirage#} :</label>
  		            <select name="ordreTirage" id="ordreTirage">
  		              <Option Value="0">nc</Option>
  		              {section name=i loop=$arrayEquipe}
    		              <Option Value="{$smarty.section.i.iteration}">{$smarty.section.i.iteration}</Option>
  		              {/section}
  		            </select>
  		          </td>
  		        </tr>
  		        <tr>
  		          <td colspan=3>
  		            <input type="button" onclick="Tirage();" name="tirageEquipe" id="tirageEquipe" value="{#Valider#}" />
  		          </td>
  		        </tr>
  		      </table>
		      {/if}
		      {if $profile == 1 && $AuthModif == 'O'}
  		      <br>
  		      <table width=100%>
  		        <tr>
  		          <th class='titreForm' colspan=3>
  		            <label>Update logos</label>
  		          </th>
  		        </tr>
  		        <tr>
  		          <td colspan=3>
  		            <label for="updateLogo">Update teams logos</label>
  		            <input type="button" onclick="UpdateLogos();" name="updateLogos" id="updateLogos" value="{#Valider#}" />
  		          </td>
  		        </tr>
  		      </table>
		      {/if}
		    </div>

		  </form>

</div>