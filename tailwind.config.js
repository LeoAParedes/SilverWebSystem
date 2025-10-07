/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.php",
    "./app/**/*.php",
    "./assets/**/*.js"
  ],
  theme: {
    extend: {
      colors: {
        emerald: {
          50: '#ecfdf5',
          100: '#d1fae5',
          200: '#a7f3d0',
          300: '#6ee7b7',
          400: '#34d399',
          500: '#10b981',
          600: '#059669',
          700: '#047857',
          800: '#065f46',
          900: '#064e3b',
        }
      },
      animation: {
        'golden-fade': 'goldenFadeIn 1.618s ease-out',
        'bounce': 'bounce 2s infinite',
      },
      scale: {
        '10382': '1.0382',
      }
    },
  },
  plugins: [],
}
/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [],
  theme: {
    extend: {},
  },
  plugins: [],
}

