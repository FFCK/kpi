import type { Config } from 'tailwindcss'

export default <Partial<Config>>{
  theme: {
    colors: {
      blue: {
        50:  '#f2f5ff',   // généré par l’outil
        100: '#e4eaff',
        200: '#cbd5ff',
        300: '#b1bef5',
        400: '#909eda',
        500: '#7582bf', // color-primary
        600: '#5e6aa4',
        700: '#4f598e',
        800: '#404978',
        900: '#20265b', // color-header
      },
      gray: {
        50:  '#f5f5f5',
        100: '#ebebea',
        200: '#d7d7d6',
        300: '#c1c1bf',
        400: '#a2a29f',
        500: '#868684',
        600: '#6f6f6c',
        700: '#5e5e5b',
        800: '#4d4d4b',
        900: '#424241', // color-dark
        950: '#292928'
      },
      yellow: {
        50:  '#c6c7c7', // color-light (si tu assumes ce mapping)
        100: '...',
        200: '...',
        300: '...',
        400: '...',
        500: '#e9b410', // color-warning
        600: '...',
        700: '...',
        800: '...',
        900: '...',
      },
      green: {
        50:  '...',
        100: '...',
        200: '...',
        300: '...',
        400: '...',
        500: '#209452', // color-success
        600: '...',
        700: '...',
        800: '...',
        900: '...',
      },
      red: {
        50:  '...',
        100: '...',
        200: '...',
        300: '...',
        400: '...',
        500: '#c94a4c', // color-danger
        600: '...',
        700: '...',
        800: '...',
        900: '...',
      },
    },
    extend: {
      colors: {
        'kpi-primary': 'var(--color-primary)',
        'kpi-secondary': 'var(--color-secondary)',
        'kpi-light': 'var(--color-light)',
        'kpi-dark': 'var(--color-dark)',
        'kpi-warning': 'var(--color-warning)',
        'kpi-success': 'var(--color-success)',
        'kpi-danger': 'var(--color-danger)',
        'kpi-header': 'var(--color-header)',
      },
      fontFamily: {
        sans: ['Raleway', 'sans-serif'],
        display: ['"Agency FB"', 'sans-serif'],
      },
    },
  },
}
