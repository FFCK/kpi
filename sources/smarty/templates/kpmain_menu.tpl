{* main_menu.tpl Smarty *}

<div class="col-xs-12">
  <nav class="site-navigation navbar navbar-default navbar-mv-up" role="navigation">
    <div class="menu-short-container container-fluid">
      <!--    Brand and toggle get grouped for better mobile di…    -->
      <div class="navbar-header">
        <button class="navbar-toggle collapsed navbar-color-mod" data-target="#bs-example-navbar-collapse-1"
          data-toggle="collapse" type="button">
          <span class="sr-only">
            Toggle navigation
          </span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
      </div>
      <!--    Collect the nav links, forms, and other content f…  -->
      <div id="bs-example-navbar-collapse-1" class="collapse navbar-collapse">
        <div class="menu-nav1-container">
          <ul id="menu-nav1" class="site-menu">
            {section name=i loop=$arraymenu}
              {assign var='temporaire' value=$arraymenu[i].name}
              <li
                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-958{if $currentmenu eq $arraymenu[i].name} active{/if}">
                {if $temporaire == 'Accueil' && $lang == 'en'}
                  <a href="./?lang=en">{$smarty.config.$temporaire|default:$temporaire}</a>
                {elseif $temporaire == 'Accueil' && $lang == 'fr'}
                  <a href="./?lang=fr">{$smarty.config.$temporaire|default:$temporaire}</a>
                {else}
                  <a href="{$arraymenu[i].href}">{$smarty.config.$temporaire|default:$temporaire}</a>
                {/if}
              </li>
            {/section}
            {if $bPublic}
              <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-958"><a href="?lang=fr"><img
                    width="22" src="img/Pays/FRA.png" alt="FR" title="FR" /></a></li>
              <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-958"><a href="?lang=en"><img
                    width="22" src="img/Pays/GBR.png" alt="EN" title="EN" /></a></li>
            {else}
              <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-958"><a href="?lang=fr"><img
                    width="22" src="../img/Pays/FRA.png" alt="FR" title="FR" /></a></li>
              <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-958"><a href="?lang=en"><img
                    width="22" src="../img/Pays/GBR.png" alt="EN" title="EN" /></a></li>
            {/if}
          </ul>
        </div>
      </div>
    </div>
  </nav>
</div>