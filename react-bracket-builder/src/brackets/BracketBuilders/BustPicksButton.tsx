import React from 'react'
import { baseButtonStyles } from '../shared/components/ActionButtons'
import { LightningIcon } from '../shared'

export const BustPicksButton = (props: {
  onClick: () => Promise<void>
  disabled?: boolean
}) => {
  const styles = [
    'tw-py-15',
    'tw-rounded-8',
    'tw-font-700',
    'tw-text-16',
    'sm:tw-text-24',
    'tw-gap-15',
    'sm:tw-gap-20',
    'tw-bg-red/15',
    'tw-text-white',
    'tw-border',
    'tw-border-solid',
    'tw-border-red',
    'tw-flex-grow',
    'tw-basis-1/2',
  ]
  const buttonStyles = [...baseButtonStyles, ...styles]
  return (
    <button
      onClick={props.onClick}
      disabled={props.disabled}
      className={buttonStyles.join(' ')}
    >
      <LightningIcon className="tw-h-16 sm:tw-h-24" />
      Bust Picks
    </button>
  )
}
