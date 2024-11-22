// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React, { useState } from 'react'
import { DarkModeContext } from './context'

export const DarkModeProvider = ({ children }) => {
  const [darkMode, setDarkMode] = useState<boolean>(true)
  return (
    <DarkModeContext.Provider value={{ darkMode, setDarkMode }}>
      {children}
    </DarkModeContext.Provider>
  )
}
