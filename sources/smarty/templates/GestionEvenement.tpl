	<div class="main">
	  <form method="POST" action="GestionEvenement.php" name="formEvenement" enctype="multipart/form-data">
	    <input type='hidden' name='Cmd' Value='' />
	    <input type='hidden' name='ParamCmd' Value='' />
	    <input type='hidden' name='Pub' Value='' />
	    <input type='hidden' name='App' Value='N' />
	    <input type='hidden' name='idEvenement' Value='{$idEvenement}' />

	    <div class='blocLeft'>
	      <div class='titrePage'>{#Evenements#}</div>
	      <div class='blocTable' id='blocCompet'>
	        <table class='tableau' id='tableCompet'>
	          <thead>
	            <tr class='header'>
	              <th width=18><img width="19" src="../img/oeil2.gif" alt="{#Publier#} ?" title="{#Publier#} ?" /></th>
	              <th>Id</th>
	              {if $profile <= 2}
  	              <th width=18><img width="19" src="../img/appO.png" alt="{#App#} ?" title="{#App#} ?" /></th>
	              {/if}
	              <th>&nbsp;</th>
	              <th>{#Nom#}</th>
	              <th>{#Lieu#}</th>
	              <th>{#Date_debut#}</th>
	              <th>{#Date_fin#}</th>
	              <th>&nbsp;</th>
	            </tr>
	          </thead>
	          <tbody>
	            {section name=i loop=$arrayEvenement}
  	            <tr class='{cycle values="impair,pair"} {$arrayEvenement[i].StdOrSelected}'>
  	              {*
									<td><input type="checkbox" name="checkEvenement" value="{$arrayEvenement[i].Id}" id="checkDelete{$smarty.section.i.iteration}" /></td>
									*}
  	              <td class='color{$arrayEvenement[i].Publication}2'>
  	                <a href="#" Id="Publication{$arrayEvenement[i].Id}"
  	                  onclick="publiEvt({$arrayEvenement[i].Id},'{$arrayEvenement[i].Publication}')">
  	                  <img width="24" src="../img/oeil2{$arrayEvenement[i].Publication}.gif" alt="{#Publier#}"
  	                    title="{#Publier#}" />
  	                </a>
  	              </td>
  	              <td>{$arrayEvenement[i].Id}</td>
  	              {if $profile <= 2}
    	              <td class='color{$arrayEvenement[i].app}2'>
    	                <a href="#" Id="App{$arrayEvenement[i].Id}"
    	                  onclick="appEvt({$arrayEvenement[i].Id},'{$arrayEvenement[i].app}')">
    	                  <img width="24" src="../img/app{$arrayEvenement[i].app}.png" alt="{#App#}" title="{#App#}" />
    	                </a>
    	              </td>
  	              {/if}
  	              <td>
  	                <a href="#" Id="Param{$arrayEvenement[i].Id}" onclick="paramEvt({$arrayEvenement[i].Id})">
  	                  <img height="18" src="../img/glyphicons-31-pencil.png" alt="{#Editer#}" title="{#Editer#}" />
  	                </a>
  	              </td>
  	              <td>{$arrayEvenement[i].Libelle}</td>
  	              <td>{$arrayEvenement[i].Lieu}</td>
  	              <td>{$arrayEvenement[i].Date_debut}</td>
  	              <td>{$arrayEvenement[i].Date_fin}</td>
  	              {if $profile <= 1}
    	              <td><a href="#" onclick="RemoveCheckbox('formEvenement', '{$arrayEvenement[i].Id}');return false;"><img
    	                    height="20" src="../img/glyphicons-17-bin.png" alt="{#Supprimer#}" title="{#Supprimer#}" /></a>
    	              </td>
  	              {else}
    	              <td>&nbsp;</td>
  	              {/if}
  	            </tr>
	            {/section}
	          </tbody>
	        </table>
	      </div>
	    </div>
	    <div class='blocRight'>
	      <table width=100%>
	        <tr>
	          <th class='titreForm' colspan=2>
	            <label>{if $idEvenement == -1}{#Ajouter#}{else}{#Modifier#}{/if}</label>
	          </th>
	        </tr>
	        <tr>
	          <td colspan=2>
	            <label for="Libelle">{#Nom#} :</label>
	            <input type="text" name="Libelle" value="{$Libelle}" maxlength=40 id="Libelle" />
	          </td>
	        </tr>
	        <tr>
	          <td colspan=2>
	            <label for="Lieu">{#Lieu#} : </label>
	            <input type="text" name="Lieu" value="{$Lieu}" maxlength=40 id="Lieu" />
	          </td>
	        </tr>
	        <tr>
	          <td>
	            <label for="Date_debut">{#Date_debut#} :</label>
	            <input type="text" class='date' name="Date_debut" value="{$Date_debut}" id="Date_debut"
	              onfocus="displayCalendar(document.forms[0].Date_debut,{if $lang=='en'}'yyyy-mm-dd'{else}'dd/mm/yyyy'{/if},this)" />
	          </td>
	          <td>
	            <label for="Date_fin">{#Date_fin#} :</label>
	            <input type="text" class='date' name="Date_fin" value="{$Date_fin}" id="Date_fin"
	              onfocus="displayCalendar(document.forms[0].Date_fin,{if $lang=='en'}'yyyy-mm-dd'{else}'dd/mm/yyyy'{/if},this)" />
	          </td>
	        </tr>
	        <tr>
	          {if $idEvenement != -1}
  	          <td>
  	            <br>
  	            <br>
  	            <input type="button" onclick="updateEvt()" id="updateEvenement" name="updateEvenement"
  	              value="<< {#Modifier#}">
  	          </td>
  	          <td>
  	            <br>
  	            <br>
  	            <input type="button" onclick="razEvt()" id="razEvenement" name="razEvenement" value="{#Annuler#}">
  	          </td>
	          {else}
  	          <td colspan=2>
  	            <br>
  	            <br>
  	            <input type="button" onclick="addEvt()" name="addEvenement" value="<< {#Ajouter#}">
  	          </td>
	          {/if}
	        </tr>
	      </table>
	    </div>
	  </form>
</div>