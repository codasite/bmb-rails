import { ModalHeader } from '../../../ModalHeader'
import { ModalHeaderLogo } from './ModalHeaderLogo'
import { SetUpPaymentsButton } from './SetUpPaymentsButton'
import { CancelButton } from '../../../ModalButtons'

export const SetUpPayments = (props: { onCancel: () => void }) => {
  return (
    <>
      <ModalHeaderLogo />
      <ModalHeader text={'Set an Entry Fee for Your Tournament'} />
      <div className="tw-flex tw-flex-col tw-gap-10">
        <SetUpPaymentsButton />
        <CancelButton onClick={props.onCancel} />
      </div>
    </>
  )
}
