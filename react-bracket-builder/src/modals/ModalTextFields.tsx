import * as React from 'react'

export const ModalTextField = (props: {
  hasError: boolean
  errorText: string
  placeholderText: string
  input: string
  setInput: (input: string) => void
  setHasError: (hasError: boolean) => void
}) => {
  return (
    <input
      className={`${
        props.hasError
          ? 'tw-placeholder-red/60 tw-border-red tw-text-red'
          : 'tw-placeholder-white/60'
      } tw-border-0 tw-border-b tw-border-white tw-border-solid tw-p-15 tw-outline-none tw-bg-transparent tw-text-16 tw-text-white tw-font-sans tw-w-full tw-uppercase`}
      type="text"
      placeholder={props.hasError ? props.errorText : props.placeholderText}
      value={props.input}
      onChange={(e) => {
        props.setInput(e.target.value)
        props.setHasError(!e.target.value)
      }}
    />
  )
}
