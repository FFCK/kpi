<div class="container titre">
    <div class="col-md-9">
        <h1 class="col-md-11 col-xs-9">Clubs</h1>
    </div>
</div>
<div class="container" id="selector">
    <article class="col-md-12 padTopBottom">
        <div class="col-md-10 col-md-offset-1">
                <br>
                <br>
                <br>
                <br>
            {section name=i loop=$arrayLogos}
                <div class="col-md-1 col-sm-2 col-xs-3">
                    <a href="https://www.kayak-polo.info/kpclubs.php?clubId={$arrayLogos[i]}">
                        <img class="img2" src="img/KIP/logo/{$arrayLogos[i]}-logo.png" alt="{$arrayLogos[i]}" />
                    </a>
                </div>
            {/section}
            <div class="col-md-12 col-sm-12 col-xs-12">
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
            </div>
        </div>
    </article>

</div>

