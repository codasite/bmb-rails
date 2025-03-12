// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
import { PlaceholderWrapper } from './PlaceholderWrap'
import {
  BufferedTextInputBaseProps,
  useBufferedText,
} from './BufferedTextInputBase'

export interface BufferedTextInputProps extends BufferedTextInputBaseProps {
  inputRef?: React.RefObject<HTMLInputElement>
  onPaste?: (event: React.ClipboardEvent<HTMLInputElement>) => void
}

export const BufferedTextInput = (props: BufferedTextInputProps) => {
  const { inputRef, placeholderEl, onPaste, style, className, errorText } =
    props

  const {
    showPlaceholder,
    buffer,
    hasError,
    handleChange,
    handleKeyUp,
    startEditing,
    doneEditing,
  } = useBufferedText(props)

  const errorClass = 'tw-border-red tw-text-red'
  const extraClass = hasError ? errorClass : ''
  const finalClassName = [className, extraClass].join(' ')

  return (
    <div className="tw-relative tw-flex tw-flex-col tw-gap-8">
      {showPlaceholder && placeholderEl && (
        <PlaceholderWrapper>{placeholderEl}</PlaceholderWrapper>
      )}
      <input
        ref={inputRef}
        type="text"
        onFocus={(e) => {
          startEditing()
        }}
        onBlur={doneEditing}
        onKeyUp={handleKeyUp}
        onPaste={onPaste}
        value={buffer}
        onChange={handleChange}
        className={finalClassName}
        style={style}
      />
      {hasError && errorText && (
        <span className="tw-text-red tw-text-12 tw-font-sans tw-text-left ">
          {errorText}
        </span>
      )}
    </div>
  )
}
