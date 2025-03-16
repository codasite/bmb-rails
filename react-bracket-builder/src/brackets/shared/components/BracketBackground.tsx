// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
import darkBracketBg from '../assets/bracket-bg-dark.png'
import lightBracketBg from '../assets/bracket-bg-light.png'
import { DarkModeContext } from '../context/context'
import { useContext } from 'react'
interface BracketBackgroundProps {
  children: React.ReactNode
  className?: string
  useImageBackground?: boolean
}

export const BracketBackground = ({
  children,
  className = '',
  useImageBackground = true,
}: BracketBackgroundProps) => {
  const { darkMode } = useContext(DarkModeContext)
  const backgroundUrl = darkMode ? darkBracketBg : lightBracketBg
  const backgroundStyle = useImageBackground
    ? `url(${backgroundUrl})`
    : darkMode
    ? '#010433'
    : 'white'

  return (
    <div
      className={`wpbb-reset tw-uppercase tw-min-h-screen tw-px-20 tw-overflow-hidden ${
        darkMode ? 'tw-dark' : ''
      } ${className}`}
      style={{
        background: backgroundStyle,
        backgroundRepeat: 'no-repeat',
        backgroundPosition: 'top',
        backgroundSize: 'cover',
      }}
    >
      {children}
    </div>
  )
}
