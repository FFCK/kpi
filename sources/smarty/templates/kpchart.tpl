{include file='kpnavgroup.tpl'}
<div class="container-fluid">
  {if $recordCompetition.Code_typeclt == 'CP'}
    {assign var='idJournee' value=$arrayListJournees[0]}
    {assign var='Etape' value=$arrayJournees[$idJournee].Etape}
    <div class="row">
      <article class="col bg-light mx-1">
        {section name=i loop=$arrayListJournees}
          {assign var='idJournee' value=$arrayListJournees[i]}
          {if $Etape != $arrayJournees[$idJournee].Etape}
          </article>
          <article class="col bg-light mx-1">
          {/if}
          {assign var='Etape' value=$arrayJournees[$idJournee].Etape}
          {if $arrayJournees[$idJournee].Type == 'C'}
            <div>
              <h5 class="text-center">{$arrayJournees[$idJournee].Phase}</h5>
              <table class='table table-striped table-sm table-responsive'>
                <thead>
                  <tr>
                    <th class="text-center">#</th>
                    <th>{#Equipes#}</th>
                    <th class="text-center">{#Pts#}</th>
                    <th class="text-center">{#J#}</th>
                    <th class="text-center">{#Diff#}</th>
                  </tr>
                </thead>
                <tbody>
                  {if $arrayJournees[$idJournee].Actif == 1}
                    {section name=j loop=$arrayEquipe_journee_publi[$idJournee]}
                      <tr>
                        <td class="text-center">
                          {$arrayEquipe_journee_publi[$idJournee][j].Clt}
                          {if $arrayEquipe_journee_publi[$idJournee][j].logo != ''}
                            <img class="img2 float-end" width="30" src="{$arrayEquipe_journee_publi[$idJournee][j].logo}"
                              alt="{$arrayEquipe_journee_publi[$idJournee][j].club}" />
                          {/if}

                        </td>
                        <td>
                          <a class="btn btn-sm btn-outline-dark equipe" {if $arrayEquipe_journee_publi[$idJournee][j].Numero > 0}
                              href="kpequipes.php?Equipe={$arrayEquipe_journee_publi[$idJournee][j].Numero}&Compet={$codeCompet}&Css={$Css}"
                            title="{#Palmares#}" {/if}>{$arrayEquipe_journee_publi[$idJournee][j].Libelle}</a>
                        </td>
                        <td class="text-center">{$arrayEquipe_journee_publi[$idJournee][j].Pts/100}</td>
                        <td class="text-center">{$arrayEquipe_journee_publi[$idJournee][j].J}</td>
                        <td class="text-center">{$arrayEquipe_journee_publi[$idJournee][j].Diff}</td>
                      </tr>
                    {/section}
                  {else}
                    {if $arrayEquipes[$idJournee]}
                      {foreach from=$arrayEquipes[$idJournee] key=myId item=j}
                        <tr>
                          <td></td>
                          <td>
                            <a class="btn btn-sm btn-outline-dark equipe" {if $j.Num > 0}
                                href="frame_equipes.php?Equipe={$j.Num}&Compet={$codeCompet}&Css={$Css}" title="{#Palmares#}"
                              {/if}>{$j.Libelle}</a>
                          </td>
                          <td></td>
                          <td></td>
                          <td></td>
                        </tr>
                      {/foreach}
                    {else}
                      {section name=k loop=$arrayJournees[$idJournee].Nbequipes}
                        <tr>
                          <td class="text-center">{$smarty.section.k.iteration}</td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                        </tr>
                      {/section}
                    {/if}
                  {/if}
                </tbody>
              </table>
            </div>
          {elseif $arrayMatchs[$idJournee] && $arrayMatchs[$idJournee]|@count > 0}
            <div class="col">
              <h5 class="text-center">{$arrayJournees[$idJournee].Phase}</h5>
              {section name=j loop=$arrayMatchs[$idJournee]}
                <div class="row my-2 mx-0">
                  {if $arrayMatchs[$idJournee][j].ScoreA > $arrayMatchs[$idJournee][j].ScoreB}
                    <div class="col bg-light-grey">
                      <table class="float-end">
                        <tr>
                          <td rowspan="2">
                            <span class="me-2 text-secondary">#{$arrayMatchs[$idJournee][j].Numero_ordre}</span>
                          </td>
                          <td>
                            <a class="btn btn-sm btn-dark equipe" {if $arrayMatchs[$idJournee][j].NumA > 0}
                                href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumA}&Compet={$codeCompet}&Css={$Css}"
                              title="{#Palmares#}" {/if}>{$arrayMatchs[$idJournee][j].EquipeA}</a>
                            <a class="btn btn-sm btn-dark">{$arrayMatchs[$idJournee][j].ScoreA}</a>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <a class="btn btn-sm btn-outline-dark equipe" {if $arrayMatchs[$idJournee][j].NumB > 0}
                                href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumB}&Compet={$codeCompet}&Css={$Css}"
                              title="{#Palmares#}" {/if}>{$arrayMatchs[$idJournee][j].EquipeB}</a>
                            <a class="btn btn-sm btn-outline-dark">{$arrayMatchs[$idJournee][j].ScoreB}</a>
                          </td>
                        </tr>
                      </table>
                    </div>
                  {elseif $arrayMatchs[$idJournee][j].ScoreA < $arrayMatchs[$idJournee][j].ScoreB}
                    <div class="col bg-light-grey">
                      <table class="float-end">
                        <tr>
                          <td rowspan="2">
                            <span class="me-2 text-secondary">#{$arrayMatchs[$idJournee][j].Numero_ordre}</span>
                          </td>
                          <td>
                            <a class="btn btn-sm btn-dark equipe" {if $arrayMatchs[$idJournee][j].NumB > 0}
                                href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumB}&Compet={$codeCompet}&Css={$Css}"
                              title="{#Palmares#}" {/if}>{$arrayMatchs[$idJournee][j].EquipeB}</a>
                            <a class="btn btn-sm btn-dark">{$arrayMatchs[$idJournee][j].ScoreB}</a>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <a class="btn btn-sm btn-outline-dark equipe" {if $arrayMatchs[$idJournee][j].NumA > 0}
                                href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumA}&Compet={$codeCompet}&Css={$Css}"
                              title="{#Palmares#}" {/if}>{$arrayMatchs[$idJournee][j].EquipeA}</a>
                            <a class="btn btn-sm btn-outline-dark">{$arrayMatchs[$idJournee][j].ScoreA}</a>
                          </td>
                        </tr>
                      </table>
                    </div>
                  {else}
                    <div class="col bg-light-grey">
                      <table class="float-end">
                        <tr>
                          <td rowspan="2">
                            <span class="me-2 text-secondary">#{$arrayMatchs[$idJournee][j].Numero_ordre}</span>
                          </td>
                          <td>
                            <a class="btn btn-sm btn-outline-dark equipe" {if $arrayMatchs[$idJournee][j].NumA > 0}
                                href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumA}&Compet={$codeCompet}&Css={$Css}"
                              title="{#Palmares#}" {/if}>{$arrayMatchs[$idJournee][j].EquipeA}</a>
                            <a class="btn btn-sm btn-outline-dark">{$arrayMatchs[$idJournee][j].ScoreA}</a>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <a class="btn btn-sm btn-outline-dark equipe" {if $arrayMatchs[$idJournee][j].NumB > 0}
                                href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumB}&Compet={$codeCompet}&Css={$Css}"
                              title="{#Palmares#}" {/if}>{$arrayMatchs[$idJournee][j].EquipeB}</a>
                            <a class="btn btn-sm btn-outline-dark">{$arrayMatchs[$idJournee][j].ScoreB}</a>
                          </td>
                        </tr>
                      </table>
                    </div>
                  {/if}
                </div>
              {/section}
            </div>
          {/if}
        {/section}
      </article>
    </div>
  {else}
    {section name=i loop=$arrayJournee}
      {assign var='idJournee' value=$arrayJournee[i].Id_journee}
      <article class="padBottom table-responsive col-md-12 tableJournee">
        <div class="page-header">
          <h5>
            {$arrayJournee[i].Lieu} ({$arrayJournee[i].Departement}) {$arrayJournee[i].Date_debut|date_format:'%d/%m/%Y'} -
            {$arrayJournee[i].Date_fin|date_format:'%d/%m/%Y'}
            <a class="btn btn-sm btn-default pull-right"
              href="kpdetails.php?Saison={$Saison}&Group={$recordCompetition.Code_ref}&Compet={$codeCompet}&typ={$recordCompetition.Code_typeclt}&J={$arrayJournee[i].Id_journee}"
              title="{#Infos#}">{#Infos#}</a>
          </h5>
        </div>
        {section name=j loop=$arrayMatchs[$idJournee]}
          <div class="col-md-4 col-sm-6 col-xs-12">
            {if $arrayMatchs[$idJournee][j].ScoreA > $arrayMatchs[$idJournee][j].ScoreB}
              <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                <a class="btn btn-sm btn-primary equipe" {if $arrayMatchs[$idJournee][j].NumB > 0}
                    href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumA}&Compet={$codeCompet}&Css={$Css}"
                  title="{#Palmares#}" {/if}>{$arrayMatchs[$idJournee][j].EquipeA}</a>
                <a class="btn btn-sm btn-primary">{$arrayMatchs[$idJournee][j].ScoreA}</a>
              </div>
              <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                <a class="btn btn-sm btn-default">{$arrayMatchs[$idJournee][j].ScoreB}</a>
                <a class="btn btn-sm btn-default equipe" {if $arrayMatchs[$idJournee][j].NumB > 0}
                    href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumB}&Compet={$codeCompet}&Css={$Css}"
                  title="{#Palmares#}" {/if}>{$arrayMatchs[$idJournee][j].EquipeB}</a>
              </div>
            {elseif $arrayMatchs[$idJournee][j].ScoreA < $arrayMatchs[$idJournee][j].ScoreB}
              <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                <a class="btn btn-sm btn-default equipe" {if $arrayMatchs[$idJournee][j].NumB > 0}
                    href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumA}&Compet={$codeCompet}&Css={$Css}"
                  title="{#Palmares#}" {/if}>{$arrayMatchs[$idJournee][j].EquipeA}</a>
                <a class="btn btn-sm btn-default">{$arrayMatchs[$idJournee][j].ScoreA}</a>
              </div>
              <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                <a class="btn btn-sm btn-primary">{$arrayMatchs[$idJournee][j].ScoreB}</a>
                <a class="btn btn-sm btn-primary equipe" {if $arrayMatchs[$idJournee][j].NumB > 0}
                    href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumB}&Compet={$codeCompet}&Css={$Css}"
                  title="{#Palmares#}" {/if}>{$arrayMatchs[$idJournee][j].EquipeB}</a>
              </div>
            {else}
              <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                <a class="btn btn-sm btn-default equipe" {if $arrayMatchs[$idJournee][j].NumB > 0}
                    href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumA}&Compet={$codeCompet}&Css={$Css}"
                  title="{#Palmares#}" {/if}>{$arrayMatchs[$idJournee][j].EquipeA}</a>
                <a class="btn btn-sm btn-default">{$arrayMatchs[$idJournee][j].ScoreA}</a>
              </div>
              <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                <a class="btn btn-sm btn-default">{$arrayMatchs[$idJournee][j].ScoreB}</a>
                <a class="btn btn-sm btn-default equipe" {if $arrayMatchs[$idJournee][j].NumB > 0}
                    href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumB}&Compet={$codeCompet}&Css={$Css}"
                  title="{#Palmares#}" {/if}>{$arrayMatchs[$idJournee][j].EquipeB}</a>
              </div>
            {/if}
          </div>
        {/section}
      </article>
    {/section}

  {/if}
</div>