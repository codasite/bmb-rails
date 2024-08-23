import React, { Suspense } from 'react'
import { DarkModeProvider } from './brackets/shared/components/HigherOrder/WithDarkMode'

const App = (props) => {
  return (
    // Suspense is used to allow for lazy loading of components
    <Suspense>
      <DarkModeProvider>{props.children}</DarkModeProvider>
    </Suspense>
  )
}

export default App
