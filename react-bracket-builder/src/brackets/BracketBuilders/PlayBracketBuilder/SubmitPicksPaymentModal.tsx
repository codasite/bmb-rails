import { Elements } from '@stripe/react-stripe-js'
import { Modal } from '../../../modals/Modal'
import { CancelButton, ConfirmButton } from '../../../modals/ModalButtons'
import { ModalHeader } from '../../../modals/ModalHeader'
import { loadStripe } from '@stripe/stripe-js'

const stripePromise = loadStripe(
  'pk_test_51OWPu0GLKms5oOW8z8oolF9mn5sO2jOhXdkAbxgOSGkLe1F7U8yF5ChZ5NeaWAicb6EJjuGdIJ4JN2gUyj0euVZ000N53Gxru2'
)

interface SubmitPicksPaymentModalProps {
  show: boolean
  setShow: (show: boolean) => void
}

export default function SubmitPicksPaymentModal(
  props: SubmitPicksPaymentModalProps
) {
  const { show, setShow } = props

  const stripeOptions = {
    clientSecret: stripeClientSecret,
    apperance: {
      theme: 'stripe',
    },
  }

  return (
    <Elements stripe={stripePromise}>
      <Modal show={show} setShow={setShow}>
        <ModalHeader text={'Submit Your Picks'} />
        <div className="tw-flex tw-flex-col tw-gap-10">
          <CancelButton onClick={() => setShow(false)} />
        </div>
      </Modal>
    </Elements>
  )
}
