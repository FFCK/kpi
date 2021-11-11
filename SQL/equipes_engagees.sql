/**
* Equipes engagées en compétition nationale
*/
SELECT DISTINCT e.Libelle
FROM kp_equipe e 
LEFT JOIN kp_competition_equipe ce ON (e.Numero = ce.Numero)
WHERE (
    ce.Code_compet LIKE 'N%'
    OR ce.Code_compet LIKE 'CF%'
    )
AND ce.Code_saison = 2021

/**
* Equipes engagées en compétition nationale et régionale
*/
SELECT DISTINCT e.Libelle
FROM kp_equipe e 
LEFT JOIN kp_competition_equipe ce ON (e.Numero = ce.Numero)
WHERE (
    ce.Code_compet LIKE 'N%'
    OR ce.Code_compet LIKE 'CF%'
    OR ce.Code_compet LIKE 'REG%'
    OR ce.Code_compet LIKE 'T-R%'
    OR ce.Code_compet LIKE 'OPEN%'
    )
AND ce.Code_saison = 2021

