import { CancelButton } from '../../../ModalButtons'
import React, { useState } from 'react'
import { Modal } from '../../../Modal'
import addClickHandlers from '../../../addClickHandlers'
import { bracketApi } from '../../../../brackets/shared'
import * as Sentry from '@sentry/react'
import { Spinner } from '../../../../brackets/shared/components/Spinner'
import { InputFeeAmount } from './InputFeeAmount'
import { ModalHeader } from '../../../ModalHeader'
import { ModalHeaderLogo } from './ModalHeaderLogo'
import { SetUpPaymentsButton } from './SetUpPaymentsButton'

export const SetTournamentFeeModal = (props: {
  applicationFeeMinimum: number
  applicationFeePercentage: number
}) => {
  const [show, setShow] = useState(true)
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
    onButtonClick: async (b) => {
      setBracketId(parseInt(b.dataset.bracketId))
      setFee(parseInt(b.dataset.fee))
      setShow(true)
      // Don't refetch if we already have the info
      if (!chargesEnabled) {
        await fetchChargesEnabled()
      }
    },
  })

  return (
    <Modal show={show} setShow={setShow}>
      <ModalHeaderLogo />
      <ModalHeader text={'Set an Entry Fee for Your Tournament'} />
      {loadingAccount ? (
        <div className="tw-flex tw-justify-center tw-items-center tw-mb-30">
          <Spinner fill={'white'} height={32} width={32} />
        </div>
      ) : chargesEnabled ? (
        <InputFeeAmount
          bracketId={bracketId}
          fee={fee}
          onCancel={handleCancel}
          applicationFeeMinimum={props.applicationFeeMinimum}
          applicationFeePercentage={props.applicationFeePercentage}
        />
      ) : (
        <SetUpPaymentsButton />
      )}
      <div className="tw-flex tw-justify-center tw-mt-10" />
      <CancelButton onClick={handleCancel} />
    </Modal>
  )
}
