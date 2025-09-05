// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
import App from './App'

declare global {
  interface Window {
    ReactBracketBuilder: React.FC
  }
}

// Expose a React component for Rails to mount via window.ReactBracketBuilder
// The Rails layout will do: ReactDOM.createRoot(el).render(React.createElement(window.ReactBracketBuilder))
window.ReactBracketBuilder = function ReactBracketBuilder() {
  return (
    <App>
      <div className="rbb-container" style={{ padding: 16 }}>
        <h2>React Bracket Builder</h2>
        <p>The React app is wired up. Replace this with full integration.</p>
      </div>
    </App>
  )
}

export {}


