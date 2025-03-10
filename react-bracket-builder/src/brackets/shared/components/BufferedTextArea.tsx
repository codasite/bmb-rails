// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React, { useEffect, useRef } from 'react'
import { PlaceholderWrapper } from './PlaceholderWrap'
import {
  BufferedTextInputBaseProps,
  useBufferedText,
} from './BufferedTextInputBase'

export interface BufferedTextAreaProps extends BufferedTextInputBaseProps {
  textAreaRef?: React.RefObject<HTMLTextAreaElement>
  onPaste?: (event: React.ClipboardEvent<HTMLTextAreaElement>) => void
  rows?: number
}

export const BufferedTextArea = (props: BufferedTextAreaProps) => {
  const {
    textAreaRef,
    placeholderEl,
    onPaste,
    style,
    className,
    errorText,
    rows = 3,
  } = props

  const internalRef = useRef<HTMLTextAreaElement>(null)
  const textareaRef = textAreaRef || internalRef

  const {
    showPlaceholder,
    buffer,
    hasError,
    handleChange,
    handleKeyUp,
    startEditing,
    doneEditing,
  } = useBufferedText({
    ...props,
    shouldSubmitOnEnter: false,
  })

  const adjustHeight = () => {
    const textarea = textareaRef.current
    if (textarea) {
      textarea.style.height = 'auto'
      textarea.style.height = `${textarea.scrollHeight}px`
    }
  }

  useEffect(adjustHeight, [buffer])

  const errorClass = 'tw-border-red tw-text-red'
  const extraClass = hasError ? errorClass : ''
  const finalClassName = [className, extraClass].join(' ')

  return (
    <div className="tw-relative tw-flex tw-flex-col tw-gap-8">
      {showPlaceholder && placeholderEl && (
        <PlaceholderWrapper>{placeholderEl}</PlaceholderWrapper>
      )}
      <textarea
        ref={textareaRef}
        rows={rows}
        onFocus={(e) => {
          startEditing()
          e.target.select()
        }}
        onBlur={doneEditing}
        onKeyUp={handleKeyUp}
        onPaste={onPaste}
        value={buffer}
        onChange={(e) => {
          handleChange(e)
          adjustHeight()
        }}
        className={finalClassName}
        style={{
          ...style,
          resize: 'none',
          overflow: 'hidden',
        }}
      />
      {hasError && errorText && (
        <span className="tw-text-red tw-text-12 tw-font-sans tw-text-left ">
          {errorText}
        </span>
      )}
    </div>
  )
}
