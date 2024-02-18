{* Sélection Event/Competition/Saison/Journée/Phase *}
<div class="container my-2">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <form class="row d-contents" method="POST" action="kpmatchs.php#containor" name="formJournee" id="formJournee" enctype="multipart/form-data">
                <input type='hidden' name='Cmd' Value=''/>
                <input type='hidden' name='ParamCmd' Value=''/>
                <input type='hidden' name='idEquipeA' Value=''/>
                <input type='hidden' name='idEquipeB' Value=''/>
                <input type='hidden' name='Pub' Value=''/>
                <input type='hidden' name='Verrou' Value=''/>
                
                <div class='col-md-2 col-sm-3 d-none d-sm-grid'>
                    <div class='form-floating'>
                        <select class="form-select form-select-sm" name="Saison" onChange="submit()" id="Saison">
                            {section name=i loop=$arraySaison} 
                                <option Value="{$arraySaison[i].Code}" {if $arraySaison[i].Code eq $Saison}selected{/if}>{$arraySaison[i].Code}</option>
                            {/section}
                        </select>
                        <label for="Saison" class="form-label">{#Saison#}</label>
                    </div>
                </div>
                <div class='col-md-3 col-sm-4 d-none d-sm-grid'>
                    <div class='form-floating'>
                        <select class="form-select form-select-sm" name="event" onChange="submit();" id="event">
                            <option value="0" {if $event == 0}selected{/if}>--- {#Aucun#} ---</option>
                            {section name=i loop=$arrayEvents}
                                <option value="{$arrayEvents[i].Id}" {if $event == $arrayEvents[i].Id}selected{/if}>{$arrayEvents[i].Libelle}</option>
                            {/section}
                        </select>
                        <label for="event" class="form-label">{#Evenement#}</label>
                    </div>
                </div>
                {if $event <= 0}
                    <div class='col-md-3 col-sm-5 d-none d-sm-grid'>
                        <div class='form-floating'>
                            <select class="form-select form-select-sm" name="Group" onChange="submit();" id="Group">
                            {section name=i loop=$arrayCompetitionGroupe}
                                {assign var='options' value=$arrayCompetitionGroupe[i].options}
                                {assign var='label' value=$arrayCompetitionGroupe[i].label}
                                <optgroup label="{$smarty.config.$label|default:$label}">
                                {section name=j loop=$options}
                                    {assign var='optionLabel' value=$options[j].Groupe}
                                    <option Value="{$options[j].Groupe}" {$options[j].selected}>{$smarty.config.$optionLabel|default:$options[j].Libelle}</option>
                                {/section}
                                </optgroup>
                            {/section}
                            </select>
                            <label for="Group" class="form-label">{#Competition#}</label>
                        </div>
                    </div>
                {else}
                    <div class='col-md-6 col-sm-2 d-none d-sm-grid'>
                        <div class='form-floating'>
                            <input type="text" readonly class="form-control-plaintext" value="">
                        </div>
                    </div>
                {/if}   
                {* <div class="visible-xs col-xs-11 bold" id="subtitle"><label></label></div>     *}
                {* <a class="visible-xs-block col-xs-1 pull-right" href="" id="selects_toggle">
                    <img class="img-responsive" src="img/glyphicon-triangle-bottom.png" width="16">
                </a> *}
                {if $event <= 0}
                    {if $arrayCompetition[0].Code_typeclt == 'CHPT'}
                        <div class='col-md-3 col-sm-9 d-none d-sm-grid'>
                            <div class='form-floating'>
                                <select class="form-select form-select-sm" name="J" onChange="submit();" id="J">
                                    <option Value="*" Selected>{#Toutes#}</option>
                                    {section name=i loop=$arrayListJournees}
                                        <option Value="{$arrayListJournees[i].Id}" {if $idSelJournee == $arrayListJournees[i].Id}Selected{/if}>
                                            {if $lang == 'en'}{$arrayListJournees[i].Date_debut_en}
                                            {else}{$arrayListJournees[i].Date_debut}
                                            {/if} - {$arrayListJournees[i].Lieu}
                                        </option>
                                    {/section}
                                </select>
                                <label for="J" class="form-label">{#Journee#}</label>
                            </div>
                        </div>
                    {else}
                        <div class='col-md-3 col-sm-9 d-none d-sm-grid'></div>
                    {/if}
                {/if}
                <div class='col-md-1 col-sm-3 d-none d-sm-grid text-end'>
                    <div id="fb-root"></div>
                    <div class="fb-like" data-href="https://www.kayak-polo.info/kpmatchs.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}" 
                        data-layout="button" data-action="recommend" data-show-faces="false" data-share="true"></div>
                    <br>
                    <a class="pdfLink btn btn-default" href="PdfListeMatchs{if $lang=='en'}EN{/if}.php?S={$Saison}&idEvenement={$event}&Group={$codeCompetGroup}&Compet={$codeCompet}&Journee={$idSelJournee}" Target="_blank"><img width="20" src="img/pdf.gif" alt="{#Matchs#} (pdf)" title="{#Matchs#} (pdf)" /></a>
                </div>
            </form>
        </nav>
    </div>
</div>