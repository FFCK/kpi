SELECT concat('<option value="', a.Id, '">', a.Id, ' : ', d.Libelle, ' - ', e.Libelle,'</option>')
FROM gickp_Matchs a
Left outer join gickp_Competitions_Equipes d On (d.Id = a.Id_equipeA)
Left outer join gickp_Competitions_Equipes e On (e.Id = a.Id_equipeB)
, gickp_Journees b, gickp_Evenement_Journees c
WHERE a.Id_journee = b.Id 
And b.Id = c.Id_journee 
And c.Id_evenement = 85 
And a.Date_match = '2017-08-26' 
Order By a.Heure_match, a.Terrain


SELECT a.* FROM gickp_Matchs a, gickp_Journees b, gickp_Evenement_Journees c 
WHERE a.Id_journee = b.Id 
And b.Id = c.Id_journee 
And c.Id_evenement = 85 
And a.Date_match = '2017-08-24' 
Order By a.Heure_match, a.Terrain


