import { CancelButton } from '../../../ModalButtons'
import React, { useState } from 'react'
import { Modal } from '../../../Modal'
import addClickHandlers from '../../../addClickHandlers'
import { bracketApi } from '../../../../brackets/shared'
import * as Sentry from '@sentry/react'
import { SetUpPayments } from './SetUpPayments'
import { Spinner } from '../../../../brackets/shared/components/Spinner'
import { InputFeeAmount } from './InputFeeAmount'

export const SetTournamentFeeModal = (props: { chargesEnabled: boolean }) => {
  const [show, setShow] = useState(false)
  const [fee, setFee] = useState<number>(null)
  const [bracketId, setBracketId] = useState<number>(null)
  const [loadingAccount, setLoadingAccount] = useState(false)
  const [chargesEnabled, setChargesEnabled] = useState(true)
  const fetchChargesEnabled = async () => {
    try {
      setLoadingAccount(true)
      const acct = await bracketApi.getStripeAccount()
      if (acct?.chargesEnabled) {
        setChargesEnabled(true)
      }
    } catch (error) {
      console.error(error)
      Sentry.captureException(error)
    } finally {
      setLoadingAccount(false)
    }
  }

  const handleCancel = () => {
    setShow(false)
  }

  addClickHandlers({
    buttonClassName: 'wpbb-set-tournament-fee-button',
    onButtonClick: (b) => {
      // Don't refetch if we already have the info
      if (!chargesEnabled) {
        fetchChargesEnabled()
      }
      setBracketId(parseInt(b.dataset.bracketId))
      setFee(parseInt(b.dataset.fee))
      setShow(true)
    },
  })

  return (
    <Modal show={show} setShow={setShow}>
      {loadingAccount ? (
        <div className="tw-flex tw-justify-center tw-items-center tw-mb-30">
          <Spinner fill={'white'} height={32} width={32} />
          <CancelButton onClick={handleCancel} />
        </div>
      ) : chargesEnabled ? (
        <InputFeeAmount
          bracketId={bracketId}
          fee={fee}
          onCancel={handleCancel}
        />
      ) : (
        <SetUpPayments onCancel={handleCancel} />
      )}
    </Modal>
  )
}
