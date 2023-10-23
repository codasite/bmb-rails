import React from 'react'

export interface ActionButtonProps {
  disabled?: boolean
  onClick?: () => void
  children?: React.ReactNode
  backgroundColor?: string
  textColor?: string
  fontSize?: number
  fontWeight?: number
  padding?: number
  paddingX?: number
  paddingY?: number
  gap?: number
  borderColor?: string
  borderWidth?: number
  borderRadius?: number
  variant?: string
  className?: string
  darkMode?: boolean
  height?: number
  width?: number
}

export const ActionButtonBase = (props: ActionButtonProps) => {
  const {
    onClick,
    children,
    backgroundColor,
    textColor,
    fontSize = 20,
    fontWeight = 500,
    padding,
    paddingX,
    paddingY,
    gap = 10,
    borderColor,
    borderWidth = 1,
    borderRadius,
    className,
    disabled,
    height,
    width,
  } = props

  const baseStyles = [
    'tw-flex',
    'tw-flex-row',
    'tw-items-center',
    'tw-justify-center',
    'tw-font-sans',
    'tw-uppercase',
    'tw-whitespace-nowrap',
  ]

  if (!disabled) baseStyles.push('tw-cursor-pointer')
  if (backgroundColor) baseStyles.push(`tw-bg-${backgroundColor}`)
  if (textColor) baseStyles.push(`tw-text-${textColor}`)
  if (gap) baseStyles.push(`tw-gap-${gap}`)
  if (fontSize) baseStyles.push(`tw-text-${fontSize}`)
  if (fontWeight) baseStyles.push(`tw-font-${fontWeight}`)
  if (borderRadius) baseStyles.push(`tw-rounded-${borderRadius}`)
  if (height) baseStyles.push(`tw-h-[${height}px]`)
  if (width) baseStyles.push(`tw-w-[${width}px]`)
  if (paddingX || paddingY) {
    if (paddingX) baseStyles.push(`tw-px-${paddingX}`)
    if (paddingY) baseStyles.push(`tw-py-${paddingY}`)
  } else if (padding) baseStyles.push(`tw-py-${padding}`)

  if (borderColor && borderWidth) {
    baseStyles.push('tw-border-solid')
    if (borderWidth)
      baseStyles.push(`tw-border${borderWidth > 1 ? '-' + borderWidth : ''}`)
    if (borderColor) baseStyles.push(`tw-border-${borderColor}`)
  } else {
    baseStyles.push('tw-border-none')
  }

  const extra = className ? className.split(' ') : []

  const styles = [...baseStyles, ...extra].join(' ')

  return (
    <button className={styles} onClick={onClick} disabled={disabled}>
      {children}
    </button>
  )
}

export const GreenButton = (props: ActionButtonProps) => {
  const { disabled, darkMode } = props
  const background = disabled ? 'transparent' : 'green'
  const darkModeBackground = disabled ? 'transparent' : 'green/15'
  const border = disabled ? 'black/20' : undefined
  const darkModeBorder = disabled ? 'white/20' : 'green'
  const textColor = disabled ? 'black/20' : 'dd-blue'
  const darkModeTextColor = disabled ? 'white/20' : 'white'
  return (
    <ActionButtonBase
      backgroundColor={darkMode ? darkModeBackground : background}
      padding={16}
      textColor={darkMode ? darkModeTextColor : textColor}
      borderRadius={8}
      borderColor={darkMode ? darkModeBorder : border}
      {...props}
    />
  )
}

export const BlueButton = (props: ActionButtonProps) => {
  const { disabled } = props
  const background = disabled ? 'transparent' : 'blue/15'
  const border = disabled ? 'white/20' : 'blue'
  const textColor = disabled ? 'white/20' : 'white'

  return (
    <ActionButtonBase
      {...props}
      backgroundColor={background}
      padding={16}
      textColor={textColor}
      borderRadius={8}
      borderColor={border}
    />
  )
}

export const BigYellowButton = (props: ActionButtonProps) => {
  const { disabled } = props
  const background = disabled ? 'transparent' : 'yellow/15'
  const border = disabled ? 'white/50' : 'yellow'
  const textColor = disabled ? 'white/50' : 'yellow'

  return (
    <ActionButtonBase
      {...props}
      backgroundColor={background}
      paddingX={30}
      paddingY={16}
      fontSize={36}
      fontWeight={700}
      textColor={textColor}
      borderRadius={8}
      borderColor={border}
      borderWidth={4}
    />
  )
}

const BigGreenButton = (props: ActionButtonProps) => {
  return (
    <GreenButton
      paddingX={30}
      paddingY={16}
      fontSize={36}
      fontWeight={700}
      borderWidth={4}
      {...props}
    />
  )
}

const SmallGreenButton = (props: ActionButtonProps) => {
  return (
    <GreenButton
      height={48}
      fontSize={24}
      fontWeight={700}
      borderWidth={4}
      {...props}
    />
  )
}

export const WhiteButton = (props: ActionButtonProps) => {
  const { disabled } = props
  const background = 'transparent'
  const border = disabled ? 'white/20' : 'white'
  const textColor = disabled ? 'white/20' : 'white'

  return (
    <ActionButtonBase
      backgroundColor={background}
      textColor={textColor}
      borderRadius={8}
      fontWeight={700}
      fontSize={24}
      borderColor={border}
      height={48}
      borderWidth={4}
      {...props}
    />
  )
}

export const RedButton = (props: ActionButtonProps) => {
  const { disabled, darkMode } = props
  const background = disabled ? 'red/5' : 'red/20'
  const border = disabled ? 'red/20' : 'red'
  const textColor = disabled ? 'white/20' : 'white'
  return (
    <ActionButtonBase
      backgroundColor={background}
      textColor={textColor}
      borderRadius={8}
      borderColor={border}
      {...props}
    />
  )
}

const BigRedButton = (props: ActionButtonProps) => {
  return (
    <RedButton
      paddingX={60}
      paddingY={16}
      fontSize={36}
      fontWeight={700}
      borderWidth={4}
      {...props}
    />
  )
}

export const ActionButton = (props: ActionButtonProps) => {
  const { variant } = props

  if (props.darkMode === undefined) {
    props.darkMode = true
  }

  switch (variant) {
    case 'green':
      return <GreenButton {...props} />
    case 'big-green':
      return <BigGreenButton {...props} />
    case 'small-green':
      return <SmallGreenButton {...props} />
    case 'blue':
      return <BlueButton {...props} />
    case 'big-yellow':
      return <BigYellowButton {...props} />
    case 'white':
      return <WhiteButton {...props} />
    case 'red':
      return <RedButton {...props} />
    case 'big-red':
      return <BigRedButton {...props} />
    default:
      return <ActionButtonBase {...props} />
  }
}
