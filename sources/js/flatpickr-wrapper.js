/**
 * Wrapper function pour compatibilité dhtmlgoodies_calendar → Flatpickr
 *
 * Usage (identique à dhtmlgoodies):
 *   displayCalendar(inputField, 'dd/mm/yyyy', this)
 *
 * @param {HTMLInputElement} inputField - Champ input à transformer en datepicker
 * @param {string} formatString - Format de date ('dd/mm/yyyy' ou 'yyyy-mm-dd')
 * @param {object} context - Contexte d'appel (généralement 'this')
 */
function displayCalendar(inputField, formatString, context) {
    // Convertir format dhtmlgoodies → flatpickr
    const flatpickrFormat = formatString
        .replace('dd', 'd')      // dd → d
        .replace('mm', 'm')      // mm → m
        .replace('yyyy', 'Y');   // yyyy → Y

    // Détecter format ISO (anglais)
    const isISO = formatString === 'yyyy-mm-dd';

    // Initialiser Flatpickr sur le champ
    flatpickr(inputField, {
        dateFormat: isISO ? 'Y-m-d' : 'd/m/Y',
        locale: 'fr',               // Localisation française
        allowInput: true,           // Autoriser saisie manuelle
        altInput: false,            // Pas de champ alternatif
        disableMobile: false,       // UX mobile native
        clickOpens: true,           // Ouvrir au clic

        // Événements compatibles dhtmlgoodies
        onChange: function(selectedDates, dateStr, instance) {
            // Trigger onchange natif si défini
            if (inputField.onchange) {
                inputField.onchange();
            }

            // Trigger événement change natif
            const event = new Event('change', { bubbles: true });
            inputField.dispatchEvent(event);
        },

        onReady: function(selectedDates, dateStr, instance) {
            // Supprimer l'attribut onfocus pour éviter les boucles
            if (inputField.hasAttribute('onfocus')) {
                inputField.removeAttribute('onfocus');
            }
        }
    });
}

/**
 * Initialisation automatique des datepickers au chargement de la page
 * (optionnel - si vous voulez initialiser via classe CSS)
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser tous les inputs avec classe 'datepicker'
    const datepickers = document.querySelectorAll('input.datepicker');
    datepickers.forEach(function(input) {
        const format = input.getAttribute('data-date-format') || 'dd/mm/yyyy';
        displayCalendar(input, format, null);
    });
});
