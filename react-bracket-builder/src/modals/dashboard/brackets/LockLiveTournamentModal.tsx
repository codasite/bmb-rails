import { useState } from 'react'
import { Modal } from '../../Modal'
import { ModalHeader } from '../../ModalHeader'
import { CancelButton, ConfirmButton, DangerButton } from '../../ModalButtons'
import { bracketApi } from '../../../brackets/shared'
import { BracketData } from './BracketData'

export const LockLiveTournamentModal = (props: {
  show: boolean
  setShow: (show: boolean) => void
  bracketData: BracketData
  setBracketData: (bracketData: BracketData) => void
}) => {
  const headerText = `Update tournament status to "In Progress"?`

  return (
    <Modal show={props.show} setShow={props.setShow}>
      <ModalHeader text={headerText} />
      <p className="tw-text-center">
        No new plays will be accepted into the tournament.
        <span className="tw-text-red"> This action cannot be undone.</span>
      </p>
      <div className="tw-flex tw-flex-col tw-gap-10">
        <DangerButton
          onClick={() => {
            // Lock the tournament
            if (!props.bracketData.id) {
              return
            }
            bracketApi
              .updateBracket(props.bracketData.id, { status: 'score' })
              .then(() => {
                window.location.reload()
              })
              .catch((err) => {
                console.error(err)
              })
          }}
        >
          <span>Confirm</span>
        </DangerButton>
        <CancelButton onClick={() => props.setShow(false)}></CancelButton>
      </div>
    </Modal>
  )
}
