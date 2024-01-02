import { ActionButton } from '../shared/components/ActionButtons'
import { Spinner } from '../shared/components/Spinner'
import { PlusIcon } from '../shared'
import React, { useContext, useState } from 'react'
import { DarkModeContext } from '../shared/context/context'

export const AddToApparel = (props: {
  handleApparelClick: () => Promise<void>
  disabled: boolean
}) => {
  const darkMode = useContext(DarkModeContext)
  const { handleApparelClick, disabled } = props
  const [processingAddToApparel, setProcessingAddToApparel] = useState(false)
  const wrappedHandleApparelClick = async () => {
    setProcessingAddToApparel(true)
    try {
      await handleApparelClick()
    } catch {
      setProcessingAddToApparel(false)
    }
  }
  return (
    <div className={'tw-flex tw-self-stretch tw-flex-col tw-items-stretch'}>
      {processingAddToApparel && (
        <span
          className={`tw-m-20 tw-text-12 tw-text-center tw-font-600 ${
            darkMode ? 'tw-text-white' : 'tw-text-black'
          }`}
        >
          Generating your bracket...
        </span>
      )}
      <ActionButton
        variant="green"
        onClick={wrappedHandleApparelClick}
        disabled={disabled || processingAddToApparel}
        fontSize={24}
        fontWeight={700}
      >
        {processingAddToApparel ? (
          <Spinner fill={darkMode ? 'white' : 'black'} height={24} width={24} />
        ) : (
          <>
            <PlusIcon style={{ height: 24 }} />
            <span>Add to Apparel</span>
          </>
        )}
      </ActionButton>
    </div>
  )
}
