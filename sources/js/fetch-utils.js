/**
 * fetch-utils.js
 *
 * Utilitaires pour remplacer Axios par fetch() natif
 * Compatible avec l'usage actuel d'Axios dans le projet KPI
 *
 * @author Laurent Garrigue / Claude Code
 * @date 2025-11-01
 */

/**
 * Wrapper fetch() compatible avec la syntaxe Axios utilisée dans le projet
 *
 * Supporte les mêmes paramètres que l'usage actuel d'Axios:
 * - method: 'GET', 'POST', etc.
 * - url: URL de la requête
 * - params: Paramètres query string (ignoré pour l'instant)
 * - responseType: 'json', 'text', 'blob'
 *
 * Retourne un objet { data } comme Axios pour compatibilité maximale
 *
 * @param {Object} config - Configuration de la requête
 * @param {string} config.method - Méthode HTTP (GET, POST, etc.)
 * @param {string} config.url - URL de la requête
 * @param {string} config.responseType - Type de réponse attendu ('json' par défaut)
 * @returns {Promise<{data: any}>} - Promise retournant { data } comme Axios
 *
 * @example
 * // Utilisation identique à Axios
 * axiosLikeFetch({
 *     method: 'post',
 *     url: './cache/match_score.json',
 *     responseType: 'json'
 * })
 * .then(function (response) {
 *     console.log(response.data)  // Données parsées
 * })
 * .catch(function (error) {
 *     console.log(error)
 * })
 */
function axiosLikeFetch(config) {
    const {
        method = 'GET',
        url,
        responseType = 'json'
    } = config

    return fetch(url, {
        method: method.toUpperCase()
    })
    .then(function(response) {
        // Vérifier le statut HTTP (comme Axios)
        if (!response.ok) {
            throw new Error('HTTP ' + response.status + ': ' + response.statusText)
        }

        // Parser la réponse selon le type demandé
        if (responseType === 'json') {
            return response.json()
        } else if (responseType === 'text') {
            return response.text()
        } else if (responseType === 'blob') {
            return response.blob()
        } else {
            return response.text()
        }
    })
    .then(function(data) {
        // Retourner dans le même format qu'Axios: { data }
        return { data: data }
    })
}

/**
 * Version async/await de axiosLikeFetch pour usage moderne
 *
 * @param {Object} config - Configuration de la requête (même format que axiosLikeFetch)
 * @returns {Promise<{data: any}>} - Promise retournant { data }
 *
 * @example
 * // Utilisation avec async/await
 * try {
 *     const response = await axiosLikeFetchAsync({
 *         method: 'post',
 *         url: './cache/voie.json',
 *         responseType: 'json'
 *     })
 *     console.log(response.data)
 * } catch (error) {
 *     console.error(error)
 * }
 */
async function axiosLikeFetchAsync(config) {
    const {
        method = 'GET',
        url,
        responseType = 'json'
    } = config

    const response = await fetch(url, {
        method: method.toUpperCase()
    })

    if (!response.ok) {
        throw new Error('HTTP ' + response.status + ': ' + response.statusText)
    }

    let data
    if (responseType === 'json') {
        data = await response.json()
    } else if (responseType === 'text') {
        data = await response.text()
    } else if (responseType === 'blob') {
        data = await response.blob()
    } else {
        data = await response.text()
    }

    return { data: data }
}

// Export pour modules ES6 (si besoin futur)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        axiosLikeFetch: axiosLikeFetch,
        axiosLikeFetchAsync: axiosLikeFetchAsync
    }
}
