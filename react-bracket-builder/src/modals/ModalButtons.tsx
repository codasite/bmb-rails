import {
  ActionButton,
  baseButtonStyles,
} from '../brackets/shared/components/ActionButtons'
import * as React from 'react'
import Button from '../ui/Button'

export const DangerButton = (props: {
  disabled?: boolean
  onClick: () => void
  children?: React.ReactNode
}) => {
  return (
    <ActionButton
      variant="red"
      paddingY={12}
      paddingX={16}
      fontSize={16}
      fontWeight={700}
      disabled={props.disabled}
      onClick={props.onClick}
      className={
        (props.disabled ? '' : 'hover:tw-text-white/75') + ' tw-w-full'
      }
    >
      {props.children}
    </ActionButton>
  )
}

export const ConfirmButton = (props: {
  onClick?: () => void
  children?: React.ReactNode
  disabled?: boolean
  className?: string
  color?: 'green' | 'white'
}) => {
  const defaultStyles = [
    props.color === 'white' ? 'tw-bg-white/15' : 'tw-bg-green/15',
    'tw-gap-16',
    'tw-rounded-8',
    'tw-p-12',
    'tw-border-1',
    'tw-border-solid',
    props.color === 'white' ? 'tw-border-white' : 'tw-border-green',
    'hover:tw-text-white/75',
    'tw-text-white',
    'tw-w-full',
    'tw-text-16',
    'tw-font-700',
    'disabled:tw-bg-transparent',
    'disabled:tw-text-white/20',
    'disabled:tw-border-white/20',
  ]
  const styles = [
    ...baseButtonStyles,
    ...defaultStyles,
    ...(props.className ? props.className.split(' ') : []),
  ].join(' ')

  return (
    <button
      onClick={props.onClick}
      disabled={props.disabled}
      className={styles}
    >
      {props.children}
    </button>
  )
}
export const CancelButton = (props: {
  onClick: () => void
  children?: React.ReactNode
  disabled?: boolean
}) => {
  return (
    <Button
      variant={'filled'}
      onClick={props.onClick}
      disabled={props.disabled}
    >
      {props.children ?? 'Cancel'}
    </Button>
  )
}

export const Link = (props: {
  children: React.ReactNode
  href: string
  openInNewTab?: boolean
  color?: 'white' | 'green'
  variant?: 'filled' | 'outlined'
}) => {
  const { color = 'white', variant = 'outlined' } = props
  const styles = [
    'tw-flex',
    'tw-gap-16',
    'tw-items-center',
    'tw-justify-center',
    'tw-rounded-8',
    'tw-p-12',
    'hover:tw-text-white/75',
    'tw-font-sans',
    'tw-text-white',
    'tw-uppercase',
    'tw-w-full',
    'tw-text-16',
    'tw-font-500',
    'tw-cursor-pointer',
    'tw-no-underline',
  ]
  switch (color) {
    case 'green':
      styles.push('tw-bg-green/15')
      break
    case 'white':
    default:
      styles.push('tw-bg-white/15')
      break
  }
  switch (variant) {
    case 'filled':
      styles.push('tw-border-none')
      break
    case 'outlined':
    default:
      styles.push('tw-border')
      styles.push('tw-border-solid')
      switch (color) {
        case 'green':
          styles.push('tw-border-green')
          break
        case 'white':
        default:
          styles.push('tw-border-white')
          break
      }
      break
  }

  return (
    <a
      {...(props.openInNewTab
        ? { target: '_blank', rel: 'noopener noreferrer' }
        : {})}
      href={props.href}
      className={styles.join(' ')}
    >
      {props.children}
    </a>
  )
}
