import type { Config } from 'tailwindcss'

export default <Partial<Config>>{
  theme: {     
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
