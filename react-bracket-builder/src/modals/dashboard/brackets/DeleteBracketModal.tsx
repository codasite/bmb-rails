import { ModalHeader } from '../../ModalHeader'
import { bracketApi } from '../../../brackets/shared/api/bracketApi'
import { CancelButton, DangerButton } from '../../ModalButtons'
import { useState } from 'react'
import { Modal } from '../../Modal'
import { BracketData } from './BracketData'

export const DeleteBracketModal = (props: {
  show: boolean
  setShow: (show: boolean) => void
  bracketData: BracketData
  setBracketData: (data: BracketData) => void
}) => {
  const [loading, setLoading] = useState(false)

  const onDeleteBracket = () => {
    if (!props.bracketData.id) {
      return
    }
    setLoading(true)
    bracketApi
      .deleteBracket(props.bracketData.id)
      .then((res) => {
        window.location.reload()
      })
      .catch((err) => {
        console.error(err)
      })
      .finally(() => {
        setLoading(false)
        props.setShow(false)
      })
  }

  return (
    <Modal show={props.show} setShow={props.setShow}>
      <ModalHeader text={`Delete "${props.bracketData.title}"?`} />
      <p className="tw-text-center">This action cannot be undone.</p>
      <div className="tw-flex tw-flex-col tw-gap-10">
        <DangerButton disabled={loading} onClick={onDeleteBracket}>
          Delete
        </DangerButton>
        <CancelButton onClick={() => props.setShow(false)} />
      </div>
    </Modal>
  )
}
