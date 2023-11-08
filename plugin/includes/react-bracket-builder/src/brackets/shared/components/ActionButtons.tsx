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
  size?: string
  filled?: boolean
}

const ActionButtonBase = (props: ActionButtonProps) => {
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

const BlueButton = (props: ActionButtonProps) => {
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

const YellowButton = (props: ActionButtonProps) => {
  switch (props.size) {
    case 'big':
      return <BigYellowButton {...props} />
    case 'small':
      return <SmallYellowButton {...props} />
    default:
      return <DefaultYellowButton {...props} />
  }
}

const DefaultYellowButton = (props: ActionButtonProps) => {
  const { disabled } = props
  const background = disabled ? 'transparent' : 'yellow/15'
  const border = disabled ? 'white/50' : 'yellow'
  const textColor = disabled ? 'white/50' : 'yellow'

  return (
    <ActionButtonBase
      backgroundColor={background}
      fontWeight={700}
      padding={16}
      textColor={textColor}
      borderRadius={8}
      borderColor={border}
      borderWidth={4}
      {...props}
    />
  )
}

const BigYellowButton = (props: ActionButtonProps) => {
  return (
    <DefaultYellowButton paddingX={30} paddingY={16} fontSize={36} {...props} />
  )
}
const SmallYellowButton = (props: ActionButtonProps) => {
  return <DefaultYellowButton height={48} fontSize={24} {...props} />
}

const GreenButton = (props: ActionButtonProps) => {
  switch (props.size) {
    case 'big':
      return <BigGreenButton {...props} />
    case 'small':
      return <SmallGreenButton {...props} />
    default:
      return <DefaultGreenButton {...props} />
  }
}
const BigGreenButton = (props: ActionButtonProps) => {
  return (
    <DefaultGreenButton
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
    <DefaultGreenButton
      height={48}
      fontSize={24}
      fontWeight={700}
      borderWidth={4}
      {...props}
    />
  )
}

const DefaultGreenButton = (props: ActionButtonProps) => {
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

const WhiteButton = (props: ActionButtonProps) => {
  if (props.filled) return <FilledWhiteButton {...props} />
  return <DefaultWhiteButton {...props} />
}

const DefaultWhiteButton = (props: ActionButtonProps) => {
  const { disabled, darkMode } = props
  const background = 'transparent'
  const border = disabled ? 'black/20' : 'white'
  const darkModeBorder = disabled ? 'white/20' : 'white'
  const textColor = disabled ? 'black/20' : 'white'
  const darkModeTextColor = disabled ? 'white/20' : 'white'

  return (
    <ActionButtonBase
      backgroundColor={background}
      textColor={darkMode ? darkModeTextColor : textColor}
      borderRadius={8}
      fontWeight={700}
      fontSize={24}
      borderColor={darkMode ? darkModeBorder : border}
      height={48}
      borderWidth={1}
      {...props}
    />
  )
}

const FilledWhiteButton = (props: ActionButtonProps) => {
  const { disabled } = props
  const background = disabled ? 'white/20' : 'white'
  const textColor = disabled ? 'black/20' : 'black'

  return (
    <DefaultWhiteButton
      backgroundColor={background}
      textColor={textColor}
      borderWidth={0}
      {...props}
    />
  )
}

const RedButton = (props: ActionButtonProps) => {
  switch (props.size) {
    case 'big':
      return <BigRedButton {...props} />
    case 'small':
      return <SmallRedButton {...props} />
    default:
      return <DefaultRedButton {...props} />
  }
}

const DefaultRedButton = (props: ActionButtonProps) => {
  const { disabled } = props
  const background = disabled ? 'red/5' : 'red/20'
  const border = disabled ? 'red/20' : 'red'
  const textColor = disabled ? 'white/20' : 'white'
  return (
    <ActionButtonBase
      backgroundColor={background}
      textColor={textColor}
      borderRadius={8}
      fontWeight={700}
      fontSize={24}
      borderColor={border}
      borderWidth={4}
      {...props}
    />
  )
}

const SmallRedButton = (props: ActionButtonProps) => {
  return <DefaultRedButton height={48} fontSize={24} {...props} />
}

const BigRedButton = (props: ActionButtonProps) => {
  return (
    <DefaultRedButton paddingX={60} paddingY={16} fontSize={36} {...props} />
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
    case 'blue':
      return <BlueButton {...props} />
    case 'yellow':
      return <YellowButton {...props} />
    case 'white':
      return <WhiteButton {...props} />
    case 'red':
      return <RedButton {...props} />
    default:
      return <ActionButtonBase {...props} />
  }
}
