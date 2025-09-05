// Configure your import map in config/importmap.rb. Read more: https://github.com/rails/importmap-rails

// Load React and ReactDOM from CDN
import "https://unpkg.com/react@18/umd/react.production.min.js"
import "https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"

// Initialize React Bracket Builder when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  const bracketBuilderElement = document.getElementById('react-bracket-builder');

  if (bracketBuilderElement && typeof window.ReactBracketBuilder !== 'undefined') {
    // Mount the React app
    const root = ReactDOM.createRoot(bracketBuilderElement);
    root.render(React.createElement(window.ReactBracketBuilder));
  }
});
