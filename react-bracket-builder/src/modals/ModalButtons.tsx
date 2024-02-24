import {
  ActionButton,
  ActionButtonProps,
  baseButtonStyles,
} from '../brackets/shared/components/ActionButtons'
import * as React from 'react'

export const DangerButton = (props: {
  disabled: boolean
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
  variant?: 'green' | 'white'
}) => {
  const defaultStyles = [
    props.variant === 'white' ? 'tw-bg-white/15' : 'tw-bg-green/15',
    'tw-gap-16',
    'tw-rounded-8',
    'tw-p-12',
    'tw-border-1',
    'tw-border-solid',
    props.variant === 'white' ? 'tw-border-white' : 'tw-border-green',
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
  const defaultStyles = [
    'tw-bg-white/15',
    'tw-gap-16',
    'tw-rounded-8',
    'tw-p-12',
    'tw-border-none',
    'hover:tw-text-white/75',
    'tw-text-white',
    'tw-w-full',
    'tw-text-16',
    'tw-font-500',
    'disabled:tw-bg-white/15',
    'disabled:tw-text-white/50',
  ]
  const styles = [...baseButtonStyles, ...defaultStyles].join(' ')
  return (
    <button
      disabled={props.disabled}
      onClick={props.onClick}
      className={styles}
    >
      {props.children ?? 'Cancel'}
    </button>
  )
}

export const Link = (props: {
  children: React.ReactNode
  href: string
  openInNewTab?: boolean
}) => {
  return (
    <a
      {...(props.openInNewTab
        ? { target: '_blank', rel: 'noopener noreferrer' }
        : {})}
      href={props.href}
      className="tw-bg-white/15 tw-flex tw-gap-16 tw-items-center tw-justify-center tw-rounded-8 tw-p-12 hover:tw-text-white/75 tw-font-sans tw-text-white tw-uppercase tw-w-full tw-text-16 tw-font-500 tw-cursor-pointer tw-border-solid tw-border tw-border-white"
    >
      {props.children}
    </a>
  )
}
