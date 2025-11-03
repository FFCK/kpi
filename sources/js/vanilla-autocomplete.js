/**
 * Vanilla JS Autocomplete Component
 *
 * Remplacement moderne de jquery.autocomplete.js sans dépendance jQuery
 * API compatible pour migration transparente
 *
 * @author Laurent Garrigue / Claude Code
 * @version 1.0
 * @date novembre 2025
 */

(function(window) {
    'use strict';

    /**
     * Debounce function pour limiter les appels API
     * @param {Function} func - Fonction à debouncer
     * @param {number} wait - Délai en ms
     * @returns {Function}
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Classe VanillaAutocomplete
     */
    class VanillaAutocomplete {
        constructor(input, options) {
            // Éléments DOM
            this.input = typeof input === 'string' ? document.querySelector(input) : input;
            if (!this.input) {
                console.error('VanillaAutocomplete: Input element not found');
                return;
            }

            // Options par défaut
            this.options = Object.assign({
                url: '',                    // URL API (requis)
                minChars: 2,                // Caractères min avant recherche
                maxResults: 50,             // Nombre max de résultats
                delay: 300,                 // Délai debounce (ms)
                width: null,                // Largeur dropdown (null = largeur input)
                matchSubset: true,          // Chercher dans sous-chaînes
                cacheLength: 10,            // Nombre de requêtes en cache
                dataType: 'text',           // Type réponse: 'text' (legacy) ou 'json'
                formatItem: (item) => item, // Formatter résultat
                formatMatch: (item) => item,// Formatter pour matching
                formatResult: (item) => item, // Formatter pour valeur finale
                extraParams: {},            // Paramètres additionnels
                onSelect: null,             // Callback sélection
                onItemSelect: null,         // Alias callback (compat jQuery)
                autoFill: false,            // Remplissage auto premier résultat
                selectFirst: false,         // Sélectionner premier résultat
                scroll: true,               // Scroll dans résultats
                scrollHeight: 300           // Hauteur max dropdown
            }, options);

            // État interne
            this.cache = new Map();
            this.lastValue = '';
            this.selectedIndex = -1;
            this.results = [];
            this.isVisible = false;
            this.abortController = null;
            this.isSelecting = false; // Flag pour ignorer événement input pendant sélection

            // Créer éléments UI
            this.createUI();

            // Bind events
            this.bindEvents();
        }

        /**
         * Créer les éléments UI du dropdown
         */
        createUI() {
            // Container dropdown
            this.dropdown = document.createElement('div');
            this.dropdown.className = 'vanilla-autocomplete-dropdown';
            this.dropdown.style.cssText = `
                position: fixed;
                display: none;
                background: white;
                border: 1px solid #ccc;
                border-top: none;
                max-height: ${this.options.scrollHeight}px;
                overflow-y: auto;
                z-index: 9999;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            `;

            // Liste résultats
            this.resultsList = document.createElement('ul');
            this.resultsList.className = 'vanilla-autocomplete-results';
            this.resultsList.style.cssText = `
                list-style: none;
                margin: 0;
                padding: 0;
            `;

            this.dropdown.appendChild(this.resultsList);

            // Attacher au body (évite problèmes de positionnement)
            document.body.appendChild(this.dropdown);

            // Position et largeur
            this.updateDropdownPosition();
        }

        /**
         * Mettre à jour position dropdown
         */
        updateDropdownPosition() {
            const rect = this.input.getBoundingClientRect();
            const width = this.options.width || rect.width;

            this.dropdown.style.width = width + 'px';
            this.dropdown.style.left = rect.left + 'px';
            this.dropdown.style.top = rect.bottom + 'px';
        }

        /**
         * Bind événements
         */
        bindEvents() {
            // Input events
            this.input.addEventListener('input', debounce((e) => {
                this.handleInput(e);
            }, this.options.delay));

            this.input.addEventListener('focus', () => {
                if (this.input.value.length >= this.options.minChars) {
                    this.search(this.input.value);
                }
            });

            this.input.addEventListener('blur', () => {
                // Délai pour permettre le clic sur résultat
                setTimeout(() => this.hide(), 200);
            });

            // Keyboard navigation
            this.input.addEventListener('keydown', (e) => {
                this.handleKeydown(e);
            });

            // Window resize
            window.addEventListener('resize', debounce(() => {
                if (this.isVisible) {
                    this.updateDropdownPosition();
                }
            }, 100));

            // Document click (fermer si clic extérieur)
            document.addEventListener('click', (e) => {
                if (!this.input.contains(e.target) && !this.dropdown.contains(e.target)) {
                    this.hide();
                }
            });
        }

        /**
         * Gérer input
         */
        handleInput(e) {
            // Ignorer événement si on est en train de sélectionner un item
            if (this.isSelecting) {
                this.isSelecting = false;
                return;
            }

            const value = this.input.value;

            if (value.length < this.options.minChars) {
                this.hide();
                return;
            }

            if (value === this.lastValue) {
                return;
            }

            this.lastValue = value;
            this.search(value);
        }

        /**
         * Gérer navigation clavier
         */
        handleKeydown(e) {
            if (!this.isVisible) return;

            switch(e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    this.selectNext();
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    this.selectPrevious();
                    break;
                case 'Enter':
                    e.preventDefault();
                    if (this.selectedIndex >= 0) {
                        this.selectItem(this.results[this.selectedIndex], this.selectedIndex);
                    }
                    break;
                case 'Escape':
                    this.hide();
                    break;
                case 'Tab':
                    if (this.options.selectFirst && this.results.length > 0) {
                        this.selectItem(this.results[0], 0);
                    }
                    this.hide();
                    break;
            }
        }

        /**
         * Recherche via API
         */
        async search(query) {
            // Vérifier cache
            const cacheKey = query.toLowerCase();
            if (this.cache.has(cacheKey)) {
                this.displayResults(this.cache.get(cacheKey));
                return;
            }

            // Annuler requête précédente
            if (this.abortController) {
                this.abortController.abort();
            }

            this.abortController = new AbortController();

            try {
                // Construire URL avec paramètres (base = URL courante pour chemins relatifs)
                const url = new URL(this.options.url, window.location.href);
                url.searchParams.append('q', query);
                url.searchParams.append('limit', this.options.maxResults);

                // Format JSON si spécifié
                if (this.options.dataType === 'json') {
                    url.searchParams.append('format', 'json');
                }

                // Paramètres additionnels
                Object.keys(this.options.extraParams).forEach(key => {
                    url.searchParams.append(key, this.options.extraParams[key]);
                });

                // Fetch API
                const response = await fetch(url.toString(), {
                    method: 'GET',
                    signal: this.abortController.signal,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                let results;

                if (this.options.dataType === 'json') {
                    // Format JSON moderne
                    const data = await response.json();
                    results = Array.isArray(data) ? data : [];
                } else {
                    // Format texte legacy (line1\nline2\nline3)
                    const data = await response.text();
                    results = data.split('\n').filter(line => line.trim());
                }

                // Mettre en cache
                if (this.cache.size >= this.options.cacheLength) {
                    // Supprimer première entrée (FIFO)
                    const firstKey = this.cache.keys().next().value;
                    this.cache.delete(firstKey);
                }
                this.cache.set(cacheKey, results);

                this.displayResults(results);

            } catch (error) {
                if (error.name === 'AbortError') {
                    // Requête annulée, ignorer
                    return;
                }
                console.error('Autocomplete search error:', error);
                this.hide();
            }
        }

        /**
         * Afficher résultats
         */
        displayResults(results) {
            this.results = results;
            this.selectedIndex = -1;

            // Vider liste
            this.resultsList.innerHTML = '';

            if (results.length === 0) {
                this.hide();
                return;
            }

            // Créer items
            results.forEach((item, index) => {
                const li = document.createElement('li');
                li.className = 'vanilla-autocomplete-item';
                li.style.cssText = `
                    padding: 8px 12px;
                    cursor: pointer;
                    border-bottom: 1px solid #f0f0f0;
                `;

                // Formatter item
                const formatted = this.options.formatItem(item, index, results.length);
                li.innerHTML = formatted;

                // Données
                li.dataset.index = index;
                li.dataset.value = item;

                // Events
                li.addEventListener('mouseenter', () => {
                    this.highlightItem(index);
                });

                li.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.selectItem(item, index);
                });

                this.resultsList.appendChild(li);
            });

            // Auto-sélectionner premier si option activée
            if (this.options.selectFirst) {
                this.highlightItem(0);
            }

            this.show();
        }

        /**
         * Sélectionner item suivant
         */
        selectNext() {
            if (this.selectedIndex < this.results.length - 1) {
                this.highlightItem(this.selectedIndex + 1);
            }
        }

        /**
         * Sélectionner item précédent
         */
        selectPrevious() {
            if (this.selectedIndex > 0) {
                this.highlightItem(this.selectedIndex - 1);
            }
        }

        /**
         * Highlight item
         */
        highlightItem(index) {
            // Retirer highlight précédent
            const items = this.resultsList.querySelectorAll('li');
            items.forEach(item => {
                item.style.backgroundColor = '';
                item.style.color = '';
            });

            // Highlight nouvel item
            if (index >= 0 && index < items.length) {
                this.selectedIndex = index;
                items[index].style.backgroundColor = '#f0f0f0';
                items[index].style.color = '#000';

                // Scroll si nécessaire
                if (this.options.scroll) {
                    items[index].scrollIntoView({ block: 'nearest' });
                }

                // Auto-fill si option activée
                if (this.options.autoFill) {
                    this.input.value = this.options.formatResult(this.results[index]);
                }
            }
        }

        /**
         * Sélectionner item
         */
        selectItem(item, index) {
            // Flag pour ignorer prochain événement input
            this.isSelecting = true;

            // Formatter résultat
            const value = this.options.formatResult(item);
            this.input.value = value;
            this.lastValue = value;

            // Callbacks
            if (this.options.onSelect) {
                this.options.onSelect.call(this.input, item, index);
            }
            if (this.options.onItemSelect) {
                this.options.onItemSelect.call(this.input, { data: [item] });
            }

            // Trigger change event
            const event = new Event('change', { bubbles: true });
            this.input.dispatchEvent(event);

            this.hide();
        }

        /**
         * Afficher dropdown
         */
        show() {
            this.updateDropdownPosition();
            this.dropdown.style.display = 'block';
            this.isVisible = true;
        }

        /**
         * Cacher dropdown
         */
        hide() {
            this.dropdown.style.display = 'none';
            this.isVisible = false;
            this.selectedIndex = -1;
        }

        /**
         * Détruire instance
         */
        destroy() {
            if (this.dropdown && this.dropdown.parentNode) {
                this.dropdown.parentNode.removeChild(this.dropdown);
            }
            this.cache.clear();
            if (this.abortController) {
                this.abortController.abort();
            }
        }
    }

    /**
     * Factory function (API compatible jQuery autocomplete)
     *
     * Usage:
     *   vanillaAutocomplete('#input', { url: 'api.php', onSelect: fn })
     *   vanillaAutocomplete(element, 'api.php', { onSelect: fn })
     */
    window.vanillaAutocomplete = function(input, urlOrOptions, options) {
        // Support différents formats d'appel
        let finalOptions;

        if (typeof urlOrOptions === 'string') {
            // vanillaAutocomplete(input, 'url', {options})
            finalOptions = Object.assign({ url: urlOrOptions }, options || {});
        } else {
            // vanillaAutocomplete(input, {options})
            finalOptions = urlOrOptions || {};
        }

        return new VanillaAutocomplete(input, finalOptions);
    };

    // Export pour modules
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = VanillaAutocomplete;
    }

})(window);
