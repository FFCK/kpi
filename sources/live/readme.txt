Liste des URL utilisés à Saint-Omer 2017
----------------------------------------

1) event.php 
	http://www.kayak-polo.info/live/event.php
=> Permet de générer toutes les x secondes les fichiers de cache indiquant pour chaque terrain le match en cours 
	event@e_pitch1.json avec @e = n° Evenement = 85 : donc si 4 terrains
		/live/data/event85_pitch1.json
		/live/data/event85_pitch2.json
		/live/data/event85_pitch3.json
		/live/data/event85_pitch4.json
		
2) tv.php 
	http://www.kayak-polo.info/live/tv.php
=> Panneau de control qui permet d'envoyer sur les différentes voies les informations voulues 
=> https://www.kayak-polo.info/kptv.php

3) multi_score.php 
	http://www.kayak-polo.info/live/multi_score.php 			=> Affichage classique
	http://www.kayak-polo.info/live/multi_score.php?tv=1		=> Affichage Tv avec fond vert
	
	a) pour un terrain @t et l'évenement @e on utilise le fichier de cache /live/data/event@e_pitch@t.json pour déterminer le match @m en cours
	b) les 3 fichiers de cache sont alors lus : @m_match_global.json @m_match_score.json et @m_match_chrono.json
	
4) score.php 
	http://www.kayak-polo.info/live/score.php 					=> utilisé pour les finales (fond vert)
	a) pour un terrain @t donné on utilise le fichier de cache /live/data/@t_terrain.json pour déterminer le match @m en cours
	b) les 3 fichiers de cache sont alors lus : @m_match_global.json @m_match_score.json et @m_match_chrono.json
	
5) splitter.php
	permet de splitter par 1,2 ou 4 iframes 
	Exemple 1 : configuration dans tv.php (Scénario)
	50s : live/splitter.php?frame1=www.kayak-polo.info/frame_terrains.php|Q|Saison=2017|A|Group=CE|A|lang=en|A|Css=sainto_hd|A|filtreJour=2017-08-27	
	30s : live/splitter.php?frame1=www.kayak-polo.info/frame_phases|Q|Saison=2017|A|Group=CE|A|Compet=CEH21|A|Css=sainto|A|Round=4|A|lang=en&frame2=www.kayak-polo.info/frame_phases|Q|Saison=2017|A|Group=CE|A|Compet=CEF21|A|Css=sainto|A|Round=3
	30s : live/splitter.php?frame1=www.kayak-polo.info/frame_phases|Q|Saison=2017|A|Group=CE|A|Compet=CEH|A|Css=sainto|A|Round=3|A|lang=en&frame2=www.kayak-polo.info/frame_phases|Q|Saison=2017|A|Group=CE|A|Compet=CEF|A|Css=sainto|A|Round=3

	Exemple 2 : visualisation des 4 schémas sur 4 iframes
	http://www.kayak-polo.info/live/splitter.php?frame1=www.kayak-polo.info/img/schemas/schema_2017_CEH.png&frame2=www.kayak-polo.info/img/schemas/schema_2017_CEF.png&frame3=www.kayak-polo.info/img/schemas/schema_2017_CEH21.png&frame4=www.kayak-polo.info/img/schemas/schema_2017_CEF21.png
	
6) force_cache_match.php
	permet de forcer et de créer les 3 fichiers de cache pour un match donné 
	Exemple : http://www.kayak-polo.info/live/force_cache_match.php?match=79261771

7) schema.php
    Permet d'afficher 4 schemas sur une page. Non dynamique (liens vers les schémas dans /live/css/schema.css)

=> voir aussi /live/sql/url.txt et list_/live/sql/match.sql

	