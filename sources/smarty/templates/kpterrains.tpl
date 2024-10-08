{include file='kpnavgroup.tpl'}
{section name=i loop=$arrayDates}
  {assign var='Date' value=$arrayDates[i].date}
  <div class="section terrains">
    {*<div class="container-fluid titre-date">
            <div class="col-md-12">
                <h3 class="col-md-11 col-xs-9">{#Matchs#} {$arrayDates[i].date}</h3>
            </div>
        </div>*}

    {if $nbTerrains == 1 }
      {assign var='terrain' value=$numTerrain[0]}
      <div class="container-fluid" id="containor">
        <article class="table-responsive col-md-12 padTopBottom">
          <table class='tableau table table-striped table-condensed table-responsive table-hover display compact'>
            <thead>
              <tr class="text-center">
                <th colspan="9" class="text-center bg-primary text-white" width="100%">{$arrayDates[i].date} - {#Terrain#}
                  {$terrain}</th>
              </tr>
              <tr>
                <th class="text-center" width="4%">{#Heure#}</th>
                <th>#</th>
                <th>{#Cat#}</th>
                <th>{#Poules#}</th>
                <th class="cliquableNomEquipe">{#Equipe_A#}</th>
                <th class="cliquableScore">{#Score#}</th>
                <th class="cliquableNomEquipe">{#Equipe_B#}</th>
                <th class="cliquableNomEquipe">{#Arbitre_1#}</th>
                <th class="cliquableNomEquipe">{#Arbitre_2#}</th>
              </tr>
            </thead>
            <tbody>
              {section name=j loop=$arrayHeures[$Date]}
                {assign var='Heure' value=$arrayHeures[$Date][j].heure}
                {assign var='Match1' value=$arrayMatchs[$Date][$Heure][$terrain]}
                {assign var='validation1' value=$Match1.Validation}
                {assign var='statut1' value=$Match1.Statut}
                {assign var='periode1' value=$Match1.Periode}

                <tr class='{$Match1.past}'>
                  {if $Match1.Numero_ordre}
                    <td class="text-center" data-order="{$Heure}" data-filter="{$Heure}">
                      <span class="badge">{$Heure}</span>
                    </td>
                    <td>{$Match1.Numero_ordre}</td>
                    <td class="cat" data-cat="{$Match1.Categorie}">{$Match1.Categorie}</td>
                    <td>{$Match1.Phase|default:'&nbsp;'}</td>
                    <td class="text-center" data-filter="{$Match1.EquipeA|default:'&nbsp;'}">
                      <a class="btn btn-xs btn-default" {if $Match1.NumA > 0}
                          href="kpequipes.php?Equipe={$Match1.NumA}&Compet={$Match1.Code_competition}&Css={$Css}"
                        title="{#Palmares#}" {/if}>
                        {$Match1.EquipeA|default:'&nbsp;'}
                      </a>
                    </td>
                    <td class="text-center"><span title="{$Match1.Id}">
                        {if $validation1 == 'O' && $Match1.ScoreA != '?' && $Match1.ScoreA != '' && $Match1.ScoreB != '?' && $Match1.ScoreB != ''}
                          <a class="btn btn-success btn-xs" href="PdfMatchMulti.php?listMatch={$Match1.Id}" target="_blank"
                            title="{#END#} - {#Feuille_marque#}">
                            {$Match1.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                            {$Match1.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                          </a>
                        {elseif $statut1 == 'ON' && $validation1 != 'O'}
                          <button type="button" class="btn btn-warning btn-xs scoreProvisoire" title="{#scoreProvisoire#}">
                            {$Match1.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                            {$Match1.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                          </button>
                        {elseif $statut1 == 'END' && $validation1 != 'O'}
                          <button type="button" class="btn btn-warning btn-xs scoreProvisoire" title="{#scoreProvisoire#}">
                            {$Match1.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                            {$Match1.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                          </button>
                        {else}
                          <button type="button" class="btn btn-default btn-xs" title="{#ATT#}">
                            {#ATT#}
                          </button>
                        {/if}
                      </span>
                    </td>
                    <td class="text-center" data-filter="{$Match1.EquipeB|default:'&nbsp;'}">
                      <a class="btn btn-xs btn-default" {if $Match1.NumB > 0}
                          href="kpequipes.php?Equipe={$Match1.NumB}&Compet={$Match1.Code_competition}&Css={$Css}"
                        title="{#Palmares#}" {/if}>
                        {$Match1.EquipeB|default:'&nbsp;'}
                      </a>
                    </td>
                    <td class="text-center">
                      <a class="btn btn-xs btn-default arbitre">{$Match1.Arbitre_principal|default:'&nbsp;'}</a>
                    </td>
                    <td class="text-center">
                      <a class="btn btn-xs btn-default arbitre">{$Match1.Arbitre_secondaire|default:'&nbsp;'}</a>
                    </td>
                  {else}
                    <td colspan="6" class="pause">{#Pause#}</td>
                  {/if}

                </tr>
                {sectionelse}
                <tr>
                  <td colspan=13 align=center><i>{#Aucun_match#}</i></td>
                </tr>
              {/section}
            </tbody>
          </table>
        </article>

      {else}
        {assign var='terrainG' value=$numTerrain[0]}
        {assign var='terrainD' value=$numTerrain[1]}
        <div class="container-fluid" id="containor">
          <article class="table-responsive col-md-12 padTopBottom">
            <table class='tableau table table-striped table-condensed table-responsive table-hover display compact'>
              <thead>
                <tr class="text-center">
                  <th colspan="6" class="text-center bg-primary text-white" width="48%">{$arrayDates[i].date} - {#Terrain#}
                    {$terrainG}</th>
                  <th rowspan="2" class="text-center" width="4%">{#Heure#}</th>
                  <th colspan="6" class="text-center bg-primary text-white" width="48%">{$arrayDates[i].date} - {#Terrain#}
                    {$terrainD}</th>
                </tr>
                <tr>
                  <th>#</th>
                  <th>{#Cat#}</th>
                  <th>{#Poules#}</th>
                  <th class="cliquableNomEquipe">{#Equipe_A#}</th>
                  <th class="cliquableScore">{#Score#}</th>
                  <th class="cliquableNomEquipe">{#Equipe_B#}</th>

                  <th>#</th>
                  <th>{#Cat#}</th>
                  <th>{#Poules#}</th>
                  <th class="cliquableNomEquipe">{#Equipe_A#}</th>
                  <th class="cliquableScore">{#Score#}</th>
                  <th class="cliquableNomEquipe">{#Equipe_B#}</th>
                </tr>
              </thead>
              <tbody>
                {section name=j loop=$arrayHeures[$Date]}
                  {assign var='Heure' value=$arrayHeures[$Date][j].heure}
                  {assign var='Match1' value=$arrayMatchs[$Date][$Heure][$terrainG]}
                  {assign var='validation1' value=$Match1.Validation}
                  {assign var='statut1' value=$Match1.Statut}
                  {assign var='periode1' value=$Match1.Periode}
                  {assign var='Match2' value=$arrayMatchs[$Date][$Heure][$terrainD]}
                  {assign var='validation2' value=$Match2.Validation}
                  {assign var='statut2' value=$Match2.Statut}
                  {assign var='periode2' value=$Match2.Periode}

                  <tr class='{$Match1.past}'>
                    {if $Match1.Numero_ordre}
                      <td>{$Match1.Numero_ordre}</td>
                      <td class="cat" data-cat="{$Match1.Categorie}">{$Match1.Categorie}</td>
                      <td>{$Match1.Phase|default:'&nbsp;'}</td>
                      <td class="text-center" data-filter="{$Match1.EquipeA|default:'&nbsp;'}">
                        <a class="btn btn-xs btn-default" {if $Match1.NumA > 0}
                            href="kpequipes.php?Equipe={$Match1.NumA}&Compet={$Match1.Code_competition}&Css={$Css}"
                          title="{#Palmares#}" {/if}>
                          {$Match1.EquipeA|default:'&nbsp;'}
                        </a>
                      </td>
                      <td class="text-center"><span title="{$Match1.Id}">
                          {if $validation1 == 'O' && $Match1.ScoreA != '?' && $Match1.ScoreA != '' && $Match1.ScoreB != '?' && $Match1.ScoreB != ''}
                            <a class="btn btn-success btn-xs" href="PdfMatchMulti.php?listMatch={$Match1.Id}" target="_blank"
                              title="{#END#} - {#Feuille_marque#}">
                              {$Match1.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                              {$Match1.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                            </a>
                          {elseif $statut1 == 'ON' && $validation1 != 'O'}
                            <button type="button" class="btn btn-warning btn-xs scoreProvisoire" title="{#scoreProvisoire#}">
                              {$Match1.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                              {$Match1.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                            </button>
                          {elseif $statut1 == 'END' && $validation1 != 'O'}
                            <button type="button" class="btn btn-warning btn-xs scoreProvisoire" title="{#scoreProvisoire#}">
                              {$Match1.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                              {$Match1.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                            </button>
                          {else}
                            <button type="button" class="btn btn-default btn-xs" title="{#ATT#}">
                              {#ATT#}
                            </button>
                          {/if}
                        </span>
                      </td>
                      <td class="text-center" data-filter="{$Match1.EquipeB|default:'&nbsp;'}">
                        <a class="btn btn-xs btn-default" {if $Match1.NumB > 0}
                            href="kpequipes.php?Equipe={$Match1.NumB}&Compet={$Match1.Code_competition}&Css={$Css}"
                          title="{#Palmares#}" {/if}>
                          {$Match1.EquipeB|default:'&nbsp;'}
                        </a>
                      </td>
                    {else}
                      <td colspan="6" class="pause">{#Pause#}</td>
                    {/if}

                    <td class="text-center" data-order="{$Heure}" data-filter="{$Heure}">
                      <span class="badge">{$Heure}</span>
                    </td>

                    {if $Match2.Numero_ordre}
                      <td>{$Match2.Numero_ordre}</td>
                      <td class="cat" data-cat="{$Match2.Categorie}">{$Match2.Categorie}</td>
                      <td>{$Match2.Phase|default:'&nbsp;'}</td>
                      <td class="text-center" data-filter="{$Match2.EquipeA|default:'&nbsp;'}">
                        <a class="btn btn-xs btn-default" {if $Match2.NumA > 0}
                            href="kpequipes.php?Equipe={$Match2.NumA}&Compet={$Match2.Code_competition}&Css={$Css}"
                          title="{#Palmares#}" {/if}>
                          {$Match2.EquipeA|default:'&nbsp;'}
                        </a>
                      </td>
                      <td class="text-center"><span title="{$Match2.Id}">
                          {if $validation2 == 'O' && $Match2.ScoreA != '?' && $Match2.ScoreA != '' && $Match2.ScoreB != '?' && $Match2.ScoreB != ''}
                            <a class="btn btn-success btn-xs" href="PdfMatchMulti.php?listMatch={$Match2.Id}" target="_blank"
                              title="{#END#} - {#Feuille_marque#}">
                              {$Match2.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                              {$Match2.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                            </a>
                          {elseif $statut2 == 'ON' && $validation2 != 'O'}
                            <button type="button" class="btn btn-warning btn-xs scoreProvisoire" title="{#scoreProvisoire#}">
                              {$Match2.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                              {$Match2.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                            </button>
                          {elseif $statut2 == 'END' && $validation2 != 'O'}
                            <button type="button" class="btn btn-warning btn-xs scoreProvisoire" title="{#scoreProvisoire#}">
                              {$Match2.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                              {$Match2.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                            </button>
                          {else}
                            <button type="button" class="btn btn-default btn-xs" title="{#ATT#}">
                              {#ATT#}
                            </button>
                          {/if}
                        </span>
                      </td>
                      <td class="text-center" data-filter="{$Match2.EquipeB|default:'&nbsp;'}">
                        <a class="btn btn-xs btn-default" {if $Match2.NumB > 0}
                            href="kpequipes.php?Equipe={$Match2.NumB}&Compet={$Match2.Code_competition}&Css={$Css}"
                          title="{#Palmares#}" {/if}>
                          {$Match2.EquipeB|default:'&nbsp;'}
                        </a>
                      </td>
                    {else}
                      <td colspan="6" class="pause">{#Pause#}</td>
                    {/if}
                  </tr>
                  {sectionelse}
                  <tr>
                    <td colspan=13 align=center><i>{#Aucun_match#}</i></td>
                  </tr>
                {/section}
              </tbody>
            </table>
          </article>

          {if $nbTerrains > 2}
            {assign var='terrainG' value=$numTerrain[2]}
            {assign var='terrainD' value=$numTerrain[3]}
            <article class="table-responsive col-md-12 padTopBottom">
              <table class='tableau table table-striped table-condensed table-responsive table-hover display compact'>
                <thead>
                  <tr class="text-center">
                    <th colspan="6" class="text-center bg-primary text-white" width="48%">{$arrayDates[i].date} - {#Terrain#}
                      {$terrainG}</th>
                    <th rowspan="2" class="text-center" width="4%">{#Heure#}</th>
                    <th colspan="6" class="text-center bg-primary text-white" width="48%">{$arrayDates[i].date} - {#Terrain#}
                      {$terrainD}</th>
                  </tr>
                  <tr>
                    <th>#</th>
                    <th>{#Cat#}</th>
                    <th>{#Poules#}</th>
                    <th class="cliquableNomEquipe">{#Equipe_A#}</th>
                    <th class="cliquableScore">{#Score#}</th>
                    <th class="cliquableNomEquipe">{#Equipe_B#}</th>

                    <th>#</th>
                    <th>{#Cat#}</th>
                    <th>{#Poules#}</th>
                    <th class="cliquableNomEquipe">{#Equipe_A#}</th>
                    <th class="cliquableScore">{#Score#}</th>
                    <th class="cliquableNomEquipe">{#Equipe_B#}</th>
                  </tr>
                </thead>
                <tbody>
                  {section name=j loop=$arrayHeures[$Date]}
                    {assign var='Heure' value=$arrayHeures[$Date][j].heure}
                    {assign var='Match1' value=$arrayMatchs[$Date][$Heure][$terrainG]}
                    {assign var='validation1' value=$Match1.Validation}
                    {assign var='statut1' value=$Match1.Statut}
                    {assign var='periode1' value=$Match1.Periode}
                    {assign var='Match2' value=$arrayMatchs[$Date][$Heure][$terrainD]}
                    {assign var='validation2' value=$Match2.Validation}
                    {assign var='statut2' value=$Match2.Statut}
                    {assign var='periode2' value=$Match2.Periode}

                    <tr class='{$Match1.past}'>
                      {if $Match1.Numero_ordre}
                        <td>{$Match1.Numero_ordre}</td>
                        <td class="cat" data-cat="{$Match1.Categorie}">{$Match1.Categorie}</td>
                        <td>{$Match1.Phase|default:'&nbsp;'}</td>
                        <td class="text-center" data-filter="{$Match1.EquipeA|default:'&nbsp;'}">
                          <a class="btn btn-xs btn-default" {if $Match1.NumA > 0}
                              href="kpequipes.php?Equipe={$Match1.NumA}&Compet={$Match1.Code_competition}&Css={$Css}"
                            title="{#Palmares#}" {/if}>
                            {$Match1.EquipeA|default:'&nbsp;'}
                          </a>
                        </td>
                        <td class="text-center"><span title="{$Match1.Id}">
                            {if $validation1 == 'O' && $Match1.ScoreA != '?' && $Match1.ScoreA != '' && $Match1.ScoreB != '?' && $Match1.ScoreB != ''}
                              <a class="btn btn-success btn-xs" href="PdfMatchMulti.php?listMatch={$Match1.Id}" target="_blank"
                                title="{#END#} - {#Feuille_marque#}">
                                {$Match1.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                                {$Match1.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                              </a>
                            {elseif $statut1 == 'ON' && $validation1 != 'O'}
                              <button type="button" class="btn btn-warning btn-xs scoreProvisoire" title="{#scoreProvisoire#}">
                                {$Match1.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                                {$Match1.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                              </button>
                            {elseif $statut1 == 'END' && $validation1 != 'O'}
                              <button type="button" class="btn btn-warning btn-xs scoreProvisoire" title="{#scoreProvisoire#}">
                                {$Match1.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                                {$Match1.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                              </button>
                            {else}
                              <button type="button" class="btn btn-default btn-xs" title="{#ATT#}">
                                {#ATT#}
                              </button>
                            {/if}
                          </span>
                        </td>
                        <td class="text-center" data-filter="{$Match1.EquipeB|default:'&nbsp;'}">
                          <a class="btn btn-xs btn-default" {if $Match1.NumB > 0}
                              href="kpequipes.php?Equipe={$Match1.NumB}&Compet={$Match1.Code_competition}&Css={$Css}"
                            title="{#Palmares#}" {/if}>
                            {$Match1.EquipeB|default:'&nbsp;'}
                          </a>
                        </td>
                      {else}
                        <td colspan="6" class="pause">{#Pause#}</td>
                      {/if}

                      <td class="text-center" data-order="{$Heure}" data-filter="{$Heure}">
                        <span class="badge">{$Heure}</span>
                      </td>

                      {if $Match2.Numero_ordre}
                        <td>{$Match2.Numero_ordre}</td>
                        <td class="cat" data-cat="{$Match2.Categorie}">{$Match2.Categorie}</td>
                        <td>{$Match2.Phase|default:'&nbsp;'}</td>
                        <td class="text-center" data-filter="{$Match2.EquipeA|default:'&nbsp;'}">
                          <a class="btn btn-xs btn-default" {if $Match2.NumA > 0}
                              href="kpequipes.php?Equipe={$Match2.NumA}&Compet={$Match2.Code_competition}&Css={$Css}"
                            title="{#Palmares#}" {/if}>
                            {$Match2.EquipeA|default:'&nbsp;'}
                          </a>
                        </td>
                        <td class="text-center"><span title="{$Match2.Id}">
                            {if $validation2 == 'O' && $Match2.ScoreA != '?' && $Match2.ScoreA != '' && $Match2.ScoreB != '?' && $Match2.ScoreB != ''}
                              <a class="btn btn-success btn-xs" href="PdfMatchMulti.php?listMatch={$Match2.Id}" target="_blank"
                                title="{#END#} - {#Feuille_marque#}">
                                {$Match2.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                                {$Match2.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                              </a>
                            {elseif $statut2 == 'ON' && $validation2 != 'O'}
                              <button type="button" class="btn btn-warning btn-xs scoreProvisoire" title="{#scoreProvisoire#}">
                                {$Match2.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                                {$Match2.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                              </button>
                            {elseif $statut2 == 'END' && $validation2 != 'O'}
                              <button type="button" class="btn btn-warning btn-xs scoreProvisoire" title="{#scoreProvisoire#}">
                                {$Match2.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                                {$Match2.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                              </button>
                            {else}
                              <button type="button" class="btn btn-default btn-xs" title="{#ATT#}">
                                {#ATT#}
                              </button>
                            {/if}
                          </span>
                        </td>
                        <td class="text-center" data-filter="{$Match2.EquipeB|default:'&nbsp;'}">
                          <a class="btn btn-xs btn-default" {if $Match2.NumB > 0}
                              href="kpequipes.php?Equipe={$Match2.NumB}&Compet={$Match2.Code_competition}&Css={$Css}"
                            title="{#Palmares#}" {/if}>
                            {$Match2.EquipeB|default:'&nbsp;'}
                          </a>
                        </td>
                      {else}
                        <td colspan="6" class="pause">{#Pause#}</td>
                      {/if}
                    </tr>
                    {sectionelse}
                    <tr>
                      <td colspan=13 align=center><i>{#Aucun_match#}</i></td>
                    </tr>
                  {/section}
                </tbody>
              </table>
            </article>
          {/if}
          {if $nbTerrains > 4}
            {assign var='terrainG' value=$numTerrain[4]}
            {assign var='terrainD' value=$numTerrain[5]}
            <article class="table-responsive col-md-12 padTopBottom">
              <table class='tableau table table-striped table-condensed table-responsive table-hover display compact'>
                <thead>
                  <tr class="text-center">
                    <th colspan="6" class="text-center bg-primary text-white" width="48%">{$arrayDates[i].date} - {#Terrain#}
                      {$terrainG}</th>
                    <th rowspan="2" class="text-center" width="4%">{#Heure#}</th>
                    <th colspan="6" class="text-center bg-primary text-white" width="48%">{$arrayDates[i].date} - {#Terrain#}
                      {$terrainD}</th>
                  </tr>
                  <tr>
                    <th>#</th>
                    <th>{#Cat#}</th>
                    <th>{#Poules#}</th>
                    <th class="cliquableNomEquipe">{#Equipe_A#}</th>
                    <th class="cliquableScore">{#Score#}</th>
                    <th class="cliquableNomEquipe">{#Equipe_B#}</th>

                    <th>#</th>
                    <th>{#Cat#}</th>
                    <th>{#Poules#}</th>
                    <th class="cliquableNomEquipe">{#Equipe_A#}</th>
                    <th class="cliquableScore">{#Score#}</th>
                    <th class="cliquableNomEquipe">{#Equipe_B#}</th>
                  </tr>
                </thead>
                <tbody>
                  {section name=j loop=$arrayHeures[$Date]}
                    {assign var='Heure' value=$arrayHeures[$Date][j].heure}
                    {assign var='Match1' value=$arrayMatchs[$Date][$Heure][$terrainG]}
                    {assign var='validation1' value=$Match1.Validation}
                    {assign var='statut1' value=$Match1.Statut}
                    {assign var='periode1' value=$Match1.Periode}
                    {assign var='Match2' value=$arrayMatchs[$Date][$Heure][$terrainD]}
                    {assign var='validation2' value=$Match2.Validation}
                    {assign var='statut2' value=$Match2.Statut}
                    {assign var='periode2' value=$Match2.Periode}

                    <tr class='{$Match1.past}'>
                      {if $Match1.Numero_ordre}
                        <td>{$Match1.Numero_ordre}</td>
                        <td class="cat" data-cat="{$Match1.Categorie}">{$Match1.Categorie}</td>
                        <td>{$Match1.Phase|default:'&nbsp;'}</td>
                        <td class="text-center" data-filter="{$Match1.EquipeA|default:'&nbsp;'}">
                          <a class="btn btn-xs btn-default" {if $Match1.NumA > 0}
                              href="kpequipes.php?Equipe={$Match1.NumA}&Compet={$Match1.Code_competition}&Css={$Css}"
                            title="{#Palmares#}" {/if}>
                            {$Match1.EquipeA|default:'&nbsp;'}
                          </a>
                        </td>
                        <td class="text-center"><span title="{$Match1.Id}">
                            {if $validation1 == 'O' && $Match1.ScoreA != '?' && $Match1.ScoreA != '' && $Match1.ScoreB != '?' && $Match1.ScoreB != ''}
                              <a class="btn btn-success btn-xs" href="PdfMatchMulti.php?listMatch={$Match1.Id}" target="_blank"
                                title="{#END#} - {#Feuille_marque#}">
                                {$Match1.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                                {$Match1.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                              </a>
                            {elseif $statut1 == 'ON' && $validation1 != 'O'}
                              <button type="button" class="btn btn-warning btn-xs scoreProvisoire" title="{#scoreProvisoire#}">
                                {$Match1.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                                {$Match1.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                              </button>
                            {elseif $statut1 == 'END' && $validation1 != 'O'}
                              <button type="button" class="btn btn-warning btn-xs scoreProvisoire" title="{#scoreProvisoire#}">
                                {$Match1.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                                {$Match1.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                              </button>
                            {else}
                              <button type="button" class="btn btn-default btn-xs" title="{#ATT#}">
                                {#ATT#}
                              </button>
                            {/if}
                          </span>
                        </td>
                        <td class="text-center" data-filter="{$Match1.EquipeB|default:'&nbsp;'}">
                          <a class="btn btn-xs btn-default" {if $Match1.NumB > 0}
                              href="kpequipes.php?Equipe={$Match1.NumB}&Compet={$Match1.Code_competition}&Css={$Css}"
                            title="{#Palmares#}" {/if}>
                            {$Match1.EquipeB|default:'&nbsp;'}
                          </a>
                        </td>
                      {else}
                        <td colspan="6" class="pause">{#Pause#}</td>
                      {/if}

                      <td class="text-center" data-order="{$Heure}" data-filter="{$Heure}">
                        <span class="badge">{$Heure}</span>
                      </td>

                      {if $Match2.Numero_ordre}
                        <td>{$Match2.Numero_ordre}</td>
                        <td class="cat" data-cat="{$Match2.Categorie}">{$Match2.Categorie}</td>
                        <td>{$Match2.Phase|default:'&nbsp;'}</td>
                        <td class="text-center" data-filter="{$Match2.EquipeA|default:'&nbsp;'}">
                          <a class="btn btn-xs btn-default" {if $Match2.NumA > 0}
                              href="kpequipes.php?Equipe={$Match2.NumA}&Compet={$Match2.Code_competition}&Css={$Css}"
                            title="{#Palmares#}" {/if}>
                            {$Match2.EquipeA|default:'&nbsp;'}
                          </a>
                        </td>
                        <td class="text-center"><span title="{$Match2.Id}">
                            {if $validation2 == 'O' && $Match2.ScoreA != '?' && $Match2.ScoreA != '' && $Match2.ScoreB != '?' && $Match2.ScoreB != ''}
                              <a class="btn btn-success btn-xs" href="PdfMatchMulti.php?listMatch={$Match2.Id}" target="_blank"
                                title="{#END#} - {#Feuille_marque#}">
                                {$Match2.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                                {$Match2.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                              </a>
                            {elseif $statut2 == 'ON' && $validation2 != 'O'}
                              <button type="button" class="btn btn-warning btn-xs scoreProvisoire" title="{#scoreProvisoire#}">
                                {$Match2.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                                {$Match2.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                              </button>
                            {elseif $statut2 == 'END' && $validation2 != 'O'}
                              <button type="button" class="btn btn-warning btn-xs scoreProvisoire" title="{#scoreProvisoire#}">
                                {$Match2.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                                {$Match2.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                              </button>
                            {else}
                              <button type="button" class="btn btn-default btn-xs" title="{#ATT#}">
                                {#ATT#}
                              </button>
                            {/if}
                          </span>
                        </td>
                        <td class="text-center" data-filter="{$Match2.EquipeB|default:'&nbsp;'}">
                          <a class="btn btn-xs btn-default" {if $Match2.NumB > 0}
                              href="kpequipes.php?Equipe={$Match2.NumB}&Compet={$Match2.Code_competition}&Css={$Css}"
                            title="{#Palmares#}" {/if}>
                            {$Match2.EquipeB|default:'&nbsp;'}
                          </a>
                        </td>
                      {else}
                        <td colspan="6" class="pause">{#Pause#}</td>
                      {/if}
                    </tr>
                    {sectionelse}
                    <tr>
                      <td colspan=13 align=center><i>{#Aucun_match#}</i></td>
                    </tr>
                  {/section}
                </tbody>
              </table>
            </article>
          {/if}
          {if $nbTerrains > 6}
            {assign var='terrainG' value=$numTerrain[6]}
            {assign var='terrainD' value=$numTerrain[7]}
            <article class="table-responsive col-md-12 padTopBottom">
              <table class='tableau table table-striped table-condensed table-responsive table-hover display compact'>
                <thead>
                  <tr class="text-center">
                    <th colspan="6" class="text-center bg-primary text-white" width="48%">{$arrayDates[i].date} - {#Terrain#}
                      {$terrainG}</th>
                    <th rowspan="2" class="text-center" width="4%">{#Heure#}</th>
                    <th colspan="6" class="text-center bg-primary text-white" width="48%">{$arrayDates[i].date} - {#Terrain#}
                      {$terrainD}</th>
                  </tr>
                  <tr>
                    <th>#</th>
                    <th>{#Cat#}</th>
                    <th>{#Poules#}</th>
                    <th class="cliquableNomEquipe">{#Equipe_A#}</th>
                    <th class="cliquableScore">{#Score#}</th>
                    <th class="cliquableNomEquipe">{#Equipe_B#}</th>

                    <th>#</th>
                    <th>{#Cat#}</th>
                    <th>{#Poules#}</th>
                    <th class="cliquableNomEquipe">{#Equipe_A#}</th>
                    <th class="cliquableScore">{#Score#}</th>
                    <th class="cliquableNomEquipe">{#Equipe_B#}</th>
                  </tr>
                </thead>
                <tbody>
                  {section name=j loop=$arrayHeures[$Date]}
                    {assign var='Heure' value=$arrayHeures[$Date][j].heure}
                    {assign var='Match1' value=$arrayMatchs[$Date][$Heure][$terrainG]}
                    {assign var='validation1' value=$Match1.Validation}
                    {assign var='statut1' value=$Match1.Statut}
                    {assign var='periode1' value=$Match1.Periode}
                    {assign var='Match2' value=$arrayMatchs[$Date][$Heure][$terrainD]}
                    {assign var='validation2' value=$Match2.Validation}
                    {assign var='statut2' value=$Match2.Statut}
                    {assign var='periode2' value=$Match2.Periode}

                    <tr class='{$Match1.past}'>
                      {if $Match1.Numero_ordre}
                        <td>{$Match1.Numero_ordre}</td>
                        <td class="cat" data-cat="{$Match1.Categorie}">{$Match1.Categorie}</td>
                        <td>{$Match1.Phase|default:'&nbsp;'}</td>
                        <td class="text-center" data-filter="{$Match1.EquipeA|default:'&nbsp;'}">
                          <a class="btn btn-xs btn-default" {if $Match1.NumA > 0}
                              href="kpequipes.php?Equipe={$Match1.NumA}&Compet={$Match1.Code_competition}&Css={$Css}"
                            title="{#Palmares#}" {/if}>
                            {$Match1.EquipeA|default:'&nbsp;'}
                          </a>
                        </td>
                        <td class="text-center"><span title="{$Match1.Id}">
                            {if $validation1 == 'O' && $Match1.ScoreA != '?' && $Match1.ScoreA != '' && $Match1.ScoreB != '?' && $Match1.ScoreB != ''}
                              <a class="btn btn-success btn-xs" href="PdfMatchMulti.php?listMatch={$Match1.Id}" target="_blank"
                                title="{#END#} - {#Feuille_marque#}">
                                {$Match1.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                                {$Match1.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                              </a>
                            {elseif $statut1 == 'ON' && $validation1 != 'O'}
                              <button type="button" class="btn btn-warning btn-xs scoreProvisoire" title="{#scoreProvisoire#}">
                                {$Match1.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                                {$Match1.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                              </button>
                            {elseif $statut1 == 'END' && $validation1 != 'O'}
                              <button type="button" class="btn btn-warning btn-xs scoreProvisoire" title="{#scoreProvisoire#}">
                                {$Match1.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                                {$Match1.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                              </button>
                            {else}
                              <button type="button" class="btn btn-default btn-xs" title="{#ATT#}">
                                {#ATT#}
                              </button>
                            {/if}
                          </span>
                        </td>
                        <td class="text-center" data-filter="{$Match1.EquipeB|default:'&nbsp;'}">
                          <a class="btn btn-xs btn-default" {if $Match1.NumB > 0}
                              href="kpequipes.php?Equipe={$Match1.NumB}&Compet={$Match1.Code_competition}&Css={$Css}"
                            title="{#Palmares#}" {/if}>
                            {$Match1.EquipeB|default:'&nbsp;'}
                          </a>
                        </td>
                      {else}
                        <td colspan="6" class="pause">{#Pause#}</td>
                      {/if}

                      <td class="text-center" data-order="{$Heure}" data-filter="{$Heure}">
                        <span class="badge">{$Heure}</span>
                      </td>

                      {if $Match2.Numero_ordre}
                        <td>{$Match2.Numero_ordre}</td>
                        <td class="cat" data-cat="{$Match2.Categorie}">{$Match2.Categorie}</td>
                        <td>{$Match2.Phase|default:'&nbsp;'}</td>
                        <td class="text-center" data-filter="{$Match2.EquipeA|default:'&nbsp;'}">
                          <a class="btn btn-xs btn-default" {if $Match2.NumA > 0}
                              href="kpequipes.php?Equipe={$Match2.NumA}&Compet={$Match2.Code_competition}&Css={$Css}"
                            title="{#Palmares#}" {/if}>
                            {$Match2.EquipeA|default:'&nbsp;'}
                          </a>
                        </td>
                        <td class="text-center"><span title="{$Match2.Id}">
                            {if $validation2 == 'O' && $Match2.ScoreA != '?' && $Match2.ScoreA != '' && $Match2.ScoreB != '?' && $Match2.ScoreB != ''}
                              <a class="btn btn-success btn-xs" href="PdfMatchMulti.php?listMatch={$Match2.Id}" target="_blank"
                                title="{#END#} - {#Feuille_marque#}">
                                {$Match2.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                                {$Match2.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                              </a>
                            {elseif $statut2 == 'ON' && $validation2 != 'O'}
                              <button type="button" class="btn btn-warning btn-xs scoreProvisoire" title="{#scoreProvisoire#}">
                                {$Match2.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                                {$Match2.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                              </button>
                            {elseif $statut2 == 'END' && $validation2 != 'O'}
                              <button type="button" class="btn btn-warning btn-xs scoreProvisoire" title="{#scoreProvisoire#}">
                                {$Match2.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                                {$Match2.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                              </button>
                            {else}
                              <button type="button" class="btn btn-default btn-xs" title="{#ATT#}">
                                {#ATT#}
                              </button>
                            {/if}
                          </span>
                        </td>
                        <td class="text-center" data-filter="{$Match2.EquipeB|default:'&nbsp;'}">
                          <a class="btn btn-xs btn-default" {if $Match2.NumB > 0}
                              href="kpequipes.php?Equipe={$Match2.NumB}&Compet={$Match2.Code_competition}&Css={$Css}"
                            title="{#Palmares#}" {/if}>
                            {$Match2.EquipeB|default:'&nbsp;'}
                          </a>
                        </td>
                      {else}
                        <td colspan="6" class="pause">{#Pause#}</td>
                      {/if}
                    </tr>
                    {sectionelse}
                    <tr>
                      <td colspan=13 align=center><i>{#Aucun_match#}</i></td>
                    </tr>
                  {/section}
                </tbody>
              </table>
            </article>
          {/if}
        </div>
      {/if}
    </div>
    {sectionelse}
    <div class="container-fluid" id="containor">
      <article class="table-responsive col-md-12 padTopBottom">
        {#Aucun_match#}
      </article>
    </div>
  {/section}
  {if $voie}
    <script type="text/javascript" src="js/axios/axios.min.js?v={$NUM_VERSION}"></script>
    <script type="text/javascript" src="/js/voie.js?v={$NUM_VERSION}"></script>
    <script type="text/javascript">
      SetVoie({$voie});
    </script>
{/if}