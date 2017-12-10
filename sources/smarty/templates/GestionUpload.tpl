<ol class="breadcrumb container">
    <li><a href="GestionStructure.php">Clubs</a></li>
    <li class="active">Upload</li>
</ol>

<div class="container titre">
    <div class="col-md-12">
        <h1 class="col-md-11 col-xs-9">{#Uploads#}...Uploads...</h1>
    </div>
</div>

{if $profile == 1}
<div class="container" id="selector">
    <article class="col-md-12 padTopBottom">
        <div class="row col-sm-8" id="dropfile">Glissez une image depuis votre ordinateur</div>
        <img class='col-sm-4 responsive' id="img_actuelle" />
    </article>
    <article class="col-md-12 padTopBottom">
        <form action="" enctype="multipart/form-data" method="post">
            <div class="row">
                <div class="col-sm-3">
                    <select id="type">
                        <option value="logo">Logo de club</option>
                        <option value="team">Collectif</option>
                        <option value="colors">Couleurs d'équipe</option>
                    </select>
                </div>
                <div class="col-sm-3">
                    <select id='saison'>
                        <option value='2016'>2016</option>
                        <option value='2015'>2015</option>
                    </select>
                </div>
                <input class="col-sm-5" type='text' id='recherche1' placeholder='Recherche club' />
                <input class="col-sm-5" type='text' id='recherche2' placeholder='Recherche équipe' />
                <input class="col-sm-5" type='text' id='recherche3' placeholder='Recherche équipe' />
                <input class="col-sm-1" type='text' id='identifiant' value="0" />
            </div>
            <div class="row">
                <label for='upload'>Add Attachments:</label>
                <input id='upload' name="upload[]" type="file" multiple="multiple" />
            </div>
            <p><input type="submit" name="submit" value="Submit"></p>
        </form>
    </article>
</div>
{/if}
