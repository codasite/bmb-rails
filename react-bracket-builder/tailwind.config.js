/** @type {import('tailwindcss').Config} */

module.exports = {
  content: ['./src/**/*.{jsx,tsx,js,ts}', '../plugin/Public/**/*.php'],
  prefix: 'tw-',
  darkMode: 'class',
  corePlugins: {
    preflight: false,
  },
  theme: {
    extend: {
      opacity: {
        15: '0.15',
        85: '0.85',
      },
      scale: {
        25: '.25',
        30: '.3',
        35: '.35',
        40: '.4',
      },
      borderWidth: {
        1: '1px',
      },
    },
    spacing: {
      0: '0px',
      1: '1px',
      2: '2px',
      4: '4px',
      6: '6px',
      8: '8px',
      10: '10px',
      11: '11px',
      12: '12px',
      14: '14px',
      15: '15px',
      16: '16px',
      18: '18px',
      20: '20px',
      24: '24px',
      30: '30px',
      32: '32px',
      40: '40px',
      48: '48px',
      50: '50px',
      52: '52px',
      60: '60px',
      80: '80px',
      100: '100px',
      section: '1160px',
    },
    minWidth: {
      20: '20px',
      24: '24px',
    },
    colors: {
      transparent: 'transparent',
      black: '#000',
      white: '#fff',
      green: '#05FF3C',
      'dark-green': '#00440F',
      blue: '#2137ff',
      'dark-blue': '#0D1454',
      'dd-blue': '#010433',
      'grey-blue': '#3D4376',
      yellow: '#F8E11A',
      red: '#FF456D',
      bluish: '#999BCD',
    },
    borderRadius: {
      4: '4px',
      8: '8px',
      16: '16px',
      full: '50%',
      60: '60px',
    },
    fontSize: {
      10: '10px',
      11: '11px',
      12: '12px',
      14: '14px',
      16: '16px',
      20: '20px',
      24: '24px',
      30: '30px',
      36: '36px',
      32: '32px',
      48: '48px',
      60: '60px',
      64: '64px',
      80: '80px',
    },
    fontWeight: {
      500: '500',
      600: '600',
      700: '700',
    },
    fontFamily: {
      sans: ['Clash Display', 'sans-serif'],
    },
  },
  plugins: [
    function ({ addUtilities, addComponents, e, prefix, config }) {
      const individualBorderStyles = {
        '.border-t-none': {
          'border-top-style': 'none',
        },
        '.border-r-none': {
          'border-right-style': 'none',
        },
        '.border-b-none': {
          'border-bottom-style': 'none',
        },
        '.border-l-none': {
          'border-left-style': 'none',
        },
        '.border-x-none': {
          'border-left-style': 'none',
          'border-right-style': 'none',
        },
        '.border-y-none': {
          'border-top-style': 'none',
          'border-bottom-style': 'none',
        },
        '.border-b-solid': {
          'border-bottom-style': 'solid',
        },
      }
      addUtilities(individualBorderStyles)
    },
    function ({ addUtilities }) {
      const hideScrollbar = {
        '.no-scrollbar::-webkit-scrollbar': {
          display: 'none',
        },
        '.no-scrollbar': {
          '-ms-overflow-style': 'none',
          'scrollbar-width': 'none',
        },
      }
      addUtilities(hideScrollbar)
    },
  ],
}
