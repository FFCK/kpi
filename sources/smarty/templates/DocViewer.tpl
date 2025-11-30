<link rel="stylesheet" href="../css/DocViewer.css">

<div class="main">
	<div class="titrePage">📚 Documentation KPI</div>

	<div class="doc-viewer-container">
		<!-- Sidebar avec la liste des documents -->
		<div class="doc-sidebar">
			<div class="doc-sidebar-header">
				<h3>📖 Navigation</h3>
			</div>

			<!-- Documentation Utilisateur -->
			<div class="doc-category">
				<div class="doc-category-header" onclick="toggleCategory('user')">
					<span class="doc-category-icon" id="user-icon">▼</span>
					<span class="doc-category-title">📘 Documentation Utilisateur</span>
				</div>
				<div class="doc-category-content" id="user-docs" style="display: block;">
					{if $userDocs && count($userDocs) > 0}
						{foreach $userDocs as $folder => $files}
							{if $folder != '_root'}
								<div class="doc-folder">
									<div class="doc-folder-name">📁 {$folder}</div>
								</div>
							{/if}
							<ul class="doc-file-list">
								{foreach $files as $file}
									<li class="doc-file-item {if $currentFile == $file.relative && $category == 'user'}active{/if}">
										<a href="DocViewer.php?category=user&file={$file.relative|urlencode}"
										   title="{$file.title|escape}"
										   class="doc-file-link">
											📄 {$file.title|escape}
										</a>
									</li>
								{/foreach}
							</ul>
						{/foreach}
					{else}
						<p class="doc-empty">Aucun document utilisateur disponible.</p>
					{/if}
				</div>
			</div>

			<!-- Documentation Développeur (accessible uniquement profil 1) -->
			{if $canAccessDeveloperDocs}
				<div class="doc-category">
					<div class="doc-category-header" onclick="toggleCategory('developer')">
						<span class="doc-category-icon" id="developer-icon">▶</span>
						<span class="doc-category-title">💻 Documentation Développeur</span>
					</div>
					<div class="doc-category-content" id="developer-docs" style="display: none;">
						{if $devDocs && count($devDocs) > 0}
							{foreach $devDocs as $folder => $files}
								{if $folder != '_root'}
									<div class="doc-folder">
										<div class="doc-folder-name">📁 {$folder}</div>
									</div>
								{/if}
								<ul class="doc-file-list">
									{foreach $files as $file}
										<li class="doc-file-item {if $currentFile == $file.relative && $category == 'developer'}active{/if}">
											<a href="DocViewer.php?category=developer&file={$file.relative|urlencode}"
											   title="{$file.title|escape}"
											   class="doc-file-link">
												📄 {$file.title|escape}
											</a>
										</li>
									{/foreach}
								</ul>
							{/foreach}
						{else}
							<p class="doc-empty">Aucun document développeur disponible.</p>
						{/if}
					</div>
				</div>
			{/if}
		</div>

		<!-- Zone de contenu principale -->
		<div class="doc-content">
			{if $currentFile && $fileExists}
				<div class="doc-content-header">
					<h1 class="doc-content-title">{$markdownTitle|escape}</h1>
					<div class="doc-breadcrumb">
						<a href="DocViewer.php">📚 Documentation</a>
						<span class="doc-breadcrumb-separator">›</span>
						<span class="doc-breadcrumb-category">
							{if $category == 'user'}📘 Utilisateur{else}💻 Développeur{/if}
						</span>
						<span class="doc-breadcrumb-separator">›</span>
						<span class="doc-breadcrumb-file">{$currentFile|escape}</span>
					</div>
				</div>

				<div class="doc-markdown-content">
					{$markdownContent}
				</div>
			{elseif $currentFile && !$fileExists}
				<div class="doc-error">
					<div class="alert alert-danger">
						<h3>⚠️ Fichier non trouvé</h3>
						<p>Le fichier demandé n'existe pas ou n'est pas accessible.</p>
						<p><a href="DocViewer.php">← Retour à la liste des documents</a></p>
					</div>
				</div>
			{else}
				<div class="doc-welcome">
					<h1>📚 Bienvenue dans la documentation KPI</h1>

					<div class="doc-welcome-section">
						<h2>📘 Documentation Utilisateur</h2>
						<p>
							La documentation utilisateur contient des guides et des explications sur les fonctionnalités
							du système KPI. Ces documents sont destinés aux utilisateurs finaux et aux administrateurs.
						</p>
						{if $userDocs && count($userDocs) > 0}
							<p class="doc-welcome-count">
								<strong>{count($userDocs)} section(s) disponible(s)</strong>
							</p>
						{/if}
					</div>

					{if $canAccessDeveloperDocs}
						<div class="doc-welcome-section">
							<h2>💻 Documentation Développeur</h2>
							<p>
								La documentation développeur contient des guides techniques, des notes de migration,
								des audits de code et des plans d'action pour le développement et la maintenance du projet.
							</p>
							{if $devDocs && count($devDocs) > 0}
								<p class="doc-welcome-count">
									<strong>{count($devDocs)} section(s) disponible(s)</strong>
								</p>
							{/if}
						</div>
					{/if}

					<div class="doc-welcome-section">
						<h3>🚀 Pour commencer</h3>
						<p>Sélectionnez un document dans le menu latéral pour commencer la lecture.</p>
						<p>Les documents sont organisés par catégories et sous-dossiers pour faciliter la navigation.</p>
						{if !$canAccessDeveloperDocs}
							<p style="margin-top: 15px; padding: 10px; background-color: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
								<strong>Note :</strong> La documentation développeur est réservée aux utilisateurs avec le profil Webmaster/Président (profil 1).
							</p>
						{/if}
					</div>
				</div>
			{/if}
		</div>
	</div>
</div>

<script src="../js/DocViewer.js"></script>
