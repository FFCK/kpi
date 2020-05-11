    <div class="container-fluid" id="selector">
    <article id="titre" class="container-fluid">
        <h1>{#Controle_tv#} - Scenario</h1>
        {if $AlertMessage}
            <div class="alert alert-warning alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                {$AlertMessage}
            </div>
        {/if}
        <form id="scenario_form" name="scenario_form" method="post" action="">
            <div class="row">
                <div class='col-md-1'>
                    <label>Scenario</label>
                    <select id="scenario" name="scenario">
                        {section name=i start=100 loop=1000 step=100}
                            <option value="{$smarty.section.i.index}" {if $smarty.section.i.index == $scenario}selected{/if}>{$smarty.section.i.index}</option>
                        {/section}
                    </select>
                    <br>
                    <br>
                    <a class="btn btn-default col-md-12" href="?scenario={$scenario}">Refresh</a>

                </div>
                <div class='col-md-11'>
                    <table class="table table-light">
                        <thead>
                            <tr>
                                <th width="5%">Channel</th>
                                <th width="85%">Url</th>
                                <th width="10%">Delay (ms)</th>
                            </tr>
                        </thead>
                        <tbody>
                            {section name=i loop=$arrayScenes}
                                <tr>
                                    <td>
                                        {$arrayScenes[i].Voie}
                                        <input type="hidden" name="Voie-{$smarty.section.i.iteration}" value="{$arrayScenes[i].Voie}">
                                    </td>
                                    <td>
                                        <input class="form-control" type="text" name="Url-{$smarty.section.i.iteration}" value="{$arrayScenes[i].Url}">
                                    </td>
                                    <td>
                                        <input class="form-control" type="text" name="intervalle-{$smarty.section.i.iteration}" value="{$arrayScenes[i].intervalle}">
                                    </td>
                                </tr>
                            {/section}
                        </tbody>
                    </table>
                    
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 col-md-offset-3">
                    <br>
                    <input class="form-control" type="submit" value="Update">
                    <input type="hidden" name="update" value="Update">
                    <br>
                    <br>
                </div>
                <div class="col-md-3">
                    <br>
                    <a class="btn btn-default col-md-12" href="live/tv2.php?voie={$scenario}&intervalle=2000" target="_blank">Test</a>
                    <br>
                    <br>
                </div>
            </div>
        </form>
    </article>
</div>
