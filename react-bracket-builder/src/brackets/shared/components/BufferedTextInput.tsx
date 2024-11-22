// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React, { useEffect, useState } from 'react'
import { PlaceholderWrapper } from './PlaceholderWrap'

export interface BufferedTextInputProps {
  initialValue?: string
  placeholderEl?: React.ReactNode
  onDoneEditing?: (newValue: string) => void
  onStartEditing?: () => void
  validate?: (newValue: string) => boolean
  className?: string
  errorText?: string
  inputRef?: React.RefObject<HTMLInputElement>
  onHasError?: (error: string) => void
  onErrorCleared?: () => void
  noMoreInput?: boolean
  onPaste?: (event: React.ClipboardEvent<HTMLInputElement>) => void
  [key: string]: any
}

export const BufferedTextInput = (props: BufferedTextInputProps) => {
  const {
    inputRef,
    initialValue,
    onChange,
    onStartEditing,
    onDoneEditing,
    placeholderEl,
    validate,
    errorText,
    onHasError,
    onErrorCleared,
    noMoreInput,
    onPaste,
  } = props
  const [showPlaceholder, setShowPlacholder] = useState<boolean>(true)
  const [buffer, setBuffer] = useState<string>('')
  const [hasError, setHasError] = useState<boolean>(false)
  const errorClass = 'tw-border-red tw-text-red'
  const extraClass = hasError ? errorClass : ''
  const className = [props.className, extraClass].join(' ')

  useEffect(() => {
    if (initialValue) {
      setShowPlacholder(false)
      setBuffer(initialValue)
    }
  }, [initialValue])

  const doneEditing = () => {
    if (validate) {
      const isValid = validate(buffer)
      if (isValid && hasError) {
        setHasError(false)
        onErrorCleared?.()
      } else if (!isValid && !hasError) {
        setHasError(true)
        onHasError?.(errorText)
      }
    }
    if (!buffer) {
      setShowPlacholder(true)
    }
    onDoneEditing?.(buffer)
  }

  const startEditing = () => {
    setShowPlacholder(false)
    if (hasError) {
      setHasError(false)
    }
    if (onStartEditing) {
      onStartEditing()
    }
  }

  const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const { value } = event.target
    if (value.length > buffer.length && noMoreInput) {
      return
    }
    setBuffer(value)
    if (onChange) {
      onChange(event)
    }
  }

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
          e.target.select()
        }}
        onBlur={() => doneEditing()}
        onKeyUp={(e) => {
          if (e.key === 'Enter') {
            doneEditing()
            e.currentTarget.blur()
          }
        }}
        onPaste={onPaste}
        value={buffer}
        onChange={handleChange}
        className={className}
      />
      {hasError && errorText && (
        <span className="tw-text-red tw-text-12 tw-font-sans tw-text-left ">
          {errorText}
        </span>
      )}
    </div>
  )
}
