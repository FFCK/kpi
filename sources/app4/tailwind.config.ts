import type { Config } from 'tailwindcss'

export default <Partial<Config>>{
  theme: {
    extend: {
      fontFamily: {
        sans: ['Raleway', 'sans-serif'],
        display: ['"Agency FB"', 'sans-serif'],
      },
    },
  },
}
