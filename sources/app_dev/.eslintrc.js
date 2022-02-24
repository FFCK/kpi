module.exports = {
  root: true,

  env: {
    es2021: true
  },

  extends: [
    'plugin:vue/vue3-essential',
    '@vue/standard'
  ],

  rules: {
    'no-console': import.meta.env.NODE_ENV === 'production' ? 'warn' : 'off',
    'no-debugger': import.meta.env.NODE_ENV === 'production' ? 'warn' : 'off'
  }
}
