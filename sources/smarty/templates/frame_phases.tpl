{include file='frame_navgroup.tpl'}
{if $recordCompetition.Code_typeclt == 'CP'}
    <div class="container">
        {assign var='idJournee' value=$arrayListJournees[0]}
        {assign var='niveau' value=$arrayJournees[$idJournee].Niveau}
        {assign var='compteur' value=0}
        <article class="col-md-12">
        {foreach from=$arrayListJournees item=idJournee key=i}
            {assign var='idJourneeNext' value=$arrayListJournees[$i+1]|default:null}
            {if $niveau != $arrayJournees[$idJournee].Niveau}
                </article><article class="col-md-12">
            {/if}
            {assign var='niveau' value=$arrayJournees[$idJournee].Niveau}
            {if $arrayJournees[$idJournee].Type == 'C'}
                {assign var='compteur' value=$compteur+1}
                {if $compteur == 2}
                    {assign var='compteur' value=0}
                {/if}
                {if $idJourneeNext && isset($arrayJournees[$idJourneeNext]) && $niveau != $arrayJournees[$idJourneeNext].Niveau && $compteur == 1}
                    <div class="padTopBottom table-responsive col-md-6 col-md-offset-3 col-sm-12 tablePhase">
                    {assign var='compteur' value=0}
                {else}
                    <div class="padTopBottom table-responsive col-md-6 col-sm-12 tablePhase">
                {/if}
                    <h4>{$arrayJournees[$idJournee].Phase}</h4>
                    <table class='table table-striped table-condensed table-hover'>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{#Equipes#}</th>
                                <th>{#Pts#}</th>
                                <th>{#J#}</th>
                                <th>{#G#}</th>
                                <th>{#N#}</th>
                                <th>{#P#}</th>
{*                                <th>{#F#}</th>*}
                                <th>+</th>
                                <th>-</th>
                                <th>{#Diff#}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {if $arrayJournees[$idJournee].Actif == 1}
                                {foreach from=$arrayEquipe_journee_publi[$idJournee] item=j}
                                    <tr>
                                        <td>
                                            {$j.Clt}
                                        </td>
                                        <td>
                                            <a class="btn btn-xs btn-default"{if $j.Id > 0} href="frame_team.php?Team={$j.Id}&Compet={$codeCompet}&Css={$Css}&navGroup={$navGroup}" title="{#Palmares#}"{/if}>{$j.Libelle}</a>
                                        </td>
                                        <td>{$j.Pts/100}</td>
                                        <td>{$j.J}</td>
                                        <td>{$j.G}</td>
                                        <td>{$j.N}</td>
                                        <td>{$j.P}</td>
    {*                                    <td>{$j.F}</td>*}
                                        <td>{$j.Plus}</td>
                                        <td>{$j.Moins}</td>
                                        <td>{$j.Diff}</td>
                                    </tr>
                                {/foreach}
                            {else}
                                {if $arrayEquipes[$idJournee]}
                                    {foreach from=$arrayEquipes[$idJournee] key=myId item=j}
                                        <tr>
                                            <td></td>
                                            <td>
                                                <a class="btn btn-xs btn-default"{if $j.Id > 0} href="frame_team.php?Team={$j.Id}&Compet={$codeCompet}&Css={$Css}&navGroup={$navGroup}" title="{#Palmares#}"{/if}>{$j.Libelle}</a>
                                            </td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    {foreach from=$arrayJournees[$idJournee].Nbequipes item=k}
                                        <tr>
                                            <td>{$k@iteration}</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    {/foreach}
                                {/if}
                            {/if}
                        </tbody>
                    </table>
                </div>
            {elseif $arrayMatchs[$idJournee]|@count > 0}
                <div class="padTopBottom table-responsive col-md-12 tableMatch">
                    <h4 class="row text-center">{$arrayJournees[$idJournee].Phase}</h4>
                    {foreach from=$arrayMatchs[$idJournee] item=j}
                        {if $j@index is odd}
                            <br>
                        {/if}
                        <div class="row cliquableNomEquipe">
                            {if $j.ScoreA > $j.ScoreB}
                                <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                    <a class="btn btn-xs btn-primary"{if $j.Id_equipeA > 0} href="frame_team.php?Team={$j.Id_equipeA}&Compet={$codeCompet}&Css={$Css}&navGroup={$navGroup}" title="{#Palmares#}"{/if}>{$j.EquipeA}</a>
                                    <span class="btn btn-xs btn-primary score">{$j.ScoreA}</span>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                                    <span class="btn btn-xs btn-default score">{$j.ScoreB}</span>
                                    <a class="btn btn-xs btn-default"{if $j.Id_equipeB > 0} href="frame_team.php?Team={$j.Id_equipeB}&Compet={$codeCompet}&Css={$Css}&navGroup={$navGroup}" title="{#Palmares#}"{/if}>{$j.EquipeB}</a>
                                </div>
                            {elseif $j.ScoreA < $j.ScoreB}
                                <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                    <a class="btn btn-xs btn-default"{if $j.Id_equipeA > 0} href="frame_team.php?Team={$j.Id_equipeA}&Compet={$codeCompet}&Css={$Css}&navGroup={$navGroup}" title="{#Palmares#}"{/if}>{$j.EquipeA}</a>
                                    <span class="btn btn-xs btn-default score">{$j.ScoreA}</span>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                                    <span class="btn btn-xs btn-primary score">{$j.ScoreB}</span>
                                    <a class="btn btn-xs btn-primary"{if $j.Id_equipeB > 0} href="frame_team.php?Team={$j.Id_equipeB}&Compet={$codeCompet}&Css={$Css}&navGroup={$navGroup}" title="{#Palmares#}"{/if}>{$j.EquipeB}</a>
                                </div>
                            {else}
                                <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                    <a class="btn btn-xs btn-default"{if $j.Id_equipeA > 0} href="frame_team.php?Team={$j.Id_equipeA}&Compet={$codeCompet}&Css={$Css}&navGroup={$navGroup}" title="{#Palmares#}"{/if}>{$j.EquipeA}</a>
                                    <span class="btn btn-xs btn-default score">{$j.ScoreA}</span>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                                    <span class="btn btn-xs btn-default score">{$j.ScoreB}</span>
                                    <a class="btn btn-xs btn-default"{if $j.Id_equipeB > 0} href="frame_team.php?Team={$j.Id_equipeB}&Compet={$codeCompet}&Css={$Css}&navGroup={$navGroup}" title="{#Palmares#}"{/if}>{$j.EquipeB}</a>
                                </div>
                            {/if}
                        </div>
                    {/foreach}
                </div>
            {/if}
        {/foreach}
        </article>
    </div>
{else}
    <div class="container-fluid">
        {foreach from=$arrayJournee item=journee}
            {assign var='idJournee' value=$journee.Id_journee}
                <article class="padTopBottom table-responsive col-md-12 tableJournee">
                    <div class="page-header">
                        <h4>
                            {$journee.Lieu} ({$journee.Departement}) {$journee.Date_debut|date_format:'%d/%m/%Y'} - {$journee.Date_fin|date_format:'%d/%m/%Y'}
                        </h4>
                    </div>
                    {foreach from=$arrayMatchs[$idJournee] item=j}
                        <div class="col-md-4 col-sm-6 col-xs-12">
                            {if $j.ScoreA > $j.ScoreB}
                                <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                    <a class="btn btn-xs btn-primary"{if $j.Id_equipeA > 0} href="frame_team.php?Team={$j.Id_equipeA}&Compet={$codeCompet}&Css={$Css}&navGroup={$navGroup}" title="{#Palmares#}"{/if}>{$j.EquipeA}</a>
                                    <span class="btn btn-xs btn-primary">{$j.ScoreA}</span>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                                    <span class="btn btn-xs btn-default">{$j.ScoreB}</span>
                                    <a class="btn btn-xs btn-default"{if $j.Id_equipeB > 0} href="frame_team.php?Team={$j.Id_equipeB}&Compet={$codeCompet}&Css={$Css}&navGroup={$navGroup}" title="{#Palmares#}"{/if}>{$j.EquipeB}</a>
                                </div>
                            {elseif $j.ScoreA < $j.ScoreB}
                                <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                    <a class="btn btn-xs btn-default"{if $j.Id_equipeA > 0} href="frame_team.php?Team={$j.Id_equipeA}&Compet={$codeCompet}&Css={$Css}&navGroup={$navGroup}" title="{#Palmares#}"{/if}>{$j.EquipeA}</a>
                                    <span class="btn btn-xs btn-default">{$j.ScoreA}</span>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                                    <span class="btn btn-xs btn-primary">{$j.ScoreB}</span>
                                    <a class="btn btn-xs btn-primary"{if $j.Id_equipeB > 0} href="frame_team.php?Team={$j.Id_equipeB}&Compet={$codeCompet}&Css={$Css}&navGroup={$navGroup}" title="{#Palmares#}"{/if}>{$j.EquipeB}</a>
                                </div>
                            {else}
                                <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                    <a class="btn btn-xs btn-default"{if $j.Id_equipeA > 0} href="frame_team.php?Team={$j.Id_equipeA}&Compet={$codeCompet}&Css={$Css}&navGroup={$navGroup}" title="{#Palmares#}"{/if}>{$j.EquipeA}</a>
                                    <span class="btn btn-xs btn-default">{$j.ScoreA}</span>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                                    <span class="btn btn-xs btn-default">{$j.ScoreB}</span>
                                    <a class="btn btn-xs btn-default"{if $j.Id_equipeB > 0} href="frame_team.php?Team={$j.Id_equipeB}&Compet={$codeCompet}&Css={$Css}&navGroup={$navGroup}" title="{#Palmares#}"{/if}>{$j.EquipeB}</a>
                                </div>
                            {/if}
                        </div>
                    {/foreach}
                </article>
        {/foreach}
        
    </div>
{/if}
