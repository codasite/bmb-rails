import { ModalHeader } from '../../ModalHeader'
import { CancelButton, ConfirmButton } from '../../ModalButtons'
import React, { useState } from 'react'
import { Modal } from '../../Modal'
import addClickHandlers from '../../addClickHandlers'
import { bracketApi } from '../../../brackets/shared'
import * as Sentry from '@sentry/react'
import { ModalTextField } from '../../ModalTextFields'

export default function SetTournamentFeeModal(props: {
  chargesEnabled: boolean
}) {
  const [show, setShow] = useState(false)
  const [showConfirm, setShowConfirm] = useState(false)
  const [fee, setFee] = useState<number>(null)
  const [feeHasError, setFeeHasError] = useState(false)
  const [bracketId, setBracketId] = useState<number>(null)
  const [setUpPaymentsProcessing, setSetUpPaymentsProcessing] = useState(false)
  addClickHandlers({
    buttonClassName: 'wpbb-set-tournament-fee-button',
    onButtonClick: (b) => {
      setBracketId(parseInt(b.dataset.bracketId))
      setFee(parseInt(b.dataset.fee))
      setShow(true)
    },
  })
  const setUpPaymentsButton = (
    <>
      <ConfirmButton
        disabled={setUpPaymentsProcessing}
        variant="red"
        onClick={async () => {
          setSetUpPaymentsProcessing(true)
          try {
            const { url } = await bracketApi.getStripeOnboardingLink()
            window.location.href = url
          } catch (error) {
            Sentry.captureException(error)
            setSetUpPaymentsProcessing(false)
          }
        }}
      >
        <span>Set up payments</span>
      </ConfirmButton>
    </>
  )
  return (
    <Modal show={show} setShow={setShow}>
      {showConfirm && (
        <>
          <ModalHeader text={'Are you sure?'} />
          <p className={'tw-text-64 tw-font-700 tw-leading-none'}>
            ${fee.toFixed(2)}
          </p>
          <ConfirmButton
            variant="green"
            onClick={async () => {
              // set entry fee
              try {
                await bracketApi.updateBracket(bracketId, { fee: 5 })
              } catch (err) {
                Sentry.captureException(err)
              } finally {
                setShow(false)
              }
            }}
          >
            <span>Yes</span>
          </ConfirmButton>
          <CancelButton onClick={() => setShowConfirm(false)} />
        </>
      )}

      {!showConfirm && (
        <>
          <ModalHeader text={'Set an entry fee for your tournament'} />
          <ModalTextField
            hasError={feeHasError}
            errorText={'Fee is required'}
            placeholderText={'$0'}
            input={(fee ?? '').toString()}
            setInput={(val) => setFee(parseInt(val))}
            setHasError={setFeeHasError}
          />
          <ConfirmButton
            variant="green"
            onClick={() => {
              setShowConfirm(true)
            }}
          >
            <span>Set entry fee</span>
          </ConfirmButton>
        </>
      )}
      <CancelButton
        onClick={() => {
          setShowConfirm(false)
          setShow(false)
        }}
        className={'tw-mt-10'}
      />
    </Modal>
  )
}
