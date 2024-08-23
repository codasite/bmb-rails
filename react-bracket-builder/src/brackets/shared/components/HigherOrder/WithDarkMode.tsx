import React, { useState } from 'react'
import { DarkModeContext } from '../../context/context'

export const WithDarkMode = (Component: React.ComponentType<any>) => {
  return (props: any) => {
    const [darkMode, setDarkMode] = useState(true)

    return (
      <DarkModeContext.Provider value={{ darkMode, setDarkMode }}>
        <Component {...props} darkMode={darkMode} setDarkMode={setDarkMode} />
      </DarkModeContext.Provider>
    )
  }
}

export const DarkModeProvider = ({ children }) => {
  const [darkMode, setDarkMode] = useState<boolean>(true)

  return (
    <DarkModeContext.Provider value={{ darkMode, setDarkMode }}>
      {children}
    </DarkModeContext.Provider>
  )
}
