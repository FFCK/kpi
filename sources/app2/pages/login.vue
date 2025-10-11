<template>
  <div class="container mx-auto px-4 py-8">
    <div v-if="user" class="text-center">
      <h2 class="text-2xl font-semibold mb-4">{{ t('Login.Welcome') }}, {{ user.firstname }} {{ user.name }}</h2>
      <button @click="handleLogout" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded mb-4">
        {{ t('Login.Logout') }}
      </button>
      <div class="flex justify-center pt-8">
        <NuxtLink to="/scrutineering" class="border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white font-semibold rounded px-4 py-2 text-lg transition-colors flex items-center justify-center gap-2 w-64">
          <UIcon name="i-heroicons-clipboard-document-check" class="h-6 w-6" />
          <span>{{ t('nav.Scrutineering') }}</span>
        </NuxtLink>
      </div>
    </div>
    <div v-else>
      <form @submit.prevent="handleLogin" class="max-w-sm mx-auto bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <h2 class="text-2xl font-semibold mb-6 text-center">{{ t('Login.Authentication') }}</h2>
        <div v-if="message" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
          <span class="block sm:inline">{{ message }}</span>
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="login">
            {{ t('Login.Login') }}
          </label>
          <input v-model="input.login" id="login" type="tel" :placeholder="t('Login.LoginHelp')" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="mb-6">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
            {{ t('Login.Password') }}
          </label>
          <input v-model="input.password" id="password" type="password" placeholder="******************" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="flex items-center justify-between">
          <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            {{ t('Login.Submit') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuth } from '~/composables/useAuth'

// Protect this page - require event selection
definePageMeta({
  middleware: 'event-guard'
})

const { t } = useI18n()
const { user, login, logout } = useAuth()

const input = ref({
  login: '',
  password: ''
})
const message = ref('')

const handleLogin = async () => {
  if (input.value.login && input.value.password) {
    const success = await login(input.value.login, input.value.password)
    if (!success) {
      message.value = t('Login.UnauthorizedMsg')
    } else {
      message.value = ''
    }
  } else {
    message.value = t('Login.EmptyMsg')
  }
}

const handleLogout = async () => {
  await logout()
}
</script>
