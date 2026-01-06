/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./index.php",
    "./includes/**/*.php",
    "./assets/js/**/*.js"
  ],
  theme: {
    extend: {
      colors: {
        clifford: '#da373d',
      },
    },
  },
  plugins: [],
}