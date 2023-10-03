import * as React from 'react'
import { useState } from 'react'
import { ActionButton } from '../../brackets/shared/components/ActionButtons'
import addClickHandlers from '../addClickHandlers'
import { Modal } from '../Modal'

export const DashboardModal = (props: {
  loading: boolean
  submitButtonText: string
  onSubmit: () => void
  errorText: string
  placeholderText: string
  header: string
  input: string
  setInput: (input: string) => void
  buttonClassName: string
  onButtonClick: (e: HTMLButtonElement) => void
  hasError: boolean
  setHasError: (hasError: boolean) => void
}) => {
  const [showModal, setShowModal] = useState(false)
  addClickHandlers({
    buttonClassName: props.buttonClassName,
    onButtonClick: (b) => {
      props.onButtonClick(b)
      setShowModal(true)
    },
  })
  return (
    <Modal show={showModal} setShow={setShowModal}>
      <div className="tw-flex tw-flex-col">
        <h1 className="tw-text-32 tw-leading-10 tw-font-white tw-whitespace-pre-line tw-mb-30">
          {props.header}
        </h1>
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
          <ActionButton
            variant="green"
            paddingY={12}
            paddingX={16}
            fontSize={16}
            fontWeight={700}
            disabled={props.loading || props.hasError}
            onClick={props.onSubmit}
            className="hover:tw-text-white/75"
          >
            {props.submitButtonText}
          </ActionButton>
          <button
            onClick={() => setShowModal(false)}
            className="tw-bg-white/15 tw-flex tw-gap-16 tw-items-center tw-justify-center tw-rounded-8 tw-p-12 tw-border-none hover:tw-text-white/75 tw-font-sans tw-text-white tw-uppercase tw-w-full tw-text-16 tw-font-500 tw-cursor-pointer"
          >
            Cancel
          </button>
        </div>
      </div>
    </Modal>
  )
}
