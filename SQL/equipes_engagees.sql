/**
* Equipes engagées en compétition nationale
*/
SELECT DISTINCT e.Libelle
FROM gickp_Equipe e 
LEFT JOIN gickp_Competitions_Equipes ce ON (e.Numero = ce.Numero)
WHERE (
    ce.Code_compet LIKE 'N%'
    OR ce.Code_compet LIKE 'CF%'
    )
AND ce.Code_saison = 2020

/**
* Equipes engagées en compétition nationale et régionale
*/
SELECT DISTINCT e.Libelle
FROM gickp_Equipe e 
LEFT JOIN gickp_Competitions_Equipes ce ON (e.Numero = ce.Numero)
WHERE (
    ce.Code_compet LIKE 'N%'
    OR ce.Code_compet LIKE 'CF%'
    OR ce.Code_compet LIKE 'REG%'
    OR ce.Code_compet LIKE 'T-R%'
    )
AND ce.Code_saison = 2020

