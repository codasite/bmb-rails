import * as React from 'react'
import { useState } from 'react'
import { bracketApi } from '../../../brackets/shared/api/bracketApi'
import { TextFieldModal } from '../../TextFieldModal'
import addClickHandlers from '../../addClickHandlers'

export const EditTournamentModal = () => {
  const [tournamentId, setTournamentId] = useState<number | null>(null)
  const [loading, setLoading] = useState(false)
  const [input, setInput] = useState('')
  const [hasError, setHasError] = useState(false)
  const [show, setShow] = useState(false)
  addClickHandlers({
    buttonClassName: 'wpbb-edit-tournament-button',
    onButtonClick: (b) => {
      setInput(b.dataset.tournamentName)
      setTournamentId(parseInt(b.dataset.tournamentId))
      setShow(true)
    },
  })
  const onEditTournament = () => {
    if (!input) {
      setHasError(true)
      return
    }
    setLoading(true)
    bracketApi
      .updateTournament(tournamentId, {
        title: input,
      })
      .then((res) => {
        window.location.reload()
      })
      .catch((err) => {
        console.error(err)
        setLoading(false)
      })
  }
  return (
    <TextFieldModal
      submitButtonText={'Save'}
      onSubmit={onEditTournament}
      header={'Edit info'}
      input={input}
      setInput={setInput}
      hasError={hasError}
      setHasError={setHasError}
      loading={loading}
      errorText={'Tournament name is required'}
      placeholderText={'Tournament name...'}
      setShow={setShow}
      show={show}
    />
  )
}
