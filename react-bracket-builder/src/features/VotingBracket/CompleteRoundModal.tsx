import { useState } from 'react'
import { Modal } from '../../modals/Modal'
import { CancelButton, ConfirmButton } from '../../modals/ModalButtons'
import { ModalHeader } from '../../modals/ModalHeader'
import addClickHandlers from '../../modals/addClickHandlers'
import { votingBracketApi } from './votingBracketApi'
import { HttpError } from '../../brackets/shared/api/wpHttpClient'

export default function CompleteRoundModal() {
  const [show, setShow] = useState(false)
  const [loading, setLoading] = useState(false)
  const [errorMessage, setErrorMessage] = useState('')
  const [bracketId, setBracketId] = useState(0)
  const [liveRoundIndex, setLiveRoundIndex] = useState(0)

  addClickHandlers({
    buttonClassName: 'wpbb-complete-round-btn',
    onButtonClick: (b) => {
      setBracketId(parseInt(b.dataset.bracketId))
      setLiveRoundIndex(parseInt(b.dataset.liveRoundIndex))
      setShow(true)
      setErrorMessage('')
    },
  })

  async function onCompleteRound() {
    setLoading(true)
    try {
      await votingBracketApi.completeRound(bracketId)
      window.location.reload()
    } catch (error) {
      console.log(error)
      if (error instanceof HttpError) {
        setErrorMessage(`Error completing round. ${error.data.message}`)
      } else {
        setErrorMessage('Error completing round')
      }
    } finally {
      setLoading(false)
    }
  }

  return (
    <Modal show={show} setShow={setShow}>
      <ModalHeader text={`Complete Voting Round ${liveRoundIndex + 1}`} />
      <p className="tw-text-center">
        Complete the current voting round and set the next round to live.
      </p>
      <div className="tw-flex tw-flex-col tw-gap-10">
        <div className="tw-flex tw-gap-10 tw-items-center"></div>
        {errorMessage && (
          <p className="tw-text-red tw-text-center">{errorMessage}</p>
        )}
        <ConfirmButton disabled={loading} onClick={onCompleteRound}>
          Complete Round
        </ConfirmButton>
        <CancelButton onClick={() => setShow(false)} />
      </div>
    </Modal>
  )
}
