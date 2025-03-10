// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React, { useEffect, useState } from 'react'
import { PlaceholderWrapper } from './PlaceholderWrap'

export interface BufferedTextInputBaseProps {
  initialValue?: string
  placeholderEl?: React.ReactNode
  onDoneEditing?: (newValue: string) => void
  onStartEditing?: () => void
  validate?: (newValue: string) => boolean
  className?: string
  errorText?: string
  onHasError?: (error: string) => void
  onErrorCleared?: () => void
  noMoreInput?: boolean
  style?: React.CSSProperties
  onChange?: (event: React.ChangeEvent<any>) => void
}

export interface UseBufferedTextProps extends BufferedTextInputBaseProps {
  shouldSubmitOnEnter?: boolean
}

export const useBufferedText = (props: UseBufferedTextProps) => {
  const {
    initialValue,
    onChange,
    onStartEditing,
    onDoneEditing,
    validate,
    errorText,
    onHasError,
    onErrorCleared,
    noMoreInput,
    shouldSubmitOnEnter = true,
  } = props

  const [showPlaceholder, setShowPlaceholder] = useState<boolean>(true)
  const [buffer, setBuffer] = useState<string>('')
  const [hasError, setHasError] = useState<boolean>(false)

  useEffect(() => {
    if (initialValue) {
      setShowPlaceholder(false)
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
      setShowPlaceholder(true)
    }
    onDoneEditing?.(buffer)
  }

  const startEditing = () => {
    setShowPlaceholder(false)
    if (hasError) {
      setHasError(false)
    }
    onStartEditing?.()
  }

  const handleChange = <T extends HTMLInputElement | HTMLTextAreaElement>(
    event: React.ChangeEvent<T>
  ) => {
    const { value } = event.target
    if (value.length > buffer.length && noMoreInput) {
      return
    }
    setBuffer(value)
    onChange?.(event)
  }

  const handleKeyUp = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter' && shouldSubmitOnEnter) {
      doneEditing()
      ;(e.target as HTMLElement).blur()
    }
  }

  return {
    showPlaceholder,
    buffer,
    hasError,
    handleChange,
    handleKeyUp,
    startEditing,
    doneEditing,
  }
}
