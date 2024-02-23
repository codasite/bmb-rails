import { baseButtonStyles } from '../shared/components/ActionButtons'
import { Spinner } from '../shared/components/Spinner'
import { PlusIcon } from '../shared'
import React, { useContext, useState } from 'react'
import { DarkModeContext } from '../shared/context/context'

export const AddToApparel = (props: {
  handleApparelClick: () => Promise<void>
  disabled?: boolean
  darkMode?: boolean
  variant?: 'green' | 'grey'
  processing?: boolean
  error?: boolean
}) => {
  const darkMode = useContext(DarkModeContext)
  const { handleApparelClick, disabled, processing, error } = props
  const greenStyles = [
    'tw-bg-green',
    'tw-text-dd-blue',
    'disabled:!tw-bg-transparent',
    'disabled:tw-border',
    'disabled:tw-border-solid',
    'disabled:tw-border-black/20',
    'disabled:tw-text-black/20',
    'dark:tw-bg-green/15',
    'dark:tw-text-white',
    'dark:tw-border',
    'dark:tw-border-solid',
    'dark:tw-border-green',
    'dark:disabled:tw-text-white/20',
    'dark:disabled:tw-border-white/20',
  ]
  const greyStyles = ['tw-bg-grey-blue', 'tw-text-white']
  const styles = [
    'tw-border-none',
    'tw-py-15',
    'tw-rounded-8',
    'tw-font-700',
    'tw-text-16',
    'sm:tw-text-24',
    'tw-gap-15',
    'sm:tw-gap-20',
  ]
  const colorStyles = props.variant === 'grey' ? greyStyles : greenStyles
  const buttonStyles = [...baseButtonStyles, ...styles, ...colorStyles]
  return (
    <div className={'tw-flex tw-self-stretch tw-flex-col tw-items-stretch'}>
      {(processing || error) && (
        <span
          className={`tw-m-20 tw-text-12 tw-text-center tw-font-600 ${
            darkMode ? 'tw-text-white' : 'tw-text-black'
          }`}
        >
          {error
            ? 'Sorry, we encountered an error while generating your bracket. Please try again later.'
            : 'Generating your bracket...'}
        </span>
      )}
      <button
        onClick={handleApparelClick}
        disabled={disabled || processing}
        className={buttonStyles.join(' ')}
      >
        {processing ? (
          <Spinner fill={darkMode ? 'white' : 'black'} height={24} width={24} />
        ) : (
          <>
            <PlusIcon className="tw-h-16 sm:tw-h-24" />
            <span>Add to Apparel</span>
          </>
        )}
      </button>
    </div>
  )
}
