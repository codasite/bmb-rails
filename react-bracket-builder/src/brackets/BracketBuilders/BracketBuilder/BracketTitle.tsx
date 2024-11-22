// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React, { useState } from 'react'

export interface BracketTitleProps {
  title: string
  placeholder: string
  setTitle: (title: string) => void
  showError?: boolean
  errorMessage?: string
  onChange?: (event: any) => void
}

export const BracketTitle = (props: BracketTitleProps) => {
  const { title, setTitle, placeholder, showError, errorMessage, onChange } =
    props

  const [editing, setEditing] = useState(false)
  const [textBuffer, setTextBuffer] = useState(title)

  const startEditing = () => {
    setEditing(true)
    setTextBuffer(title)
  }
  const doneEditing = (event: any) => {
    setTitle(textBuffer)
    setEditing(false)
  }

  const onInputChange = (event: any) => {
    setTextBuffer(event.target.value)
    if (onChange) {
      onChange(event)
    }
  }

  return (
    <div
      className={`tw-flex tw-justify-center tw-border-b-solid ${
        showError ? 'tw-border-red/80' : 'tw-border-white/30'
      } tw-p-16 `}
      onClick={startEditing}
    >
      {editing ? (
        <input
          className="tw-py-0 tw-outline-none tw-border-none tw-bg-transparent tw-text-24 sm:tw-text-32 tw-text-white tw-text-center tw-font-sans tw-w-full tw-uppercase"
          autoFocus
          onFocus={(e) => e.target.select()}
          type="text"
          value={textBuffer}
          onChange={onInputChange}
          onBlur={doneEditing}
          onKeyUp={(e) => {
            if (e.key === 'Enter') {
              doneEditing(e)
            }
          }}
        />
      ) : (
        <h1
          className={`tw-font-500 tw-text-24 sm:tw-text-32 ${
            showError ? '!tw-text-red/80' : '!tw-text-white/20'
          } tw-text-center`}
        >
          {showError ? errorMessage : title || placeholder}
        </h1>
      )}
    </div>
  )
}
