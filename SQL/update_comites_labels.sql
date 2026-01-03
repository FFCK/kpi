-- Update des libellés dans les tables des comités
-- Remplace 'COMITE DEPARTEMENTAL' par 'CD' et 'CANOE KAYAK' par 'CK'

-- Table kp_cd : Comités Départementaux
UPDATE kp_cd 
SET Libelle = REPLACE(REPLACE(Libelle, 'COMITE DEPARTEMENTAL', 'CD'), 'CANOE KAYAK', 'CK')
WHERE Libelle LIKE '%COMITE DEPARTEMENTAL%' 
   OR Libelle LIKE '%CANOE KAYAK%';

-- Table kp_cr : Comités Régionaux
UPDATE kp_cr 
SET Libelle = REPLACE(REPLACE(Libelle, 'COMITE REGIONAL', 'CR'), 'CANOE KAYAK', 'CK')
WHERE Libelle LIKE '%COMITE REGIONAL%' 
   OR Libelle LIKE '%CANOE KAYAK%';
