<div class="container titre">
    <div class="col-md-9">
        <h1 class="col-md-11 col-xs-9">{#Historique#}</h1>
    </div>
<!--
    <div class="col-md-3">
        <span class="badge pull-right">{$smarty.config.Saison|default:'Saison'} {$Saison}</span>
    </div>
-->
</div>
<div class="container" id="selector">
    <article class="col-md-12 padTopBottom">
			<form class="form-inline" method="POST" action="kphistorique.php#selector" name="formHistorique" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value='' />
				<input type='hidden' name='ParamCmd' Value='' />
				<div class='col-md-8 col-sm-8 col-xs-12 form-group'>
								<label for="Group">{#Competition#} :</label>
								<select name="Group" onChange="submit();">
										<Option Value="">{#Selectionnez#}...</Option>
									{section name=i loop=$arrayCompetitionGroupe}
										{assign var='temporaire' value=$arrayCompetitionGroupe[i][1]}
										<Option Value="{$arrayCompetitionGroupe[i][1]}" {$arrayCompetitionGroupe[i][3]}>{$smarty.config.$temporaire|default:$arrayCompetitionGroupe[i][2]}</Option>
									{/section}
								</select>
                </div>
				<div class='col-md-4 col-sm-4 col-xs-12 text-center'>
					<div class="fb-like pull-right" data-href="http://www.kayak-polo.info/kphistorique.php?Group={$codeCompetGroup}" data-layout="button" data-action="recommend" data-show-faces="false" data-share="true"></div>
				</div>
            </form>
    </article>
</div>

<div role="tabpanel" class="container-fluid">
    <!-- Nav tabs -->
    <ul class="pagination" role="tablist">
        {section  name=i loop=$arraySaisons}
            <li role="presentation" {if $smarty.section.i.iteration == 1}class="active"{/if}><a href="#saison{$arraySaisons[i].saison}" aria-controls="saison{$arraySaisons[i].saison}" role="tab" data-toggle="tab">{$arraySaisons[i].saison}</a></li>
        {/section}
    </ul>
    <!-- Tab panes -->
    <div class="tab-content container-fluid">
        {section  name=i loop=$arraySaisons}
            {assign var='codesaison' value=$arraySaisons[i].saison}
            <article role="tabpanel" class="padTopBottom tab-pane{if $smarty.section.i.iteration == 1} active{/if}" id="saison{$arraySaisons[i].saison}">
                <h3 class="row">
                    <div class="col-md-6 col-sm-6">{#Saison#} {$arraySaisons[i].saison}</div>
                </h3>
                {if $arrayCompets[$codesaison][0].LogoLink != ''}
                    <div class="col-md-2 col-sm-4 hidden-xs">
						{if $arrayCompets[$codesaison][0].Web != ''}
							<a href='{$arrayCompets[$codesaison][0].Web}' target='_blank'>
						{/if}
                                <img class="img-thumbnail" src='{$arrayCompets[$codesaison][0].LogoLink}'>
						{if $arrayCompets[$codesaison][0].Web != ''}
							</a>
						{/if}
					</div>
                    <div class="col-md-1 col-sm-2 hidden-xs"></div>
                {/if}

                {section  name=j loop=$arrayCompets[$codesaison]}
                    {assign var='codecompet' value=$arrayCompets[$codesaison][j].code}
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <table class='table table-striped table-hover' id='tableMatchs'>
                            <caption>
                                {if $arrayCompets[$codesaison][j].Titre_actif != 'O' && $arrayCompets[$codesaison][j].Soustitre != ''}
                                    {$arrayCompets[$codesaison][j].Soustitre}
                                {else}
                                    {$arrayCompets[$codesaison][j].libelle}
                                {/if}
                                {if $arrayCompets[$codesaison][j].Soustitre2 != ''}<br>{$arrayCompets[$codesaison][j].Soustitre2}{/if}
                            </caption>
                            <tbody>
                                {section name=k loop=$arrayClts[$codesaison][$codecompet]}
                                    <tr>
                                        {if $arrayClts[$codesaison][$codecompet][k].Code_typeclt=='CHPT'}
                                            {if $arrayClts[$codesaison][$codecompet][k].Clt > 0 && $arrayClts[$codesaison][$codecompet][k].Clt <= 3}
                                                <td class='medaille'><img width="28" src="img/medal{$arrayClts[$codesaison][$codecompet][k].Clt}.gif" alt="Podium" /></td>
                                            {else}
                                                <td>{$arrayClts[$codesaison][$codecompet][k].Clt}</td>
                                            {/if}
                                        {else}
                                            {if $arrayClts[$codesaison][$codecompet][k].CltNiveau > 0 && $arrayClts[$codesaison][$codecompet][k].CltNiveau <= 3}
                                                <td class='medaille'><img width="28" src="img/medal{$arrayClts[$codesaison][$codecompet][k].CltNiveau}.gif" alt="Podium" /></td>
                                            {else}
                                                <td>{$arrayClts[$codesaison][$codecompet][k].CltNiveau}</td>
                                            {/if}
                                        {/if}
                                        <td class="cliquableNomEquipe">
											{if $arrayClts[$codesaison][$codecompet][k].logo != ''}
												<img class="img2 pull-left" width="28" src="{$arrayClts[$codesaison][$codecompet][k].logo}" alt="{$arrayClts[$codesaison][$codecompet][k].club}" />
											{/if}
                                            <a class="btn btn-xs btn-default" href='kpequipes.php?Equipe={$arrayClts[$codesaison][$codecompet][k].Numero}' title='{#Palmares#}'>{$arrayClts[$codesaison][$codecompet][k].Libelle}</a>
                                        </td>
                                    </tr>
                                {sectionelse}
                                    <tr>
                                        <td><i class="center-block">{#Pas_de_classement#}</i></td>
                                    </tr>
                                {/section}
                            </tbody>
                        </table>
                    </div>
                {/section}
            </article>
        {/section}
    </div>
</div>
