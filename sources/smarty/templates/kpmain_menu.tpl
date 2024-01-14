{* main_menu.tpl Smarty *}

<div class="col-xs-12">
  <nav class="site-navigation navbar navbar-expand-md navbar-mv-up" role="navigation">
    <div class="menu-short-container container-fluid">
      <!--    Brand and toggle get grouped for better mobile di…    -->
      {* <a class="navbar-brand" href="#">KPI</a> *}
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <!--    Collect the nav links, forms, and other content f…  -->
      <div id="navbarSupportedContent" class="collapse navbar-collapse">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          {section name=i loop=$arraymenu}
            {assign var='temporaire' value=$arraymenu[i].name}
            <li class="nav-item">
              {if $temporaire == 'Accueil' && $lang == 'en'}
                <a href="./?lang=en" class="nav-link">{$smarty.config.$temporaire|default:$temporaire}</a>
              {elseif $temporaire == 'Accueil' && $lang == 'fr'}
                <a href="./?lang=fr" class="nav-link">{$smarty.config.$temporaire|default:$temporaire}</a>
              {else}
                <a href="{$arraymenu[i].href}" class="nav-link {if $currentmenu eq $arraymenu[i].name} active{/if}">{$smarty.config.$temporaire|default:$temporaire}</a>
              {/if}
            </li>
          {/section}
          {if $bPublic}
            <li class="nav-item"><a href="?lang=fr" class="nav-link"><img
                  width="22" src="img/Pays/FRA.png" alt="FR" title="FR" /></a></li>
            <li class="nav-item"><a href="?lang=en" class="nav-link"><img
                  width="22" src="img/Pays/GBR.png" alt="EN" title="EN" /></a></li>
          {else}
            <li class="nav-item"><a href="?lang=fr" class="nav-link"><img
                  width="22" src="../img/Pays/FRA.png" alt="FR" title="FR" /></a></li>
            <li class="nav-item"><a href="?lang=en" class="nav-link"><img
                  width="22" src="../img/Pays/GBR.png" alt="EN" title="EN" /></a></li>
          {/if}
        </ul>
      </div>
    </div>
  </nav>
</div>