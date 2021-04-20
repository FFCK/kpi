SELECT concat('<option value="', a.Id, '">', a.Id, ' : ', d.Libelle, ' - ', e.Libelle,'</option>')
FROM kp_match a
Left outer join kp_competition_equipe d On (d.Id = a.Id_equipeA)
Left outer join kp_competition_equipe e On (e.Id = a.Id_equipeB)
, kp_journee b, kp_evenement_journee c
WHERE a.Id_journee = b.Id 
And b.Id = c.Id_journee 
And c.Id_evenement = 85 
And a.Date_match = '2017-08-26' 
Order By a.Heure_match, a.Terrain


SELECT a.* FROM kp_match a, kp_journee b, kp_evenement_journee c 
WHERE a.Id_journee = b.Id 
And b.Id = c.Id_journee 
And c.Id_evenement = 85 
And a.Date_match = '2017-08-24' 
Order By a.Heure_match, a.Terrain


