import { ModalHeader } from '../../../ModalHeader'
import { CancelButton, ConfirmButton } from '../../../ModalButtons'
import React, { useState } from 'react'
import { ModalHeaderLogo } from './ModalHeaderLogo'
import { BackwardsCurrencyInput } from './BackwardsCurrencyInput'
import { ConfirmFeeAmount } from './ConfirmFeeAmount'
import { bracketApi } from '../../../../brackets/shared'

export const InputFeeAmount = (props: {
  bracketId: number
  fee: number
  onCancel: () => void
}) => {
  const [showConfirm, setShowConfirm] = useState(false)
  const [processing, setProcessing] = useState(false)
  const [fee, setFee] = useState<number>(props.fee) // bracket fee in whole dollar amounts

  const handleSave = async () => {
    if (fee === props.fee) {
      props.onCancel()
      return
    }
    setProcessing(true)
    bracketApi
      .updateBracket(props.bracketId, {
        fee: fee,
      })
      .then((res) => {
        window.location.reload()
      })
      .catch((err) => {
        console.error(err)
      })
  }
  const handleGoBack = () => {
    setShowConfirm(false)
  }
  const inputStyles = [
    'tw-bg-transparent',
    'tw-border-none',
    'tw-outline-none',
    'tw-text-white/60',
    'tw-text-48',
    'sm:tw-text-64',
    'tw-font-sans',
    'tw-w-full',
    'tw-text-center',
  ]
  return (
    <>
      {showConfirm ? (
        <ConfirmFeeAmount
          fee={fee}
          handleGoBack={handleGoBack}
          handleSave={handleSave}
          disabled={processing}
          processing={processing}
        />
      ) : (
        <>
          <ModalHeaderLogo />
          <ModalHeader text={'Set an entry fee for your tournament'} />
          <div className="tw-flex tw-justify-center tw-py-12 tw-px-16 tw-mb-30 tw-border-b tw-border-b-solid tw-border-b-white/50">
            <BackwardsCurrencyInput
              value={fee}
              onChange={(value) => {
                console.log(value)
                setFee(value)
              }}
              allowCents={false}
              classNames={inputStyles.join(' ')}
            />
          </div>
          <div className="tw-flex tw-flex-col tw-gap-10">
            <ConfirmButton
              onClick={() => {
                setShowConfirm(true)
              }}
            >
              <span>Set entry fee</span>
            </ConfirmButton>
            <CancelButton
              onClick={() => {
                props.onCancel()
              }}
            />
          </div>
        </>
      )}
    </>
  )
}
