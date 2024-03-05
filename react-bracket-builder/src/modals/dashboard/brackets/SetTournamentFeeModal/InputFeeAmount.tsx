import { ConfirmButton } from '../../../ModalButtons'
import React, { useState } from 'react'
import { BackwardsCurrencyInput } from './BackwardsCurrencyInput'
import { bracketApi } from '../../../../brackets/shared'
import { Checkbox } from '../../../../brackets/BracketBuilders/BracketResultsBuilder/Checkbox'

export const InputFeeAmount = (props: {
  bracketId: number
  fee: number
  onCancel: () => void
}) => {
  const [processing, setProcessing] = useState(false)
  const [fee, setFee] = useState<number>(props.fee) // bracket fee in whole dollar amounts
  const [acceptTermsChecked, setAcceptTermsChecked] = useState(false)
  const [showErrors, setShowErrors] = useState(true)

  const handleSave = async () => {
    if (!acceptTermsChecked) {
      setShowErrors(true)
      return
    }
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
        setProcessing(false)
      })
  }
  const inputStyles = [
    'tw-bg-transparent',
    'tw-border-none',
    'tw-outline-none',
    'tw-text-white',
    'tw-text-48',
    'sm:tw-text-64',
    'tw-font-sans',
    'tw-w-full',
    'tw-text-center',
  ]
  return (
    <>
      <div className="tw-flex tw-justify-center tw-py-12 tw-px-16 tw-mb-30 tw-border-b tw-border-b-solid tw-border-b-white/50">
        <BackwardsCurrencyInput
          value={fee}
          onChange={(value) => {
            setFee(value)
          }}
          allowCents={false}
          classNames={inputStyles.join(' ')}
        />
      </div>
      <div className="tw-flex tw-flex-col tw-gap-10">
        <div className="tw-flex tw-items-center tw-justify-center tw-gap-10 tw-mb-15">
          <Checkbox
            id={'accept-terms-and-conditions'}
            checked={acceptTermsChecked}
            onChange={(e) => {
              if (showErrors) {
                setShowErrors(false)
              }
              setAcceptTermsChecked(e.target.checked)
            }}
            height={24}
            width={24}
          />
          <label
            htmlFor={'accept-terms-and-conditions'}
            className={`tw-text-14 tw-font-sans ${
              showErrors ? 'tw-text-red' : 'tw-text-white/60'
            }`}
          >
            I agree to the
            <a
              href="/hosting-terms-and-conditions"
              target="_blank"
              rel="noreferrer"
              className={`tw-pl-6 ${
                showErrors ? 'tw-text-red' : 'tw-text-white/60'
              } !tw-underline`}
            >
              hosting terms and conditions
            </a>
          </label>
        </div>
        <ConfirmButton
          onClick={async () => {
            await handleSave()
          }}
          disabled={processing}
        >
          <span>Set entry fee</span>
        </ConfirmButton>
      </div>
    </>
  )
}
