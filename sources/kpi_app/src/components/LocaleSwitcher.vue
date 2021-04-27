<template>
    <div class="locale-switcher nav-link">
        🌐
        <select v-model="$i18n.locale">
            <option value="en">English</option>
            <option value="fr">Français</option>
        </select>
    </div>
</template>
