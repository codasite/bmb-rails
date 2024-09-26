import { Modal } from '../../../modals/Modal'
import { CancelButton, ConfirmButton, Link } from '../../../modals/ModalButtons'
import { ModalHeader } from '../../../modals/ModalHeader'

export default function SubmitPicksRegisterModal(props: {
  show: boolean
  setShow: (show: boolean) => void
  signInUrl: string
  registerUrl: string
}) {
  const { show, setShow } = props
  return (
    <Modal show={show} setShow={setShow}>
      <ModalHeader text={'Sign in or register to submit your picks!'} />
      <div className="tw-flex tw-flex-col tw-gap-10">
        <Link href={props.signInUrl} color="green">
          <span>Sign in</span>
        </Link>
        <Link href={props.registerUrl} variant="filled">
          <span>Register</span>
        </Link>
      </div>
    </Modal>
  )
}
