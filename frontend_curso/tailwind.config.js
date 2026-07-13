/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
      colors: {
        background: '#0f172a', // Slate 900
        surface: '#1e293b',    // Slate 800
        primary: {
          DEFAULT: '#3b82f6',  // Blue 500
          hover: '#2563eb',    // Blue 600
        },
        text: {
          DEFAULT: '#f8fafc',  // Slate 50
          muted: '#94a3b8',    // Slate 400
        },
        border: '#334155',     // Slate 700
      }
    },
  },
  plugins: [],
}
