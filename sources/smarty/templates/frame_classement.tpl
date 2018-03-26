<div class="container">
    <article class="padTopBottom{if $recordCompetition.Code_typeclt != 'CHPT'} table-responsive col-md-6 col-md-offset-3{else} col-md-12{/if} tableClassement">
        {if $recordCompetition.Statut != 'END'}
            <div class="label label-warning">{#Classement_provisoire#}</div>
        {/if}
		{*if $recordCompetition.Statut != 'END'*}
            <table class='table table-striped table-condensed table-hover tableGeneral'>
                {if $recordCompetition.Code_typeclt == 'CHPT'}
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th>#</th>
                            <th>
                                {#Equipes#}
                            </th>
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
                {else}
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th>#</th>
                            <th>
                                {#Equipes#}
                            </th>
                        </tr>
                    </thead>
                {/if}
                    <tbody>
                            {section name=i loop=$arrayEquipe_publi} 
                                <tr>
                                {if $recordCompetition.Code_typeclt=='CHPT' && $recordCompetition.Code_tour=='10' && $arrayEquipe_publi[i].Clt <= 3 && $arrayEquipe_publi[i].Clt > 0 && $arrayEquipe_publi[i].Statut == 'END'}
                                    <td class='medaille text-center'><img width="30" src="img/medal{$arrayEquipe_publi[i].Clt}.gif" alt="Podium" title="Podium" /></td>
                                {elseif $recordCompetition.Code_typeclt=='CP' && $recordCompetition.Code_tour=='10' && $arrayEquipe_publi[i].CltNiveau <= 3 && $arrayEquipe_publi[i].CltNiveau > 0 && $arrayEquipe_publi[i].Statut == 'END'}
                                    <td class='medaille text-center'><img width="30" src="img/medal{$arrayEquipe_publi[i].CltNiveau}.gif" alt="Podium" title="Podium" /></td>
                                {elseif $recordCompetition.Code_typeclt=='CHPT'}
                                    {if $smarty.section.i.iteration <= $recordCompetition.Qualifies}
                                        <td class='qualifie text-center'><img width="30" src="img/up.gif" alt="Qualifié" title="Qualifié" /></td>
                                    {elseif $smarty.section.i.iteration > $recordCompetition.Nb_equipes - $recordCompetition.Elimines}
                                        <td class='elimine text-center'><img width="30" src="img/down.gif" alt="Eliminés" title="Eliminés" /></td>
                                    {else}
                                        <td>&nbsp;</td>
                                    {/if}
                                {else}
                                    {if $smarty.section.i.iteration <= $recordCompetition.Qualifies}
                                        <td class='qualifie text-center'><img width="30" src="img/up.gif" alt="Qualifié" title="Qualifié" /></td>
                                    {elseif $smarty.section.i.iteration > $recordCompetition.Nb_equipes - $recordCompetition.Elimines}
                                        <td class='elimine text-center'><img width="30" src="img/down.gif" alt="Eliminés" title="Eliminés" /></td>
                                    {else}
                                        <td>&nbsp;</td>
                                    {/if}
                                {/if}

                                        {if $recordCompetition.Code_typeclt == 'CHPT'}
                                            <td class="droite">
                                                {$arrayEquipe_publi[i].Clt}
                                                {if $arrayEquipe_publi[i].logo != ''}
                                                    <img class="img2 pull-right" width="30" src="{$arrayEquipe_publi[i].logo}" alt="{$arrayEquipe_publi[i].club}" />
                                                {/if}
                                            </td>
                                            <td class="cliquableNomEquipe"><a class="btn btn-xs btn-default equipe">{$arrayEquipe_publi[i].Libelle}</a></td>
                                            <td>{$arrayEquipe_publi[i].Pts/100}</td>
                                            <td>{$arrayEquipe_publi[i].J}</td>
                                            <td>{$arrayEquipe_publi[i].G}</td>
                                            <td>{$arrayEquipe_publi[i].N}</td>
                                            <td>{$arrayEquipe_publi[i].P}</td>
                                            <td>{$arrayEquipe_publi[i].F}</td>
                                            <td>{$arrayEquipe_publi[i].Plus}</td>
                                            <td>{$arrayEquipe_publi[i].Moins}</td>
                                            <td>{$arrayEquipe_publi[i].Diff}</td>
                                        {else}
                                            <td class="droite">
                                                {$arrayEquipe_publi[i].CltNiveau}
                                            </td>
                                            <td class="cliquableNomEquipe">
												{if $arrayEquipe_publi[i].logo != ''}
													<img class="img2 pull-left" width="30" src="{$arrayEquipe_publi[i].logo}" alt="{$arrayEquipe_publi[i].club}" />
												{/if}
												<a class="btn btn-xs btn-default equipe">{$arrayEquipe_publi[i].Libelle}</a>
											</td>
                                            {*<td width="40">{$arrayEquipe_publi[i].PtsNiveau}</td>*}
                                        {/if}
                                </tr>
                            {/section}
                    </tbody>
            </table>
        {*/if*}
    </article>
</div>