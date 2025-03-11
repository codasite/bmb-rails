// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
import darkBracketBg from '../assets/bracket-bg-dark.png'

interface BracketBackgroundProps {
  children: React.ReactNode
  backgroundImage?: string
  additionalBackground?: string
  className?: string
}

export const BracketBackground = ({
  children,
  backgroundImage = darkBracketBg,
  additionalBackground,
  className = '',
}: BracketBackgroundProps) => {
  const backgroundStyle = additionalBackground
    ? `url(${backgroundImage}), ${additionalBackground}`
    : `url(${backgroundImage})`

  return (
    <div
      className={`wpbb-reset tw-uppercase tw-bg-no-repeat tw-bg-top tw-bg-cover tw-min-h-screen tw-px-20 ${className}`}
      style={{ backgroundImage: backgroundStyle }}
    >
      {children}
    </div>
  )
}
