# App4 — Matrice des droits par profil

## Profils utilisateurs

| Niveau | Nom |
|--------|-----|
| **1** | Super Admin |
| **2** | Bureau CNAKP |
| **3** | Resp. Division |
| **4** | Resp. Poule/Compétition |
| **5** | Délégué fédéral |
| **6** | Organisateur journée |
| **7** | Resp. club/équipe |
| **8** | Consultation simple |
| **9** | Table de marque |
| **10** | (Inutilisé) |

---

## Légende

- **C** = Consultation
- **M** = Création / Modification
- **S** = Suppression
- ✅ = autorisé | ❌ = interdit | — = non applicable

---

## Tableau des droits

| Page / Fonctionnalité | Action | 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8 | 9 | 10 |
|---|---|---|---|---|---|---|---|---|---|---|---|
| **Compétitions** | C | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| | M créer | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| | M modifier | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| | Publier/dépublier | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| | Verrouiller | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| | S (supprimer) | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| | Copier une compétition | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| | Modifier le code (importé) | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Équipes** | C | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| | M (modifier inline) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| | Formulaire couleurs / logo | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| | Opérations spéciales (copie compo…) | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Journées** | C | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| | M (créer/modifier journée) | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| | Sélection journée (filtre) | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| | Associer événements | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| | Modifier code/niveau (champ restreint) | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Matchs** | C | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| | M (modifier match/horaire) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| | Saisir scores | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| | Verrouiller scores | ✅ | ✅ | ✅ | ✅ | V | V | ❌ | ❌ | ❌ | ❌ |
| | Feuille de marque (standard) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| | Feuille de marque (avancée) | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Classements** | C (classement calculé) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| | C (classement publié) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| | Calculer classement | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| | M (modifier inline) | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| | Publier classement | ✅ | ✅ | ✅ | ✅ | V | V | ❌ | ❌ | ❌ | ❌ |
| | Dépublier classement | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| | Consolider / Transférer | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| | Changer type/statut | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| | Classements initiaux | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Statistiques** | C (types standard) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| | C (types restreints) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Athlètes** | C | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| | M (modifier données) | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Feuille de présence (équipe)** | C | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| | M/S (si non verrouillée) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | X | ❌ | ❌ |
| | Copier depuis autre compétition | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| | Créer un joueur | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| | Rechercher par licence | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Feuille de présence (match)** | C | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| | M/S (si non verrouillée) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| | Copier vers matchs de la journée | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| | Copier vers compétition | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Documents** | C (section Contrôle) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| | C (section Événement) | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Clubs** | C | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| | M/S | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Utilisateurs** | C (accessible) | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| | M (créer/modifier) | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| | S (supprimer) | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **RC (Responsables de Compétition)** | C | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| | M/Copier/S | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Groupes** | C/M/S | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Événements** | C/M/S | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Journal** | C | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **TV (configuration scoreboard)** | C/M | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Opérations** | (saisonnières/import-export) | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |

---

## Notes importantes

- Les droits s'appliquent dans le **périmètre filtré** de l'utilisateur (saisons, compétitions, clubs) — un profil 6 ne voit/modifie que les journées qui lui sont assignées.
- Le système de **mandats** permet d'élever ou restreindre temporairement le profil effectif.
- Les actions M/S sont bloquées si la ressource est **verrouillée**, indépendamment du profil.
- Le profil **9 (Table de marque)** a un accès très ciblé : saisie des scores et feuilles de présence match uniquement.
- **Bypass périmètre profils 1 et 2** (voir section suivante) : les filtres de périmètre sont ignorés pour les utilisateurs dont le niveau effectif est ≤ 2.

---

## Bypass périmètre pour profils 1 et 2

### Règle

Pour un utilisateur dont le **niveau effectif** est ≤ 2, tous les filtres de périmètre stockés en base sont **ignorés** :

- `Filtre_saison` (saisons autorisées)
- `Filtre_competition` (compétitions autorisées)
- `Id_Evenement` (événements autorisés)
- `Filtre_journee` (journées autorisées)
- `Limit_clubs` (clubs autorisés)

Ces utilisateurs voient et manipulent **tous** les événements, compétitions, saisons, journées et clubs du système, indépendamment des filtres renseignés sur leur profil.

### Niveau effectif vs niveau de base

Le bypass s'applique au **niveau effectif**, c'est-à-dire :

- Si l'utilisateur a un **mandat actif**, le niveau du mandat (`mandateNiveau`) s'applique.
- Sinon, le niveau de base (`niveau`) s'applique.

**Conséquence concrète** :

| Situation | Niveau de base | Mandat actif | Niveau effectif | Bypass appliqué ? |
|-----------|---------------|--------------|----------------|-------------------|
| Super Admin sans mandat | 1 | — | 1 | ✅ Oui |
| Super Admin avec mandat profil 5 | 1 | niveau 5 | 5 | ❌ Non — filtres du mandat appliqués |
| Bureau CNAKP sans mandat | 2 | — | 2 | ✅ Oui |
| Resp. Division avec mandat profil 2 | 3 | niveau 2 | 2 | ✅ Oui — filtres du mandat ignorés |
| Resp. Division sans mandat | 3 | — | 3 | ❌ Non — filtres de base appliqués |

Cette logique permet à un super-admin de « simuler » temporairement un profil inférieur (pour tester les droits, aider un utilisateur, etc.) en endossant un mandat.

### Pourquoi ce bypass ?

Les profils 1 et 2 sont des rôles d'administration système qui peuvent avoir des filtres de périmètre renseignés en base (par exemple hérités d'une création historique ou d'une migration). Ces filtres n'ont pas vocation à restreindre leur vision : ces profils doivent pouvoir intervenir sur n'importe quelle compétition/événement/journée en cas de besoin (correction de données, support utilisateur, administration).

### Implémentation technique

Le bypass est centralisé dans l'entité `User` (`sources/api2/src/Entity/User.php`), dans les 5 méthodes d'accès aux filtres :

- `getAllowedSeasons(): ?array`
- `getAllowedCompetitions(): ?array`
- `getAllowedEvents(): ?array`
- `getAllowedJournees(): ?array`
- `getAllowedClubs(): ?array`

Chacune retourne `null` (= aucune restriction) lorsque `getEffectiveNiveau() <= 2`. Tous les contrôleurs API qui consomment ces méthodes héritent automatiquement du bypass, sans modification supplémentaire.

```php
// Exemple pour getAllowedSeasons
public function getAllowedSeasons(): ?array
{
    if ($this->getEffectiveNiveau() <= 2) {
        return null;  // bypass : aucune restriction
    }
    // ... logique existante (base ou mandat)
}
```

### Impact côté frontend

- **WorkContextSelector** : toutes les saisons, compétitions et événements sont listés pour les profils 1/2.
- **UserEditModal** : quand un admin profil 1/2 édite un utilisateur, les listes de compétitions/événements/saisons disponibles dans le formulaire sont **complètes**.
- **Pages filtrées par événement** (ex. `/events/{id}/gamedays`) : les profils 1/2 peuvent associer n'importe quelle journée, même hors de leur périmètre de base.

Aucun changement n'est requis côté frontend : le bypass backend est transparent car les endpoints `/admin/filters/*` renvoient déjà les listes complètes pour ces profils.
