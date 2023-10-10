import * as React from 'react'
import { Modal } from './Modal'
import { CancelButton, ConfirmButton } from './ModalButtons'
import { ModalHeader } from './ModalHeader'
import { ModalTextField } from './ModalTextFields'

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
        <div className="tw-flex tw-flex-col tw-gap-10">
          <ModalTextField
            hasError={props.hasError}
            errorText={props.errorText}
            placeholderText={props.placeholderText}
            input={props.input}
            setInput={props.setInput}
            setHasError={props.setHasError}
          />
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
