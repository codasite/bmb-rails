// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
import { render } from 'react-dom'
import './styles/main.css'

// Simple test component to verify React is working
const TestComponent = () => {
  const [count, setCount] = React.useState(0)
  
  return (
    <div style={{ padding: '20px', fontFamily: 'Arial, sans-serif' }}>
      <h1 style={{ color: '#333', marginBottom: '20px' }}>🏆 Bracket Builder Test</h1>
      <p>React is working! Counter: {count}</p>
      <button 
        onClick={() => setCount(count + 1)}
        style={{
          padding: '10px 20px',
          backgroundColor: '#007bff',
          color: 'white',
          border: 'none',
          borderRadius: '5px',
          cursor: 'pointer'
        }}
      >
        Click me!
      </button>
      <div style={{ marginTop: '20px', padding: '15px', backgroundColor: '#f8f9fa', borderRadius: '5px' }}>
        <h3>Next Steps:</h3>
        <ul>
          <li>✅ React rendering works</li>
          <li>🔄 Loading bracket builder components...</li>
          <li>🔄 Testing Rails API connection...</li>
        </ul>
      </div>
    </div>
  )
}

// Render the test component first
const rootDiv = document.getElementById('root')
if (rootDiv) {
  render(<TestComponent />, rootDiv)
} else {
  console.error('Root div not found!')
}
