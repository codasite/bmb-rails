import React, { useContext, useState } from 'react'
import { baseButtonStyles } from '../../shared/components/ActionButtons'
import { CircleCheckBrokenIcon } from '../../shared'
import { DarkModeContext } from '../../shared/context/context'
import { Spinner } from '../../shared/components/Spinner'

export const SubmitPicksButton = (props: {
  onClick: () => Promise<void>
  disabled?: boolean
  processing?: boolean
  error?: boolean
}) => {
  const { darkMode } = useContext(DarkModeContext)
  const { onClick, disabled, processing, error } = props
  const styles = [
    'tw-border-none',
    'tw-py-15',
    'tw-rounded-8',
    'tw-font-700',
    'tw-text-16',
    'sm:tw-text-24',
    'tw-gap-15',
    'sm:tw-gap-20',
    'tw-bg-blue',
    'tw-text-white',
    'disabled:!tw-bg-transparent',
    'disabled:tw-border',
    'disabled:tw-border-solid',
    'disabled:tw-border-black/20',
    'disabled:tw-text-black/20',
    'dark:tw-bg-blue/15',
    'dark:tw-text-white',
    'dark:tw-border',
    'dark:tw-border-solid',
    'dark:tw-border-blue',
    'dark:disabled:tw-text-white/20',
    'dark:disabled:tw-border-white/20',
  ]
  const buttonStyles = [...baseButtonStyles, ...styles]
  return (
    <button
      onClick={onClick}
      disabled={disabled || processing}
      className={buttonStyles.join(' ')}
    >
      {processing ? (
        <Spinner fill={darkMode ? 'white' : 'black'} height={24} width={24} />
      ) : (
        <>
          <CircleCheckBrokenIcon className="tw-h-16 sm:tw-h-24" />
          <span>Submit Picks</span>
        </>
      )}
    </button>
  )
}
