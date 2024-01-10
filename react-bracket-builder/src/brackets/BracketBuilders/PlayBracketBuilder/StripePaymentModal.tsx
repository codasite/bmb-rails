import { Elements } from '@stripe/react-stripe-js'
import { Modal } from '../../../modals/Modal'
import { CancelButton, ConfirmButton } from '../../../modals/ModalButtons'
import { ModalHeader } from '../../../modals/ModalHeader'
import { loadStripe, StripeElementsOptions } from '@stripe/stripe-js'
import StripePaymentForm from './StripePaymentForm'

const stripePromise = loadStripe(
  'pk_test_51OWPu0GLKms5oOW8z8oolF9mn5sO2jOhXdkAbxgOSGkLe1F7U8yF5ChZ5NeaWAicb6EJjuGdIJ4JN2gUyj0euVZ000N53Gxru2'
)

interface StripePaymentModalProps {
  title: string
  show: boolean
  setShow: (show: boolean) => void
  clientSecret: string
}

export default function StripePaymentModal(props: StripePaymentModalProps) {
  const { title, show, setShow, clientSecret } = props

  const appearance: StripeElementsOptions['appearance'] = {
    theme: 'night',
  }
  const stripeOptions = {
    clientSecret,
    appearance,
  }

  return (
    <>
      {clientSecret && (
        <Elements stripe={stripePromise} options={stripeOptions}>
          <Modal show={show} setShow={setShow}>
            <ModalHeader text={title} />
            <div className="tw-flex tw-flex-col tw-gap-10">
              <StripePaymentForm />
              <CancelButton onClick={() => setShow(false)} />
            </div>
          </Modal>
        </Elements>
      )}
    </>
  )
}
