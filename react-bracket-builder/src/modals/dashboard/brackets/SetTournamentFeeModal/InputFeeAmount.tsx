import { ConfirmButton } from '../../../ModalButtons'
import React, { useState } from 'react'
import { BackwardsCurrencyInput } from './BackwardsCurrencyInput'
import { bracketApi } from '../../../../brackets/shared'
import { Checkbox } from '../../../../brackets/BracketBuilders/BracketResultsBuilder/Checkbox'
import Button from '../../../../ui/Button'

const calculateApplicationFee = (
  fee: number,
  applicationFeePercentage: number,
  applicationFeeMinimum: number
) => {
  if (fee === 0) {
    return 0
  }
  let applicationFee = fee * applicationFeePercentage
  // remove extra decimal places
  applicationFee = Math.floor(applicationFee * 100) / 100
  return applicationFee < applicationFeeMinimum
    ? applicationFeeMinimum
    : applicationFee
}

export const InputFeeAmount = (props: {
  bracketId: number
  fee: number
  onCancel: () => void
  onSave: () => void
  applicationFeeMinimum: number
  applicationFeePercentage: number
}) => {
  const applicationFeeMinimum = props.applicationFeeMinimum * 0.01 // convert cents to dollars
  const [processing, setProcessing] = useState(false)
  const [fee, setFee] = useState<number>(props.fee) // bracket fee in whole dollar amounts
  const [acceptTermsChecked, setAcceptTermsChecked] = useState(false)
  const [showTermsError, setShowTermsError] = useState(false)
  const [showFeeMinError, setShowFeeMinError] = useState(false)
  const feeMinError = fee !== 0 && fee <= applicationFeeMinimum
  const handleSave = async () => {
    if (feeMinError) {
      setShowFeeMinError(true)
    }
    if (!acceptTermsChecked) {
      setShowTermsError(true)
    }
    if (feeMinError || !acceptTermsChecked) {
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
      .then(() => {
        props.onSave()
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
  const applicationFee = calculateApplicationFee(
    fee,
    props.applicationFeePercentage,
    applicationFeeMinimum
  )
  const yourCut = Math.round((fee - applicationFee) * 100) / 100
  return (
    <>
      <div className="tw-flex tw-justify-center tw-py-12 tw-px-16 tw-mb-10 tw-border-b tw-border-b-solid tw-border-b-white/50">
        <BackwardsCurrencyInput
          value={fee}
          onChange={(value) => {
            setFee(value)
            setShowFeeMinError(false)
          }}
          allowCents={false}
          classNames={inputStyles.join(' ')}
        />
      </div>
      {showFeeMinError && (
        <span className={'tw-text-red'}>Tournament fee must $2 or more.</span>
      )}
      <div className="tw-flex tw-flex-col tw-gap-10 tw-w-full">
        <div className={'tw-mb-15 tw-mt-15'}>
          <div className={'tw-text-white/60 tw-flex tw-justify-between'}>
            <span className={''}>BMB Platform Fee & Transaction</span>
            <span className={'tw-shrink-0'}>
              -$
              {applicationFee}
            </span>
          </div>
          <div className="tw-flex tw-justify-between tw-items-center">
            <span>Your cut</span>
            <span className={'tw-text-36'}>${Math.max(0, yourCut)}</span>
          </div>
        </div>
        <div className="tw-flex tw-items-center tw-justify-center tw-gap-10 tw-mb-15">
          <Checkbox
            id={'accept-terms-and-conditions'}
            checked={acceptTermsChecked}
            onChange={(e) => {
              if (showTermsError) {
                setShowTermsError(false)
              }
              setAcceptTermsChecked(e.target.checked)
            }}
            height={24}
            width={24}
            className={'tw-shrink-0'}
          />
          <label
            htmlFor={'accept-terms-and-conditions'}
            className={`tw-text-14 tw-font-sans ${
              showTermsError ? 'tw-text-red' : 'tw-text-white/60'
            }`}
          >
            I agree to the
            <a
              href="/hosting-terms-and-conditions"
              target="_blank"
              rel="noreferrer"
              className={`tw-pl-6 ${
                showTermsError ? 'tw-text-red' : 'tw-text-white/60'
              } !tw-underline`}
            >
              hosting terms and conditions
            </a>
          </label>
        </div>
        <Button
          color={'green'}
          variant={'outlined'}
          onClick={async () => {
            await handleSave()
          }}
          disabled={processing}
        >
          <span>Set entry fee</span>
        </Button>
      </div>
    </>
  )
}
