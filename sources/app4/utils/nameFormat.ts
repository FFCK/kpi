export const formatNom = (nom: string): string => nom.toUpperCase()

export const formatPrenom = (prenom: string): string =>
  prenom.replace(/\S+/g, w => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase())
