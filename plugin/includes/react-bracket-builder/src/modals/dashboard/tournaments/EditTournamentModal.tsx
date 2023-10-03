import * as React from 'react'
import { useState } from 'react'
import { bracketApi } from '../../../brackets/shared/api/bracketApi'
import { DashboardModal } from '../DashboardModal'

export const EditTournamentModal = () => {
  const [tournamentId, setTournamentId] = useState<number | null>(null)
  const [loading, setLoading] = useState(false)
  const [input, setInput] = useState('')
  const [hasError, setHasError] = useState(false)
  const handleEditTournamentClick = (e: HTMLButtonElement) => {
    setInput(e.dataset.tournamentName)
    setTournamentId(parseInt(e.dataset.tournamentId))
  }
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
    <DashboardModal
      submitButtonText={'Save'}
      onSubmit={onEditTournament}
      header={'Edit info'}
      input={input}
      setInput={setInput}
      buttonClassName={'wpbb-edit-tournament-button'}
      onButtonClick={handleEditTournamentClick}
      hasError={hasError}
      setHasError={setHasError}
      loading={loading}
      errorText={'Tournament name is required'}
      placeholderText={'Tournament name...'}
    />
  )
}
