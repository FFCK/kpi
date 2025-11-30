/**
 * DocViewer.js
 * Gestion de l'interface du visualiseur de documentation
 */

/**
 * Basculer l'affichage d'une catégorie de documentation
 */
function toggleCategory(categoryId) {
	const docsDiv = document.getElementById(categoryId + '-docs');
	const iconSpan = document.getElementById(categoryId + '-icon');

	if (!docsDiv || !iconSpan) {
		return;
	}

	if (docsDiv.style.display === 'none') {
		docsDiv.style.display = 'block';
		iconSpan.textContent = '▼';
	} else {
		docsDiv.style.display = 'none';
		iconSpan.textContent = '▶';
	}
}

/**
 * Rechercher dans la documentation
 */
function searchDocs() {
	const searchInput = document.getElementById('doc-search');
	if (!searchInput) {
		return;
	}

	const searchTerm = searchInput.value.toLowerCase();
	const fileLinks = document.querySelectorAll('.doc-file-item');

	fileLinks.forEach(function(item) {
		const text = item.textContent.toLowerCase();
		if (text.includes(searchTerm)) {
			item.style.display = 'list-item';
		} else {
			item.style.display = 'none';
		}
	});
}

/**
 * Initialisation au chargement de la page
 */
document.addEventListener('DOMContentLoaded', function() {
	// Ouvrir automatiquement la catégorie contenant le document actif
	const activeItem = document.querySelector('.doc-file-item.active');
	if (activeItem) {
		const categoryContent = activeItem.closest('.doc-category-content');
		if (categoryContent) {
			categoryContent.style.display = 'block';
			const categoryId = categoryContent.id.replace('-docs', '');
			const iconSpan = document.getElementById(categoryId + '-icon');
			if (iconSpan) {
				iconSpan.textContent = '▼';
			}
		}
	}

	// Améliorer les liens dans le contenu markdown
	enhanceMarkdownLinks();

	// Ajouter un bouton "retour en haut" si le contenu est long
	addScrollToTop();
});

/**
 * Améliorer les liens dans le contenu markdown
 * - Ouvrir les liens externes dans un nouvel onglet
 * - Convertir les liens relatifs vers d'autres docs
 */
function enhanceMarkdownLinks() {
	const contentDiv = document.querySelector('.doc-markdown-content');
	if (!contentDiv) {
		return;
	}

	const links = contentDiv.querySelectorAll('a');
	links.forEach(function(link) {
		const href = link.getAttribute('href');
		if (!href) {
			return;
		}

		// Liens externes
		if (href.startsWith('http://') || href.startsWith('https://')) {
			link.setAttribute('target', '_blank');
			link.setAttribute('rel', 'noopener noreferrer');
			// Ajouter une icône pour indiquer que c'est un lien externe
			if (!link.querySelector('.external-link-icon')) {
				const icon = document.createElement('span');
				icon.className = 'external-link-icon';
				icon.textContent = ' ↗';
				link.appendChild(icon);
			}
		}
		// Liens vers d'autres fichiers markdown
		else if (href.endsWith('.md')) {
			const currentUrl = new URL(window.location.href);
			const category = currentUrl.searchParams.get('category') || 'user';

			// Construire le chemin du fichier cible
			let targetFile = href;

			// Si le lien est relatif, résoudre le chemin
			if (href.startsWith('./') || href.startsWith('../') || (!href.startsWith('/') && !href.startsWith('http'))) {
				const currentFile = currentUrl.searchParams.get('file') || '';
				const currentDir = currentFile.substring(0, currentFile.lastIndexOf('/') + 1);

				// Nettoyer le lien
				targetFile = href.replace(/^\.\//, ''); // Supprimer ./

				// Si c'est un lien parent (..)
				if (targetFile.startsWith('../')) {
					// Remonter dans l'arborescence
					let dirParts = currentDir.split('/').filter(p => p);
					let fileParts = targetFile.split('/');

					while (fileParts[0] === '..') {
						dirParts.pop();
						fileParts.shift();
					}

					targetFile = dirParts.concat(fileParts).join('/');
				} else {
					// Lien dans le même dossier ou sous-dossier
					targetFile = currentDir + targetFile;
				}
			} else if (targetFile.startsWith('/')) {
				// Lien absolu depuis la racine de la catégorie
				targetFile = targetFile.substring(1);
			}

			// Construire le nouveau lien
			link.setAttribute('href', 'DocViewer.php?category=' + category + '&file=' + encodeURIComponent(targetFile));
		}
	});
}

/**
 * Ajouter un bouton "retour en haut" pour les longs documents
 */
function addScrollToTop() {
	const contentDiv = document.querySelector('.doc-content');
	if (!contentDiv) {
		return;
	}

	// Créer le bouton
	const scrollButton = document.createElement('button');
	scrollButton.className = 'doc-scroll-top';
	scrollButton.innerHTML = '↑ Haut';
	scrollButton.title = 'Retour en haut de la page';
	scrollButton.style.display = 'none';

	// Ajouter le bouton au DOM
	document.body.appendChild(scrollButton);

	// Afficher/masquer le bouton selon le scroll
	window.addEventListener('scroll', function() {
		if (window.pageYOffset > 300) {
			scrollButton.style.display = 'block';
		} else {
			scrollButton.style.display = 'none';
		}
	});

	// Action au clic
	scrollButton.addEventListener('click', function() {
		window.scrollTo({
			top: 0,
			behavior: 'smooth'
		});
	});
}

/**
 * Copier le code d'un bloc code dans le presse-papier
 */
function copyCodeBlock(button) {
	const codeBlock = button.parentElement.querySelector('code');
	if (!codeBlock) {
		return;
	}

	const text = codeBlock.textContent;

	// Utiliser l'API Clipboard si disponible
	if (navigator.clipboard && navigator.clipboard.writeText) {
		navigator.clipboard.writeText(text).then(function() {
			button.textContent = '✓ Copié !';
			setTimeout(function() {
				button.textContent = '📋 Copier';
			}, 2000);
		}).catch(function(err) {
			console.error('Erreur lors de la copie:', err);
		});
	} else {
		// Fallback pour les navigateurs plus anciens
		const textArea = document.createElement('textarea');
		textArea.value = text;
		textArea.style.position = 'fixed';
		textArea.style.left = '-9999px';
		document.body.appendChild(textArea);
		textArea.select();
		try {
			document.execCommand('copy');
			button.textContent = '✓ Copié !';
			setTimeout(function() {
				button.textContent = '📋 Copier';
			}, 2000);
		} catch (err) {
			console.error('Erreur lors de la copie:', err);
		}
		document.body.removeChild(textArea);
	}
}
