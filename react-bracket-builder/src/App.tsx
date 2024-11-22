// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
import { DarkModeProvider } from './brackets/shared/context/Providers'

const App = (props) => {
  return (
    // Suspense is used to allow for lazy loading of components
    <React.Suspense>
      <DarkModeProvider>{props.children}</DarkModeProvider>
    </React.Suspense>
  )
}

export default App
