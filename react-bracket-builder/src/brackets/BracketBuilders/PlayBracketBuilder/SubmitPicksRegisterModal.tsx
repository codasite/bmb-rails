import { Modal } from '../../../modals/Modal'
import { CancelButton, ConfirmButton } from '../../../modals/ModalButtons'
import { ModalHeader } from '../../../modals/ModalHeader'

export default function SubmitPicksRegisterModal(props: {
  show: boolean
  setShow: (show: boolean) => void
  loginUrl: string
}) {
  const { show, setShow } = props
  return (
    <Modal show={show} setShow={setShow}>
      <ModalHeader text={'Sign in or register to submit your picks!'} />
      <div className="tw-flex tw-flex-col tw-gap-10">
        <ConfirmButton
          onClick={() => {
            window.location.href = props.loginUrl
          }}
        >
          <span>Sign in/register</span>
        </ConfirmButton>
        <CancelButton onClick={() => setShow(false)} />
      </div>
    </Modal>
  )
}
