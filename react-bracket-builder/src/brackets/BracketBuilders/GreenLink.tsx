// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
import { baseButtonStyles } from '../shared/components/ActionButtons'

export const GreenLink = (props: {
  children?: React.ReactNode
  href?: string
}) => {
  const styles = [
    'tw-py-15',
    'tw-rounded-8',
    'tw-font-700',
    'tw-text-16',
    'sm:tw-text-24',
    'tw-gap-15',
    'sm:tw-gap-20',
    'tw-bg-green/15',
    'tw-text-white',
    'tw-border',
    'tw-border-solid',
    'tw-border-green',
    'tw-flex-grow',
    'tw-basis-1/2',
  ]
  const buttonStyles = [...baseButtonStyles, ...styles]
  return (
    <a href={props.href} className={buttonStyles.join(' ')}>
      {props.children}
    </a>
  )
}
