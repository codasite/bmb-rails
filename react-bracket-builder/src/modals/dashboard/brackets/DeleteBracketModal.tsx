import { ModalHeader } from '../../ModalHeader'
import { bracketApi } from '../../../brackets/shared/api/bracketApi'
import { CancelButton, DangerButton } from '../../ModalButtons'
import { useState } from 'react'
import { Modal } from '../../Modal'
import addClickHandlers from '../../addClickHandlers'
import { ReactComponent as LinkIcon } from '../../../brackets/deleted/assets/link.svg'

export const DeleteBracketModal = () => {
  const [bracketId, setBracketId] = useState<number | null>(null)
  const [loading, setLoading] = useState(false)
  const [title, setTitle] = useState('')
  const [show, setShow] = useState(false)

  addClickHandlers({
    buttonClassName: 'wpbb-delete-bracket-button',
    onButtonClick: (b) => {
      setTitle(b.dataset.bracketTitle)
      setBracketId(parseInt(b.dataset.bracketId))
      setShow(true)
    },
  })

  const resetState = () => {
    setBracketId(null)
    setLoading(false)
    setTitle('')
    setShow(false)
  }

  const onDeleteBracket = () => {
    if (!bracketId) {
      return
    }
    setLoading(true)
    bracketApi
      .deleteBracket(bracketId)
      .then((res) => {
        window.location.reload()
      })
      .catch((err) => {
        console.error(err)
      })
      .finally(() => {
        setLoading(false)
        resetState()
      })
  }

  return (
    <Modal show={show} setShow={setShow}>
      <ModalHeader text={`Delete "${title}"?`} />
      <p className="tw-text-center">This action cannot be undone.</p>
      <div className="tw-flex tw-flex-col tw-gap-10">
        <DangerButton disabled={loading} onClick={onDeleteBracket}>
          Delete
        </DangerButton>
        <CancelButton onClick={() => setShow(false)} />
      </div>
    </Modal>
  )
}
