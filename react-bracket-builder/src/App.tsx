import React, { Suspense } from 'react'

const App = (props) => {
  return (
    // Suspense is used to allow for lazy loading of components
    <Suspense>{props.children}</Suspense>
  )
}

export default App
