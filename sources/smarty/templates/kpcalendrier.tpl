<div class="container titre">
    <div class="col-md-9">
        <h1 class="col-md-11 col-xs-9">{#Calendrier_des_competitions#}</h1>
    </div>
    <div class="col-md-3">
        <span class="badge pull-right">{$smarty.config.Saison|default:'Saison'} {$Saison}</span>
    </div>
</div>

<div class="container-fluid">
    <article class="col-md-12 padTopBottom">        
		<div id='calendar_{$lang}' class='fc'></div>
    </article>
</div>
		
		