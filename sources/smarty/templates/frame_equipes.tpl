{if $codeCompet != '' || $Equipe == 0}
  <div class="container-fluid" id="navGroup">
    <nav class="col-md-12 navbar navbar-custom">
      <div class='pull-left'>
        <a class="btn btn-primary btn-navigation" href='javascript:history.back()'>
          < {#Retour#}</a>
      </div>
    </nav>
    {*        <nav class="col-md-12 navbar navbar-custom"></nav>*}
  </div>
{/if}
{if $Equipe > 0}
  <div class="container">
    <article class="col-md-6 padTopBottom">
      <div class="form-horizontal">
        {if $codeCompet == ''}
          <label class="col-sm-2">{#Chercher#}:</label>
          <input class="col-sm-6" type="text" id="rechercheEquipe" placeholder="{#Nom_de_l_equipe#}">
          <input class="col-sm-2" type="hidden" id="equipeId" value="{$Equipe}">
        {/if}
        <h2 class="col-sm-12 text-center" id="nomEquipe">{$nomEquipe}</h2>
        <div class="form-group">
          <div class="col-sm-12 text-center" id="nomClub">
            <a class="btn btn-xs btn-default" {if !$arrayCompo}href='kpclubs.php?clubId={$Code_club}' title='{#Club#}'
              {/if}>
              {$Club}
            </a>
            <div id="fb-root"></div>
            <div class="fb-like"
              data-href="https://www.kayak-polo.info/frame_equipes.php?Equipe={$Equipe}&Compet={$codeCompet}&Css={$Css}"
              data-layout="button" data-action="recommend" data-show-faces="false" data-share="true"></div>
          </div>
        </div>
      </div>
      {if $eColors}
        <div class="col-xs-10 col-xs-offset-1" id="equipeColors">
          <a href="{$eColors}?v={$NUM_VERSION}" target="_blank">
            <img class="img-responsive img-thumbnail" src="{$eColors}?v={$NUM_VERSION}" alt="{$nomEquipe}">
          </a>
          <span class="pull-right badge">{$eSeason}</span>
        </div>
      {elseif $eLogo}
        <div class="col-sm-6 col-sm-offset-3 col-xs-8 col-xs-offset-2" id="equipeColors">
          <a {if !$arrayCompo}href="frame_clubs.php?clubId={$Code_club}" title='{#Club#}' {/if}>
            <img class="img-responsive img-thumbnail" src="{$eLogo}?v={$NUM_VERSION}" alt="{$nomEquipe}">
          </a>
        </div>
      {/if}
    </article>

    <article class="col-md-6 padTopBottom" id="equipePalmares">
      {if $eTeam}
        <div class="col-sm-12" id="equipeTeam">
          <a href="{$eTeam}?v={$NUM_VERSION}" target="_blank">
            <img class="img-responsive img-thumbnail" src="{$eTeam}?v={$NUM_VERSION}" alt="{$nomEquipe}"
              title="{$nomEquipe}">
          </a>
          <span class="pull-right badge">{$eSeason2}</span>
        </div>
      {/if}

      {if $codeCompet != ''}
        <div class="page-header">
          <h4 class="text-info">{$recordCompetition.Soustitre}<br>{$recordCompetition.Soustitre2}</h4>
        </div>
        <table class='table table-condensed' id='tableStats'>
          <thead>
            <tr class='header'>
              <th class="text-center">#</th>
              <th>{#Nom#}</th>
              <th class="text-center">{#Buts#}</th>
              <th class="bg-success text-center">{#C_V#}</th>
              <th class="bg-warning text-center">{#C_J#}</th>
              <th class="bg-danger text-center">{#C_R#}</th>
              <th class="bg-danger text-center">{#C_D#}</th>
            </tr>
          </thead>
          <tbody>
            {section name=i loop=$arrayCompo}
              <tr class='{cycle values="impair,pair"}'>
                <td class="text-center">
                  {if $arrayCompo[i].Capitaine == 'E'}
                    <span class="label label-default" title="Staff">S</span>
                  {else}
                    {$arrayCompo[i].Numero}
                  {/if}
                </td>
                <td>
                  {$arrayCompo[i].Nom|upper} {$arrayCompo[i].Prenom|upper}
                  {if $arrayCompo[i].Capitaine == 'C'}
                    <span class="label label-default" title="{#Capitaine#}">C</span>
                  {/if}
                </td>
                <td class="text-center">
                  {if $arrayCompo[i].buts > 0}
                    <span class="badgeg">{$arrayCompo[i].buts}</span>
                  {/if}
                </td>
                <td class="text-center">
                  {if $arrayCompo[i].verts > 0}
                    <span class="label label-success">{$arrayCompo[i].verts}</span>
                  {/if}
                </td>
                <td class="text-center">
                  {if $arrayCompo[i].jaunes > 0}
                    <span class="label label-warning">{$arrayCompo[i].jaunes}</span>
                  {/if}
                </td>
                <td class="text-center">
                  {if $arrayCompo[i].rouges > 0}
                    <span class="label label-danger">{$arrayCompo[i].rouges}</span>
                  {/if}
                </td>
                <td class="text-center">
                  {if $arrayCompo[i].rouges_definitif > 0}
                    <span class="label label-danger">{$arrayCompo[i].rouges_definitif}</span>
                  {/if}
                </td>
              </tr>
              {sectionelse}
              <tr>
                <td colspan="6" class="text-center">{#Information_non_disponible#}</td>
              </tr>
            {/section}
          </tbody>
        </table>
      {/if}

      <div class="page-header">
        <h3 class="text-info">{#Palmares#}</h3>
      </div>
      {section  name=i loop=$arraySaisons}
        {assign var='Saison' value=$arraySaisons[i].Saison}
        <table class='table table-striped table-hover table-condensed' id='tableMatchs'>
          <caption>
            <h4>{$Saison}</h4>
          </caption>
          <tbody>
            {section  name=j loop=$arrayPalmares[$Saison]}
              {if $arrayPalmares[$Saison][j].Code_tour == 10}
                <tr>
                  <td>
                    <a class="btn btn-xs btn-default"
                      {if !$arrayCompo}href='kpclassements.php?Compet={$arrayPalmares[$Saison][j].Code}&Group={$arrayPalmares[$Saison][j].Code_ref}&Saison={$arrayPalmares[$Saison][j].Saison}'
                      title='{#Classement#}' {/if}>
                      {$arrayPalmares[$Saison][j].Competitions}
                    </a>
                  </td>
                  <td class="text-center">
                    {if $arrayPalmares[$Saison][j].Classt > 0 && $arrayPalmares[$Saison][j].Classt <= 3}
                      <img width="20" src="img/medal{$arrayPalmares[$Saison][j].Classt}.gif"
                        alt="{$arrayPalmares[$Saison][j].Classt}" title="{$arrayPalmares[$Saison][j].Classt}" />
                    {else}
                      {$arrayPalmares[$Saison][j].Classt}
                    {/if}

                  </td>
                </tr>
              {else}
                <tr>
                  <td class="text-right">
                    <a class="btn btn-xs btn-default"
                      {if !$arrayCompo}href='kpclassements.php?Compet={$arrayPalmares[$Saison][j].Code}&Group={$arrayPalmares[$Saison][j].Code_ref}&Saison={$arrayPalmares[$Saison][j].Saison}'
                      title='{#Classement#}' {/if}>
                      <i>{$arrayPalmares[$Saison][j].Competitions}</i>
                    </a>
                    <i>{$arrayPalmares[$Saison][j].Classt}</i>
                  </td>
                  <td></td>
                </tr>
              {/if}
            {/section}
          </tbody>
        </table>
        {sectionelse}
        <em class="text-right">{#Pas_de_classement_equipe#}.</em>
      {/section}
    </article>
  </div>
{/if}