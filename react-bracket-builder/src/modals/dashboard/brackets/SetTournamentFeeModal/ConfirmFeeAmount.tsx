import { ModalHeader } from '../../../ModalHeader'
import { CancelButton, ConfirmButton } from '../../../ModalButtons'
import { Spinner } from '../../../../brackets/shared/components/Spinner'

export const ConfirmFeeAmount = (props: {
  fee?: number
  handleGoBack?: () => void
  handleSave?: () => void
  disabled?: boolean
  processing?: boolean
}) => {
  return (
    <>
      <ModalHeader text={'Are you sure?'} />
      <div className="tw-flex tw-flex-col tw-items-center tw-mb-30">
        <span
          className={'tw-text-48 sm:tw-text-64 tw-font-500 tw-leading-none'}
        >
          ${props.fee.toFixed(2)}
        </span>
        <span className={'tw-text-16 sm:tw-text-20 tw-font-500 tw-text-white'}>
          Entry Fee
        </span>
      </div>
      <div className="tw-flex tw-flex-col tw-gap-10">
        <ConfirmButton
          onClick={props.handleSave}
          disabled={props.disabled}
          className="tw-h-[42px]"
        >
          {props.processing ? (
            <Spinner fill="white" height={32} width={32} />
          ) : (
            <span>Yes, Confirm</span>
          )}
        </ConfirmButton>
        <CancelButton onClick={props.handleGoBack} disabled={props.disabled}>
          Go Back
        </CancelButton>
      </div>
    </>
  )
}
