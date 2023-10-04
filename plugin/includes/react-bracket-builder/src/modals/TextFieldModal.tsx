import * as React from 'react'
import { Modal } from './Modal'
import { CancelButton, ConfirmButton } from './ModalButtons'
import { ModalHeader } from './ModalHeader'

export const TextFieldModal = (props: {
  show: boolean
  setShow: (show: boolean) => void
  loading: boolean
  submitButtonText: string
  onSubmit: () => void
  errorText: string
  placeholderText: string
  header: string
  input: string
  setInput: (input: string) => void
  hasError: boolean
  setHasError: (hasError: boolean) => void
}) => {
  return (
    <Modal show={props.show} setShow={props.setShow}>
      <div className="tw-flex tw-flex-col">
        <ModalHeader text={props.header} />
        <input
          className={`${
            props.hasError
              ? 'tw-placeholder-red/60 tw-border-red tw-text-red'
              : 'tw-placeholder-white/60'
          } tw-border-0 tw-border-b tw-border-white tw-mb-30 tw-border-solid tw-p-15 tw-outline-none tw-bg-transparent tw-text-16 tw-text-white tw-font-sans tw-w-full tw-uppercase`}
          type="text"
          placeholder={props.hasError ? props.errorText : props.placeholderText}
          value={props.input}
          onChange={(e) => {
            props.setInput(e.target.value)
            props.setHasError(!e.target.value)
          }}
        />
        <div className="tw-flex tw-flex-col tw-gap-10">
          <ConfirmButton
            disabled={props.loading || props.hasError}
            onClick={props.onSubmit}
          >
            {props.submitButtonText}
          </ConfirmButton>
          <CancelButton onClick={() => props.setShow(false)} />
        </div>
      </div>
    </Modal>
  )
}
